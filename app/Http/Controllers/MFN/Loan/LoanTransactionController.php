<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class LoanTransactionController extends Controller
{
    protected $transactionType = '';

    public function __construct(Request $req)
    {
        parent::__construct();
        if ($req->is('*/regularloanTransaction*') || $req->transactionType == 'Regular') {
            $this->transactionType = 'Regular';
        }

        if ($req->is('*/oneTimeLoanTransaction*')) {
            $this->transactionType = 'Onetime';
        }
    }

    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {

            $branchList = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select('id', 'branch_name', 'branch_code')
                ->get();

            if (count($branchList) > 1) {
                $samities = [];
            } else {
                $samities = MfnService::getSamities($branchList->pluck('id')->toArray());
            }

            $loanProductIds = MfnService::getBranchAssignedLoanProductIds($branchList->pluck('id')->toArray());

            $loanProducts = DB::table('mfn_loan_products')
                ->whereIn('id', $loanProductIds)
                ->where('is_delete', 0)
                ->select('id', 'name', 'productCode')
                ->get();

            $data = array(
                'branchList'    => $branchList,
                'samities'      => $samities,
                'loanProducts'  => $loanProducts,
            );

            return view('MFN.Loan.Transaction.index', $data);
        }

        $columns = [
            'memberName',
            'memberCode',
            'loanCode',
            'date',
            'principalAmount',
            'interestAmount',
            'principalWithInterest',
            'paid',
            'outStanding',
            'paymentType',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $loanTransactions = DB::table('mfn_loan_collections as mlc')
            ->select('mlc.id', 'mlc.isAuthorized', 'mm.name as memberName', 'mm.memberCode as memberCode', 'mlc.loanId', 'ml.loanCode as loanCode', 'mlc.collectionDate as date', 'amount', 'principalAmount', 'interestAmount', 'mlc.paymentType as paymentType')
            ->leftJoin('mfn_members as mm', 'mlc.memberId', 'mm.id')
            ->leftJoin('mfn_loans as ml', 'mlc.loanId', 'ml.id')
            ->whereIn('mlc.branchId', $accessAbleBranchIds)
            ->where([['mlc.is_delete', 0], ['mlc.amount', '!=', 0], ['ml.loanType', $this->transactionType]])
            ->where(function ($loanTransactions) use ($search) {
                if ($search != null) {
                    $loanTransactions->Where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            });

        if ($req->filBranch != '') {
            $loanTransactions->where('mlc.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $loanTransactions->where('mlc.samityId', $req->filSamity);
        }
        if ($req->fillProduct != '') {
            $loanTransactions->where('ml.productId', $req->fillProduct);
        }
        if ($req->loanCode != '') {
            $loanTransactions->where('ml.loanCode', 'LIKE', "%$req->loanCode%");
        }
        if ($req->start_date != '') {
            $startDate = date('Y-m-d', strtotime($req->start_date));
            $loanTransactions->where('mlc.collectionDate', '>=', $startDate);
        }
        if ($req->end_date != '') {
            $endDate = date('Y-m-d', strtotime($req->end_date));
            $loanTransactions->where('mlc.collectionDate', '<=', $endDate);
        }

        $totalData = (clone $loanTransactions)->count();

        $loanTransactions = $loanTransactions
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)->get();

        $sl = (int)$req->start + 1;

        $loanTransactions = $loanTransactions->sortBy('loanId')->sortBy('date')->values();

        $loanIds = $loanTransactions->unique('loanId')->pluck('loanId')->toArray();

        $loanStatuses = array();

        foreach ($loanIds as $key => $loanId) {
            $minTraDate = $loanTransactions->where('loanId', $loanId)->min('date');
            // get status on previous day of minimum tra. date
            $minTraDate = date('Y-m-d', strtotime('-1 day', strtotime($minTraDate)));
            $loanStatus = MfnService::getLoanStatus($loanId, $minTraDate);

            $loanStatuses = array_merge($loanStatuses, $loanStatus);
        }
        $loanStatuses = collect($loanStatuses);

        foreach ($loanStatuses as $key => $loanStatus) {
            $loanOutStandings[$loanStatus['loanId']] = $loanStatus['outstanding'];
        }

        foreach ($loanTransactions as $key => $loanTransaction) {
            $outStanding = $loanOutStandings[$loanTransaction->loanId] - $loanTransaction->amount;
            // update outstanding for next collection
            $loanOutStandings[$loanTransaction->loanId] = $outStanding;

            $loanTransactions[$key]->date                  = Carbon::parse($loanTransaction->date)->format('d-m-Y');
            $loanTransactions[$key]->principalAmount       = round($loanTransaction->principalAmount, 2);
            $loanTransactions[$key]->interestAmount        = round($loanTransaction->interestAmount, 2);
            $loanTransactions[$key]->principalWithInterest = round($loanTransaction->amount, 2);
            $loanTransactions[$key]->outStanding           = $outStanding;
            $loanTransactions[$key]->sl                    = $sl++;
            $loanTransactions[$key]->status                = '';

            $loanTransactions[$key]->id                    = encrypt($loanTransaction->id);
            $loanTransactions[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $loanTransactions[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $loanTransactions,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $members = MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $data = array(
            'sysDate' => $sysDate,
            'members' => $members,
        );

        return view('MFN.Loan.Transaction.add', $data);
    }

    public function store(Request $req)
    {
        $pass = $this->validationPass($req, $operationType = 'store');
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            $requestData = $req->all();

            $requestData['collectionDate'] = Carbon::parse($req->collectionDate)->format('Y-m-d');
            $requestData['created_by']     = Auth::user()->id;

            if ($req->paymentType == "Cash") {
                $requestData['ledgerId'] = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $requestData['chequeNo'] = '';
            }

            $loan = Loan::where('is_delete', 0)->where('id', $req->loanId)->select('id', 'loanType', 'interestRateIndex', 'loanAmount', 'repayAmount')->first();

            if ($loan->loanType == 'Regular') {
                $amount       = $req->amount;
                $interestRate = $loan->interestRateIndex;
                $requestData['principalAmount'] = round($amount / $interestRate, 5);
                $requestData['interestAmount']  = round($amount - $requestData['principalAmount'], 5);

                $justifiedData = $this->justifyPrincipalInterestAmount($loan, $amount, $requestData['principalAmount'], $requestData['interestAmount']);

                if ($justifiedData['isValid'] == false) {
                    throw new \Exception($justifiedData['errorMsg']);
                } else {
                    $requestData['principalAmount'] = $justifiedData['principalAmount'];
                    $requestData['interestAmount'] = $justifiedData['interestAmount'];
                }
            }

            $isCreate = LoanCollection::create($requestData);

            $updateStatus = self::updateLoanStatus($req->loanId);

            if ($updateStatus['isValid'] == false) {
                $notification = array(
                    'message'    => $updateStatus['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $th->getFile() . ' ' . $th->getLine() . ' ' . $th->getMessage(),
            );

            return response()->json($notification);
        }

        $notification = array(
            'message'    => 'Successfully Inserted',
            'alert-type' => 'success',
            'createId'   => $isCreate->id,
        );

        return response()->json($notification);
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $loanCollection = DB::table('mfn_loan_collections as mlc')
            ->where('mlc.id', decrypt($req->id))
            ->select('mlc.*', 'ml.id as loanId', 'mm.name as memberName','mm.memberCode as memberCode','ml.loanCode as loanCode')
            ->leftJoin('mfn_loans as ml', 'mlc.loanId', 'ml.id')
            ->leftjoin('mfn_members as mm','mm.id','mlc.memberId')
            ->first();
        
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $members = MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $data = array(
            'loanId'          => $loanCollection->loanId,
            'loanCode'        => $loanCollection->loanCode,
            'memberId'        => $loanCollection->memberId,
            'memberName'      => $loanCollection->memberName,
            'memberCode'      => $loanCollection->memberCode,
            'samityId'        => $loanCollection->samityId,
            'branchId'        => $loanCollection->branchId,
            'collectionDate'  => $loanCollection->collectionDate,
            'amount'          => round($loanCollection->amount, 2),
            'principalAmount' => round($loanCollection->principalAmount, 2),
            'interestAmount'  => round($loanCollection->interestAmount, 2),
            'paymentType'     => $loanCollection->paymentType,
            'ledgerId'        => $loanCollection->ledgerId,
            'chequeNo'        => $loanCollection->chequeNo,
            'loanId'          => $loanCollection->loanId,
            'members'         => $members,
        );

        return view('MFN.Loan.Transaction.edit', $data);
    }

    public function update(Request $req)
    {
        $collection = LoanCollection::find(decrypt($req->id));
        $pass       = $this->validationPass($req, $operationType = 'update', $collection);
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();
        
        try {
            $requestData = $req->all();

            $requestData['loanId'] = $collection->loanId;
            $requestData['memberId'] = $collection->memberId;
            $requestData['samityId'] = $collection->samityId;
            $requestData['branchId'] = $collection->branchId;
            $requestData['collectionDate'] = $collection->collectionDate;

            if ($req->paymentType == "Cash") {
                $requestData['ledgerId'] = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $requestData['chequeNo'] = '';
            }

            $loan = Loan::where('is_delete', 0)->where('id', $collection->loanId)->select('id', 'loanType', 'interestRateIndex', 'loanAmount', 'repayAmount')->first();
            
            if ($loan->loanType == 'Regular') {

                $amount       = floatval($req->amount);
                $interestRate = floatval($loan->interestRateIndex);

                $requestData['principalAmount'] = round($amount / $interestRate, 5);
                $requestData['interestAmount']  = round($amount - $requestData['principalAmount'], 5);

                $justifiedData = $this->justifyPrincipalInterestAmount($loan, $amount, $requestData['principalAmount'], $requestData['interestAmount'], $collection->id);
                
                if ($justifiedData['isValid'] == false) {
                    throw new \Exception($justifiedData['errorMsg']);
                } else {
                    $requestData['principalAmount'] = $justifiedData['principalAmount'];
                    $requestData['interestAmount'] = $justifiedData['interestAmount'];
                }
            }
            
            if (isset($req->is_delete)) {
                $requestData['is_delete'] = $req->is_delete;
            }

            $collection->update($requestData);

            $updateStatus = self::updateLoanStatus($req->loanId);
            
            if ($updateStatus['isValid'] == false) {
                $notification = array(
                    'message'    => $updateStatus['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $th->getFile() . ' ' . $th->getLine() . ' ' . $th->getMessage(),
            );

            return response()->json($notification);
        }

        $notification = array(
            'message'    => 'Successfully Updated',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $collection = LoanCollection::find(decrypt($req->id));
        $pass       = $this->validationPass($req, $operationType = 'delete', $collection);
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            LoanCollection::where('id', decrypt($req->id))->update(['is_delete' => 1]);

            self::updateLoanStatus($collection->loanId);

            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            DB::commit();

            return response()->json($notification);
        } catch (\Throwable $th) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $th->getFile() . ' ' . $th->getLine() . ' ' . $th->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function view(Request $req)
    {
        $loanCollection = DB::table('mfn_loan_collections as mlc')
            ->leftJoin('mfn_members as mm', 'mlc.memberId', 'mm.id')
            ->leftJoin('mfn_loans as ml', 'mlc.loanId', 'ml.id')
            ->where('mlc.id', decrypt($req->id))
            ->select('mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'mlc.*')
            ->first();

        $loanStatus = MfnService::getLoanStatus($loanCollection->loanId, $loanCollection->collectionDate);

        $entryBy = @DB::table('gnl_sys_users')->where('id', $loanCollection->created_by)->first()->employee_id;

        $data = array(
            'memberName'            => $loanCollection->memberName,
            'memberCode'            => $loanCollection->memberCode,
            'loanCode'              => $loanCollection->loanCode,
            'date'                  => date('d-m-Y', strtotime($loanCollection->collectionDate)),
            'principalAmount'       => round($loanCollection->principalAmount, 2),
            'interestAmount'        => round($loanCollection->interestAmount, 2),
            'outStanding'           => round($loanStatus[0]['outstanding'], 2),
            'amount' => round($loanCollection->amount, 2),
            'paymentType'           => $loanCollection->paymentType,
            'status'                => $loanCollection->isAuthorized ? 'Authorized' : 'Unauthorized',
            'entryBy'               => $entryBy,
        );

        return response()->json($data);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'member') {

            $filters['memberId'] = $req->memberId;
            // $filters['status']   = 'Living';
            $filters['isAuthorized']   = 1;
            $filters['onlyActiveLoan']   = 'yes';
            $branchDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $filters['date']   = $branchDate;

            $loans               = MfnService::getLoanAccounts($filters);

            // if loan has schedule then it shold be collected from auto process
            // those loan shold be avoided
            $schedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray(), $branchDate, $branchDate);
            $schedules = collect($schedules);

            $loanIdsHavingSchedule = $schedules->pluck('loanId')->toArray();

            $loans = $loans->whereNotIn('id', $loanIdsHavingSchedule)->where('loanType', $this->transactionType)->pluck('loanCode', 'id')->all();
            $data  = array(
                'loans' => $loans,
            );
        }

        if ($req->context == 'loanData') {

            $loanInfo = DB::table('mfn_loans as ml')
                ->where('ml.id', $req->loanId)
                ->select('ml.samityId', 'ml.branchId', 'ml.installmentAmount')
                ->first();

            $loanStatus = MfnService::getLoanStatus($req->loanId);

            $data = array(
                'collection'    => $loanStatus[0]['paidAmount'],
                'dueAmount'     => $loanStatus[0]['dueAmount'],
                'advanceAmount' => $loanStatus[0]['advanceAmount'],
                'outStanding'   => $loanStatus[0]['outstanding'],
                'installmentNo' => ($this->transactionType == 'Regular') ? floor($loanStatus[0]['paidAmount'] / $loanInfo->installmentAmount) + 1 : 1,

                'samityId'      => $loanInfo->samityId,
                'branchId'      => $loanInfo->branchId,
            );
        }

        return response()->json($data);
    }

    public function validationPass($req, $operationType, $collection = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {
            $loan = DB::table('mfn_loans')->where('id', $req->loanId)->first();
            $collectionDate = Carbon::parse($req->collectionDate)->format('Y-m-d');
            $sysDate        = MfnService::systemCurrentDate($loan->branchId);
        }
        else{
            $loan = DB::table('mfn_loans')->where('id', $collection->loanId)->first();
            $collectionDate = $collection->collectionDate;
            $sysDate        = MfnService::systemCurrentDate($collection->branchId);
        }

        if ($operationType == 'store') {

            // multiple transactio  is not allowed
            $transactionExists = DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['loanId', $loan->id],
                    ['collectionDate', $collectionDate],
                ])
                ->exists();

            if ($transactionExists) {
                $errorMsg = "Multiple Transaction at same date is not allowed.";
            }

            if ($sysDate != $collectionDate) {

                $errorMsg = "Your Branch Date Not Match!!";
            } else {

                if ($this->transactionType == 'Regular') {
                    $colln = DB::table('mfn_loans as ml')
                        ->where([['ml.id', $loan->id], ['ml.branchId', Auth::user()->branch_id]])
                        ->select('ml.repayAmount', DB::raw('SUM(mlc.amount) as amount'))
                        ->leftjoin('mfn_loan_collections as mlc', function ($query) {
                            $query->on('ml.id', 'mlc.loanId')->where('mlc.is_delete', 0);
                        })
                        ->groupBy('ml.id')
                        ->first();

                    if (($colln->amount + $req->amount) > $colln->repayAmount) {
                        $errorMsg = "Your Collection Amount Greater Than Loan Amount!!";
                    }
                }

                if ($this->transactionType == 'Onetime') {

                    $colln = DB::table('mfn_loans as ml')
                        ->where([['ml.id', $loan->id], ['ml.branchId', Auth::user()->branch_id]])
                        ->select('ml.loanAmount')
                        ->first();

                    if ($req->principalAmount > $colln->loanAmount) {
                        $errorMsg = "Your Collection Amount Greater Than Loan Amount!!";
                    }
                }
            }
        }

        if ($operationType == 'update') {

            if ($this->transactionType == 'Regular') {

                $loan = DB::table('mfn_loans')
                    ->where([
                        ['id', $collection->loanId],
                        ['branchId', $loan->branchId],
                    ])
                    ->select('repayAmount')
                    ->first();

                $loanCollection = DB::table('mfn_loan_collections')
                    ->where([
                        ['id', '!=', $collection->id],
                        ['loanId', $loan->id],
                        ['is_delete', 0],
                    ])
                    ->select(DB::raw('IFNULL(SUM(amount), 0) as amount'))
                    ->first();

                if ((@$loanCollection->amount + $req->amount) > $loan->repayAmount) {
                    $errorMsg = "Your Collection Amount Greater Than Loan Amount!!";
                }
            }

            if ($this->transactionType == 'Onetime') {

                $colln = DB::table('mfn_loans as ml')
                    ->where([['ml.id', $loan->id], ['ml.branchId', Auth::user()->branch_id]])
                    ->select('ml.loanAmount')
                    ->first();

                if ($req->principalAmount > $colln->loanAmount) {
                    $errorMsg = "Your Collection Amount Greater Than Loan Amount!!";
                }
            }
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this this transaction is auhorized then it could not be updated/deleted
            if ($collection->isAuthorized && $collection->amount != 0) {
                $errorMsg = "This is authorized, you can not update/delete it.";
            }
        }

        if ($sysDate != $collectionDate) {
            $errorMsg = "Your Branch Date Not Match!!";
        }

        // if any primary product trannsfer exists today/next days, then it could not be done any kind of update/delete or insert
        $productTransferExists = DB::table('mfn_member_primary_product_transfers')
        ->where([
            ['is_delete', 0],
            ['memberId', $loan->memberId],
            ['transferDate', '>=', $collectionDate],
        ])
        ->exists();

        if ($productTransferExists) {
            $errorMsg = 'Member primart product transfer exits.';
        }

        $isValid = $errorMsg == null ? true : false;

        $pass = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $pass;
    }

    public static function updateLoanStatus($loanIdOrIds)
    {
        $errorMsg = '';

        if (!is_array($loanIdOrIds)) {
            $loanIds = [$loanIdOrIds];
        } else {
            $loanIds = $loanIdOrIds;
        }

        $loans = DB::table('mfn_loans')->whereIn('id', $loanIds)->get();

        $collections = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
            ])
            ->whereIn('loanId', $loanIds)
            ->groupby('loanId')
            ->select(DB::raw("loanId, MAX(collectionDate) AS maxCollectionDate, SUM(amount) AS amount, SUM(principalAmount) AS principalAmount, SUM(interestAmount) AS interestAmount"))
            ->get();

        foreach ($loans as $key => $loan) {
            $canUpdate = true;
            $collection = $collections->where('loanId', $loan->id)->first();

            if ($collection == null) {
                DB::table('mfn_loans')->where('id', $loan->id)->update(['loanCompleteDate' => '0000-00-00', 'loanStatusId' => 4]);
            } else {
                if ($collection->principalAmount > $loan->loanAmount) {
                    $errorMsg .= 'Collection Principal amount could not be greater than Loan amount for ' . $loan->loanCode . '  ';
                    $canUpdate = false;
                } elseif ($loan->loanType == 'Regular') {
                    if ($collection->amount > $loan->repayAmount) {
                        $errorMsg .= 'Collection amount could not be greater than Loan Repay amount for ' . $loan->loanCode . '  ';
                        $canUpdate = false;
                    }
                    if ($collection->interestAmount > $loan->ineterestAmount) {
                        $errorMsg .= 'Collection Service Charge amount could not be greater than Loan Service Charge amount for ' . $loan->loanCode . '  ';
                        $canUpdate = false;
                    }
                }
                if ($canUpdate) {
                    if ($loan->loanType == 'Onetime') {
                        if ($collection->principalAmount >= $loan->loanAmount) {
                            DB::table('mfn_loans')->where('id', $loan->id)->update(['loanCompleteDate' => $collection->maxCollectionDate, 'loanStatusId' => 5]);
                        } else {
                            DB::table('mfn_loans')->where('id', $loan->id)->update(['loanCompleteDate' => '0000-00-00', 'loanStatusId' => 4]);
                        }
                    } else {
                        if ($collection->amount >= $loan->repayAmount) {
                            DB::table('mfn_loans')->where('id', $loan->id)->update(['loanCompleteDate' => $collection->maxCollectionDate, 'loanStatusId' => 5]);
                        } else {
                            DB::table('mfn_loans')->where('id', $loan->id)->update(['loanCompleteDate' => '0000-00-00', 'loanStatusId' => 4]);
                        }
                    }
                }
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $message = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $message;
    }

    /**
     * Sometimes it happens that when loan is going to be completed, principal amount or interest amount would be fractionally more or less than loanAmount or Interest amount.
     * 
     * So, when amount would be same as loan repay amount, then we have to correct the principal amount and interest amount thus sum of principal amount would be eual to loan amount and interest amount would be loan interest amount.
     * 
     * This is only applicable for regulat loan accounts. we will only modify the last collection amount.
     */
    public function justifyPrincipalInterestAmount($loan, $amount, $principalAmount, $interestAmount, $collectionId = null)
    {
        $errorMsg = null;
        // total collection amount
        $totalCollection = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['loanId', $loan->id],
            ])
            ->select(DB::raw("SUM(amount) AS amount, SUM(principalAmount) AS principalAmount, SUM(interestAmount) AS interestAmount"));

        if ($collectionId !== null) {
            $totalCollection->where('id', '!=', $collectionId);
        }

        $totalCollection = $totalCollection->first();

        // tota collection would be
        $totalCollectionAmount = $totalCollection->amount + $amount;
        $totalCollectionAmountPrincipal = $totalCollection->principalAmount + $principalAmount;
        $totalCollectionAmountInterest = $totalCollection->interestAmount + $interestAmount;

        if ($totalCollectionAmount >= $loan->repayAmount) {
            if ($totalCollectionAmountPrincipal != $loan->loanAmount) {
                $difference = $totalCollectionAmountPrincipal - $loan->loanAmount;
                if ($difference < 1) {
                    $principalAmount -= $difference;
                    $interestAmount = $amount - $principalAmount;
                } else {
                    $errorMsg = "Something went wrong with loan collection amount.";
                }
            }
        }

        $isValid = $errorMsg == null ? true : false;

        if ($isValid == true) {
            $message = array(
                'isValid'           => $isValid,
                'principalAmount'   => $principalAmount,
                'interestAmount'    => $interestAmount,
            );
        } else {
            $message = array(
                'isValid'  => $isValid,
                'errorMsg' => $errorMsg,
            );
        }

        return $message;
    }
}
