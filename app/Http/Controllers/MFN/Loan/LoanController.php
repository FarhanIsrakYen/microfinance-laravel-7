<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanDetails;
use App\Model\MFN\LoanGuarantor;
use App\Model\MFN\MemberDetails;
use App\Rules\MobileNo;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use App\Services\ResizeImage;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use App\Model\MFN\LoanCollection;

class LoanController extends Controller
{
    protected $loanType  = '';
    protected $isOpening = false;

    public function __construct(Request $req)
    {
        parent::__construct();

        if ($req->is('*/regularloan*')) {
            $this->loanType = 'Regular';
        }
        if ($req->is('*/oneTimeLoan*')) {
            $this->loanType = 'Onetime';
        }

        $this->middleware(function ($req, $next) {
            if (isset($req->id)) {
                $loan            = DB::table('mfn_loans')->where('id', decrypt($req->id))->select('branchId', 'isOpening')->first();
                $this->isOpening = MfnService::isOpening($loan->branchId) && $loan->isOpening;
            } else {
                $this->isOpening = MfnService::isOpening(Auth::user()->branch_id);
            }

            return $next($req);
        });
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

            return view('MFN.Loan.Account.index', $data);
        }

        $columns = [
            'mfn_loans.loanCode', 'mfn_members.memberCode', 'mfn_members.name', 'mfn_loans.loanAmount', 'mfn_loans.repayAmount', 'mfn_loan_details.interestCalculationMethodId',
            'mfn_loans.disbursementDate', 'mfn_loans.firstRepayDate', 'mfn_loans.numberOfInstallment', 'mfn_loans.isAuthorized', 'mfn_loan_status.name', 'hr_employees.emp_name'
        ];
        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable

        $search              = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $regularLoanAccounts = DB::table('mfn_loans')
            ->leftJoin('mfn_loan_details', 'mfn_loan_details.loanId', 'mfn_loans.id')
            ->leftJoin('mfn_members', 'mfn_members.id', 'mfn_loans.memberId')
            ->leftJoin('hr_employees', 'hr_employees.user_id', 'mfn_loans.created_by')
            ->leftJoin('mfn_loan_status', 'mfn_loan_status.id', 'mfn_loans.loanStatusId')
            ->leftJoin('mfn_loan_product_interest_rates', 'mfn_loan_product_interest_rates.id', 'mfn_loans.interestRateId')
            ->leftJoin('mfn_loan_interest_calculation_methods', 'mfn_loan_interest_calculation_methods.id', 'mfn_loan_details.interestCalculationMethodId')
            ->whereIn('mfn_loans.branchId', $accessAbleBranchIds)
            ->where('mfn_loans.is_delete', 0)
            ->where('mfn_loans.loanType', $this->loanType)
            ->select(
                'mfn_members.name AS memberName',
                'mfn_members.memberCode AS memberCode',
                'hr_employees.emp_name AS empName',
                'mfn_loan_product_interest_rates.interestRatePerYear',
                'mfn_loan_status.name AS loanStatus',
                DB::raw('CONCAT(mfn_loan_product_interest_rates.interestRatePerYear, " % ",mfn_loan_interest_calculation_methods.name) AS LoanIntMethods'),
                'mfn_loans.*'
            )
            ->orderBy($order, $dir);

        if ($search != null) {
            $regularLoanAccounts->where(function ($query) use ($search) {
                $query->Where('mfn_members.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_loans.loanCode', 'LIKE', "%{$search}%");
            });
        }

        if ($req->branch_id != '') {
            $regularLoanAccounts->where('mfn_loans.branchId', $req->branch_id);
        }
        if ($req->samity_id != '') {
            $regularLoanAccounts->where('mfn_loans.samityId', $req->samity_id);
        }
        if ($req->product != '') {
            $regularLoanAccounts->where('mfn_loans.productId', $req->product);
        }
        if ($req->loanCode != '') {
            $regularLoanAccounts->where('mfn_loans.loanCode', 'LIKE', "%$req->loanCode%");
        }
        if ($req->start_date != '') {
            $startDate = Carbon::parse($req->start_date)->format('Y-m-d');
            $regularLoanAccounts->where('mfn_loans.disbursementDate', '>=', $startDate);
        }
        if ($req->end_date != '') {
            $endDate = Carbon::parse($req->end_date)->format('Y-m-d');
            $regularLoanAccounts->where('mfn_loans.disbursementDate', '<=', $endDate);
        }

        $totalData = (clone $regularLoanAccounts)->count();
        $regularLoanAccounts = $regularLoanAccounts->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        
        foreach ($regularLoanAccounts as $key => $regularLoanAccount) {
            $regularLoanAccounts[$key]->disbursementDate = Carbon::parse($regularLoanAccount->disbursementDate)->format('d-m-Y');
            $regularLoanAccounts[$key]->firstRepayDate   = Carbon::parse($regularLoanAccount->firstRepayDate)->format('d-m-Y');
            $regularLoanAccounts[$key]->sl               = $sl++;
            $regularLoanAccounts[$key]->id               = encrypt($regularLoanAccount->id);
            $regularLoanAccounts[$key]->action           = RoleService::roleWiseArray($this->GlobalRole, $regularLoanAccounts[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $regularLoanAccounts,
        );
        return response()->json($data);
    }

    public function view($id)
    {
        $loan = DB::table('mfn_loans')->where('id', decrypt($id))
            ->select('*')->first();

        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $loan->branchId) {
            return '';
        }

        $loanProductName = DB::table('mfn_loan_products')
            ->where('id', $loan->productId)
            ->select(DB::raw('CONCAT(productCode, " - ", name) AS productName'))
            ->first()
            ->productName;

        $member = DB::table('mfn_members')->where('id', $loan->memberId)->select('name', 'memberCode', 'id')->first();

        $memberDetails = DB::table('mfn_member_details')->where('memberId', $member->id)
            ->select('dateOfBirth', 'fatherName', 'spouseName', 'mobileNo', 'profileImage', 'signatureImage')->first();

        $loanDetails = DB::table('mfn_loan_details')->where('loanId', $loan->id)->select(
            'loanId',
            'paymentType',
            'interestCalculationMethodId',
            'folioNumber',
            'additionalFee',
            'loanFormFee',
            'loanPurposeId',
            'familyFTM',
            'familyFTF',
            'familyPTM',
            'familyPTF',
            'fullTimeMaleWage',
            'fullTimeFemaleWage',
            'partTimeMaleWage',
            'partTimeFemaleWage',
            'businessName',
            'loanPurposeId'
        )->first();

        $loanCurrentStatus = DB::table('mfn_loan_status')->where('id', $loan->loanStatusId)->value('name');

        // member age calculate
        $memberAge = floor((time() - strtotime($memberDetails->dateOfBirth)) / 31556926);

        $samity = DB::table('mfn_samity')->where('id', $loan->samityId)->select('name', 'samityCode', 'samityDay')->first();

        $intRate            = DB::table('mfn_loan_product_interest_rates')->where('id', $loan->interestRateId)->select('interestRatePerYear')->first();
        $intCalculateMethod = DB::table('mfn_loan_interest_calculation_methods')->where('id', $loanDetails->interestCalculationMethodId)->select('name')->first();

        $guarantors = DB::table('mfn_loan_guarantors')
            ->where([
                ['is_delete', 0],
                ['loanId', $loan->id],
            ])
            ->get();

        $repayment    = DB::table('mfn_loan_repayment_frequency')->where('id', $loan->repaymentFrequencyId)->select('name')->first();
        $loanPurposes = DB::table('mfn_loan_purposes')->where('id', $loanDetails->loanPurposeId)->select('title')->first();

        $loanCollection = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['loanId', $loan->id],
            ])
            ->select(DB::raw("SUM(amount) AS amount, SUM(principalAmount) AS principalAmount"))
            ->first();

        $loanSchedules = MfnService::generateLoanSchedule($loan->id);

        $sysDate = MfnService::systemCurrentDate($loan->branchId);

        $loanFormFeeLabel = DB::table('mfn_config')->where('title','loanFormFeeLabel')->first()->content;

        $data = array(
            'loan'               => $loan,
            'loanDetails'        => $loanDetails,
            'loanProductName'    => $loanProductName,
            'loanCurrentStatus'  => $loanCurrentStatus,
            'member'             => $member,
            'memberDetails'      => $memberDetails,
            'memberAge'          => $memberAge,
            'samity'             => $samity,
            'intRate'            => $intRate,
            'intCalculateMethod' => $intCalculateMethod,
            'guarantors'         => $guarantors,
            'repayment'          => $repayment,
            'loanPurposes'       => $loanPurposes,
            'loanSchedules'      => $loanSchedules,
            'loanCollection'     => $loanCollection,
            'sysDate'            => $sysDate,
            'loanFormFeeLabel'  => $loanFormFeeLabel,
        );

        return view('MFN.Loan.Account.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $members = MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $loanPurposes = DB::table('mfn_loan_purposes')->where('status', 1)->get();

        $mfnGnlConfig    = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $mfnMemberConfig = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);

        $loanFormFeeLabel = DB::table('mfn_config')->where('title','loanFormFeeLabel')->first()->content;

        $data = array(
            'loanType'        => $this->loanType,
            'isOpening'       => $this->isOpening,
            'sysDate'         => $sysDate,
            'members'         => $members,
            'loanPurposes'    => $loanPurposes,
            'mfnGnlConfig'    => $mfnGnlConfig,
            'mfnMemberConfig' => $mfnMemberConfig,
            'loanFormFeeLabel' => $loanFormFeeLabel,
        );
        return view('MFN.Loan.Account.add', $data);
    }

    public function store(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // store data
        DB::beginTransaction();

        try {
            $member = DB::table('mfn_members')->where('id', $req->memberId)->select('id', 'samityId', 'branchId', 'primaryProductId')->first();

            if ($this->loanType == 'Regular') {
                $interestRate = MfnService::getInterestRateForRegularLoan($req->productId, $req->repaymentFrequencyId, $req->numberOfInstallment, $req->disbursementDate);
            } else {
                $interestRate = MfnService::getInterestRateForOnetimeLoan($req->productId, $req->disbursementDate);
            }

            $loan                          = new Loan;
            $loan->loanCode                = $req->loanCode;
            $loan->memberId                = $req->memberId;
            $loan->samityId                = $member->samityId;
            $loan->branchId                = $member->branchId;
            $loan->loanType                = $this->loanType;
            $loan->repaymentFrequencyId    = $req->repaymentFrequencyId;
            $loan->loanDurationInMonth     = $req->loanDurationInMonth;
            $loan->loanCycle               = $req->loanCycle;
            $loan->productId               = $req->productId;
            $loan->primaryProductId        = $member->primaryProductId;
            $loan->loanAmount              = $req->loanAmount;
            $loan->repayAmount             = $req->repayAmount;
            $loan->ineterestAmount         = $req->ineterestAmount;
            $loan->interestRateIndex       = $interestRate->interestRateIndex;
            $loan->interestRateId          = $interestRate->id;
            $loan->insuranceAmount         = $req->insuranceAmount;
            $loan->installmentAmount       = $req->installmentAmount;
            $loan->actualInstallmentAmount = $req->actualInstallmentAmount;
            $loan->extraInstallmentAmount  = $req->extraInstallmentAmount;
            $loan->lastInstallmentAmount   = $req->lastInstallmentAmount;
            $loan->disbursementDate        = date('Y-m-d', strtotime($req->disbursementDate));
            $loan->firstRepayDate          = date('Y-m-d', strtotime($req->firstRepayDate));
            $loan->numberOfInstallment     = $this->loanType == 'Regular' ? $req->numberOfInstallment : 1;
            $loan->loanStatusId            = 1; // 1 for Requested
            $loan->isOpening               = $this->isOpening == true ? 1 : 0;
            $loan->created_at              = date("Y-m-d h:i:s");
            $loan->created_by              = Auth::user()->id;
            $loan->save();

            if ($this->loanType == 'Regular') {
                $loaSchedules    = Mfnservice::generateLoanSchedule($loan->id);
                $lastInstallment = end($loaSchedules);

                $loan->lastInastallmentDate = $lastInstallment['installmentDate'];
            }

            if ($this->loanType == 'Onetime') {
                $loan->lastInastallmentDate = $loan->firstRepayDate;
            }

            $loan->save();

            if ($req->paymentType == 'Cash') {
                $ledgerId = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }

            // store loan details
            $loanDetails                              = new LoanDetails;
            $loanDetails->loanId                      = $loan->id;
            $loanDetails->loanPurposeId               = $req->loanPurpose;
            $loanDetails->loanApplicationNo           = $req->loanApplicationNo;
            $loanDetails->folioNumber                 = $req->folioNumber;
            $loanDetails->interestCalculationMethodId = $interestRate->interestCalculationMethodId;
            $loanDetails->paymentType                 = $req->paymentType;
            $loanDetails->ledgerId                    = $ledgerId;
            $loanDetails->chequeNo                    = $chequeNo;
            $loanDetails->additionalFee               = $req->additionalFee;
            $loanDetails->loanFormFee                 = $req->loanFormFee;
            $loanDetails->note                        = $req->note;
            $loanDetails->familyFTM                   = (int)$req->familyFTM;
            $loanDetails->familyFTF                   = (int)$req->familyFTF;
            $loanDetails->familyPTM                   = (int)$req->familyPTM;
            $loanDetails->familyPTF                   = (int)$req->familyPTF;
            $loanDetails->outsideFTM                  = (int)$req->outsideFTM;
            $loanDetails->outsideFTF                  = (int)$req->outsideFTF;
            $loanDetails->outsidePTM                  = (int)$req->outsidePTM;
            $loanDetails->outsidePTF                  = (int)$req->outsidePTF;
            $loanDetails->fullTimeMaleWage            = (int)$req->fullTimeMaleWage;
            $loanDetails->fullTimeFemaleWage          = (int)$req->fullTimeFemaleWage;
            $loanDetails->partTimeMaleWage            = (int)$req->partTimeMaleWage;
            $loanDetails->partTimeFemaleWage          = (int)$req->partTimeFemaleWage;
            $loanDetails->businessName                = $req->businessName;
            $loanDetails->businessLocation            = $req->businessLocation;
            $loanDetails->created_at                  = Carbon::now();
            $loanDetails->created_by                  = Auth::user()->id;
            $loanDetails->save();

            // if opening, store opening collection amount
            if ($this->isOpening == true) {
                if ($this->loanType == 'Onetime') {
                    $principalAmount = $req->openingCollectionAmountPrincipal;
                    $interestAmount  = $req->openingCollectionAmountInterest;
                    $amount = $principalAmount + $interestAmount;
                } else {
                    $amount       = floatval($req->openingCollectionAmount);
                    $interestRate = floatval($loan->interestRateIndex);

                    $principalAmount = round($amount / $interestRate, 5);
                    $interestAmount  = round($amount - $principalAmount, 5);
                }

                $loanCollection = new LoanCollection;
                $loanCollection->loanId = $loan->id;
                $loanCollection->memberId = $loan->memberId;
                $loanCollection->samityId = $loan->samityId;
                $loanCollection->branchId = $loan->branchId;
                $loanCollection->collectionDate = MfnService::systemCurrentDate($loan->branchId);
                $loanCollection->amount = $amount;
                $loanCollection->principalAmount = $principalAmount;
                $loanCollection->interestAmount = $interestAmount;
                $loanCollection->paymentType = 'OB';
                $loanCollection->ledgerId = 0;
                $loanCollection->created_at = Carbon::now();
                $loanCollection->created_by = Auth::user()->id;
                $loanCollection->isAuthorized = 1;
                $loanCollection->save();
            }

            // store member image
            $memberImageData = $this->storeMemberImage($req, $member->id, 'store');

            if ($memberImageData['errorMsg'] == null) {
                $memberDetails = MemberDetails::find($member->id);
                if ($memberImageData['profileImageFilename'] != null) {
                    $memberDetails->profileImage = $memberImageData['profileImageFilename'];
                }
                if ($memberImageData['signatureImageFilename'] != null) {
                    $memberDetails->signatureImage = $memberImageData['signatureImageFilename'];
                }

                $memberDetails->save();
            } else {
                throw new \Exception($memberImageData['errorMsg']);
            }

            // store Guarantor data
            // store Guarantor image
            $guarantorImageData = $this->storeGuarantorImage($req, $loan->id, 'store');

            if ($guarantorImageData['errorMsg'] != null) {
                throw new \Exception($guarantorImageData['errorMsg']);
            }

            foreach ($req->guarantorNames as $key => $guarantorName) {
                if ($guarantorName == '' && $req->guarantorRelations[$key] == '' && $req->guarantorAddresses[$key] == '' && $req->guarantorPhones[$key] == '') {
                    continue;
                }

                $profileImage   = '';
                $signatureImage = '';

                if ($key == 0) {
                    $profileImage   = $guarantorImageData['firstGuarantorProfileImageFilename'];
                    $signatureImage = $guarantorImageData['firstGurantorSignatureImageFilename'];
                } elseif ($key == 1) {
                    $profileImage   = $guarantorImageData['secondGuarantorProfileImageFilename'];
                    $signatureImage = $guarantorImageData['secondGurantorSignatureImageFilename'];
                }

                $gurantor                 = new LoanGuarantor;
                $gurantor->loanId         = $loan->id;
                $gurantor->name           = $guarantorName;
                $gurantor->relation       = $req->guarantorRelations[$key];
                $gurantor->address        = $req->guarantorAddresses[$key];
                $gurantor->phone          = $req->guarantorPhones[$key];
                $gurantor->guarantorNo    = $key + 1;
                $gurantor->created_at     = Carbon::now();
                $gurantor->created_by     = Auth::user()->id;
                $gurantor->profileImage   = $profileImage;
                $gurantor->signatureImage = $signatureImage;
                $gurantor->save();
            }


            MfnService::sendMail('mfn_loans', $loan->memberId, $loan->created_at, $loan->loanAmount);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $canEditMsg = $this->canUpdateDelete((int)decrypt($req->id), 'update');

        if ($canEditMsg !== true) {
            // here a view will be returned with msg.
            return $canEditMsg;
        }

        $loan = DB::table('mfn_loans AS loan')
            ->join('mfn_loan_details AS ld', 'ld.loanId', 'loan.id')
            ->join('mfn_members AS member', 'member.id', 'loan.memberId')
            ->join('mfn_member_details AS md', 'md.memberId', 'loan.memberId')
            ->join('mfn_loan_products AS product', 'product.id', 'loan.productId')
            ->join('mfn_loan_product_interest_rates AS interestRate', 'interestRate.id', 'loan.interestRateId')
            ->where('loan.id', decrypt($req->id))
            ->select(DB::raw("loan.*, ld.*, CONCAT(member.memberCode, ' - ', member.name) AS memberName, CONCAT(product.productCode, ' - ', product.name) AS productName, interestRate.interestRatePerYear, interestRate.interestCalculationMethodId, md.profileImage AS memberProfileImage, md.signatureImage AS memberSignatureImage"))
            ->first();

        // if it is opening than system date is equivalent to branch software start date
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $loanPurposes = DB::table('mfn_loan_purposes')->where('status', 1)->get();

        $mfnGnlConfig    = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $mfnMemberConfig = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);

        $product = DB::table('mfn_loan_products')->where('id', $loan->productId)->first();

        $repaymentInfos = [];

        $repaymentFrequencies = DB::table('mfn_loan_repayment_frequency')->get();

        // get product repayment informations
        $repayments = json_decode($product->repaymentInfo);
        // if product is regular
        if ($product->productTypeId == 1) {

            foreach ($repayments as $key => $repayment) {
                $repaymentInfos['repaymentFrequencies'][$repayment->repaymentFrequencyId]         = $repaymentFrequencies->where('id', $repayment->repaymentFrequencyId)->first()->name;
                $repaymentInfos['eligibleNumberOfInstallments'][$repayment->repaymentFrequencyId] = $repayment->eligibleNumberOfInstallments;
            }
        }
        // if it is one time loan
        elseif ($product->productTypeId == 2) {
            $repaymentInfos['eligibleMonths'] = explode(',', $repayments->eligibleMonths);
        }

        $interestCalMethod = DB::table('mfn_loan_interest_calculation_methods')->where('id', $loan->interestCalculationMethodId)->value('name');

        $guarantors = DB::table('mfn_loan_guarantors')
            ->where([
                ['is_delete', 0],
                ['loanId', $loan->id],
            ])
            ->get();

        $openingCollection = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['loanId', $loan->id],
                ['paymentType', 'OB'],
            ])
            ->first();
        
        $loanFormFeeLabel = DB::table('mfn_config')->where('title','loanFormFeeLabel')->first()->content;
        
        $data = array(
            'loan'              => $loan,
            'loanType'          => $this->loanType,
            'isOpening'         => $this->isOpening,
            'product'           => $product,
            'sysDate'           => $sysDate,
            'loanPurposes'      => $loanPurposes,
            'mfnGnlConfig'      => $mfnGnlConfig,
            'mfnMemberConfig'   => $mfnMemberConfig,
            'repaymentInfos'    => $repaymentInfos,
            'interestCalMethod' => $interestCalMethod,
            'guarantors'        => $guarantors,
            'openingCollection' => $openingCollection,
            'loanFormFeeLabel'  => $loanFormFeeLabel,
        );
        return view('MFN.Loan.Account.edit', $data);
    }

    public function update(Request $req)
    {
        $loan     = Loan::find(decrypt($req->id));
        $passport = $this->getPassport($req, $operationType = 'update', $loan);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // update data
        DB::beginTransaction();

        try {
            $member = DB::table('mfn_members')->where('id', $loan->memberId)->select('id', 'samityId', 'branchId', 'primaryProductId')->first();

            if ($this->loanType == 'Regular') {
                $interestRate = MfnService::getInterestRateForRegularLoan($req->productId, $req->repaymentFrequencyId, $req->numberOfInstallment, $req->disbursementDate);
            } else {
                $interestRate = MfnService::getInterestRateForOnetimeLoan($req->productId, $req->disbursementDate);
            }

            if ($this->isOpening) {
                $loan->disbursementDate = date('Y-m-d', strtotime($req->disbursementDate));
            }

            $loan->repaymentFrequencyId    = $req->repaymentFrequencyId;
            $loan->loanCycle               = $req->loanCycle;
            $loan->loanAmount              = $req->loanAmount;
            $loan->repayAmount             = $req->repayAmount;
            $loan->ineterestAmount         = $req->ineterestAmount;
            $loan->interestRateIndex       = $interestRate->interestRateIndex;
            $loan->interestRateId          = $interestRate->id;
            $loan->insuranceAmount         = $req->insuranceAmount;
            $loan->installmentAmount       = $req->installmentAmount;
            $loan->actualInstallmentAmount = $req->actualInstallmentAmount;
            $loan->extraInstallmentAmount  = $req->extraInstallmentAmount;
            $loan->lastInstallmentAmount   = $req->lastInstallmentAmount;
            $loan->firstRepayDate          = date('Y-m-d', strtotime($req->firstRepayDate));
            $loan->lastInastallmentDate    = '2020-12-31';
            $loan->numberOfInstallment     = $req->numberOfInstallment;
            $loan->loanStatusId            = 1; // 1 for Requested
            $loan->isOpening               = $this->isOpening == true ? 1 : 0;
            $loan->created_at              = date("Y-m-d h:i:s");
            $loan->created_by              = Auth::user()->id;
            $loan->save();

            if ($this->loanType == 'Regular') {
                $loaSchedules    = Mfnservice::generateLoanSchedule($loan->id);
                $lastInstallment = end($loaSchedules);

                $loan->lastInastallmentDate = $lastInstallment['installmentDate'];
            }

            if ($this->loanType == 'Onetime') {
                $loan->lastInastallmentDate = $loan->firstRepayDate;
            }

            $loan->save();

            if ($req->paymentType == 'Cash') {
                $ledgerId = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }

            // update loan details
            $loanDetails                              = LoanDetails::find($loan->id);
            $loanDetails->loanPurposeId               = $req->loanPurpose;
            $loanDetails->loanApplicationNo           = $req->loanApplicationNo;
            $loanDetails->folioNumber                 = $req->folioNumber;
            $loanDetails->interestCalculationMethodId = $interestRate->interestCalculationMethodId;
            $loanDetails->paymentType                 = $req->paymentType;
            $loanDetails->ledgerId                    = $ledgerId;
            $loanDetails->chequeNo                    = $chequeNo;
            $loanDetails->additionalFee               = $req->additionalFee;
            $loanDetails->loanFormFee                 = $req->loanFormFee;
            $loanDetails->note                        = $req->note;
            $loanDetails->familyFTM                   = (int)$req->familyFTM;
            $loanDetails->familyFTF                   = (int)$req->familyFTF;
            $loanDetails->familyPTM                   = (int)$req->familyPTM;
            $loanDetails->familyPTF                   = (int)$req->familyPTF;
            $loanDetails->outsideFTM                  = (int)$req->outsideFTM;
            $loanDetails->outsideFTF                  = (int)$req->outsideFTF;
            $loanDetails->outsidePTM                  = (int)$req->outsidePTM;
            $loanDetails->outsidePTF                  = (int)$req->outsidePTF;
            $loanDetails->fullTimeMaleWage            = (int)$req->fullTimeMaleWage;
            $loanDetails->fullTimeFemaleWage          = (int)$req->fullTimeFemaleWage;
            $loanDetails->partTimeMaleWage            = (int)$req->partTimeMaleWage;
            $loanDetails->partTimeFemaleWage          = (int)$req->partTimeFemaleWage;
            $loanDetails->businessName                = $req->businessName;
            $loanDetails->businessLocation            = $req->businessLocation;
            $loanDetails->updated_by                  = Auth::user()->id;
            $loanDetails->save();

            // if opening, update opening collection amount
            if ($this->isOpening == true) {
                if ($this->loanType == 'Onetime') {
                    $principalAmount = $req->openingCollectionAmountPrincipal;
                    $interestAmount  = $req->openingCollectionAmountInterest;
                    $amount = $principalAmount + $interestAmount;
                } else {
                    $amount       = floatval($req->openingCollectionAmount);
                    $interestRate = floatval($loan->interestRateIndex);

                    $principalAmount = round($amount / $interestRate, 5);
                    $interestAmount  = round($amount - $principalAmount, 5);
                }

                $loanCollection = LoanCollection::where([
                    ['is_delete', 0],
                    ['loanId', $loan->id],
                    ['paymentType', 'OB'],
                ])
                    ->first();

                if ($loanCollection == null) {
                    $loanCollection = new LoanCollection;
                    $loanCollection->loanId = $loan->id;
                    $loanCollection->memberId = $loan->memberId;
                    $loanCollection->samityId = $loan->samityId;
                    $loanCollection->branchId = $loan->branchId;
                    $loanCollection->collectionDate = MfnService::systemCurrentDate($loan->branchId);
                    $loanCollection->amount = $amount;
                    $loanCollection->principalAmount = $principalAmount;
                    $loanCollection->interestAmount = $interestAmount;
                    $loanCollection->paymentType = 'OB';
                    $loanCollection->ledgerId = 0;
                    $loanCollection->created_at = Carbon::now();
                    $loanCollection->created_by = Auth::user()->id;
                    $loanCollection->isAuthorized = 1;
                } else {
                    $loanCollection->amount = $amount;
                    $loanCollection->principalAmount = $principalAmount;
                    $loanCollection->interestAmount = $interestAmount;
                    $loanCollection->updated_at = Carbon::now();
                    $loanCollection->updated_by = Auth::user()->id;
                }
                $loanCollection->save();
            }

            // store member image
            $memberImageData = $this->storeMemberImage($req, $member->id, 'store');

            if ($memberImageData['errorMsg'] == null) {
                $memberDetails = MemberDetails::find($member->id);
                if ($memberImageData['profileImageFilename'] != null) {
                    $memberDetails->profileImage = $memberImageData['profileImageFilename'];
                }
                if ($memberImageData['signatureImageFilename'] != null) {
                    $memberDetails->signatureImage = $memberImageData['signatureImageFilename'];
                }

                $memberDetails->save();
            } else {
                throw new \Exception($memberImageData['errorMsg']);
            }

            // store Guarantor data
            // store Guarantor image
            $guarantorImageData = $this->storeGuarantorImage($req, $loan->id, 'update');

            if ($guarantorImageData['errorMsg'] != null) {
                throw new \Exception($guarantorImageData['errorMsg']);
            }

            foreach ($req->guarantorNames as $key => $guarantorName) {
                if ($guarantorName == '' && $req->guarantorRelations[$key] == '' && $req->guarantorAddresses[$key] == '' && $req->guarantorPhones[$key] == '') {
                    DB::table('mfn_loan_guarantors')
                        ->where([
                            ['is_delete', 0],
                            ['loanId', $loan->id],
                            ['guarantorNo', $key + 1],
                        ])
                        ->update(['is_delete' => 1]);

                    continue;
                }

                $profileImage   = '';
                $signatureImage = '';

                if ($key == 0) {
                    $profileImage   = $guarantorImageData['firstGuarantorProfileImageFilename'];
                    $signatureImage = $guarantorImageData['firstGurantorSignatureImageFilename'];
                } elseif ($key == 1) {
                    $profileImage   = $guarantorImageData['secondGuarantorProfileImageFilename'];
                    $signatureImage = $guarantorImageData['secondGurantorSignatureImageFilename'];
                }

                // $gurantor = LoanGuarantor::where('loanId', $loan->id)->where('guarantorNo', $key + 1)->first();
                $gurantor              = LoanGuarantor::firstOrNew(['loanId' => $loan->id, 'guarantorNo' => $key + 1, 'is_delete' => 0]);
                $gurantor->name        = $guarantorName;
                $gurantor->relation    = $req->guarantorRelations[$key];
                $gurantor->address     = $req->guarantorAddresses[$key];
                $gurantor->phone       = $req->guarantorPhones[$key];
                $gurantor->guarantorNo = $key + 1;
                if ($gurantor->exists) {
                    $gurantor->updated_by = Auth::user()->id;
                } else {
                    $gurantor->created_by = Auth::user()->id;
                    $gurantor->created_at = Carbon::now();
                }

                if ($profileImage != null || $profileImage != '') {
                    $gurantor->profileImage = $profileImage;
                }
                if ($signatureImage != null || $signatureImage != '') {
                    $gurantor->signatureImage = $signatureImage;
                }

                $gurantor->save();
            }

            MfnService::sendMail('mfn_loans', $loan->memberId, $loan->created_at, $loan->loanAmount, true);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function delete(Request $req)
    {
        $loan     = Loan::find(decrypt($req->id));
        $passport = $this->getPassport($req, $operationType = 'delete', $loan);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $loan->is_delete = 1;
            $loan->save();

            $loanDetails            = Loandetails::find($loan->id);
            $loanDetails->is_delete = 1;
            $loanDetails->save();

            // delete guarantorsp
            DB::table('mfn_loan_guarantors')
                ->where('loanId', $loan->id)
                ->update(['is_delete' => 1]);

            // delete collections
            DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['loanId', $loan->id],
                ])
                ->delete();

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function storeMemberImage($req, $memberId, $operationType = null)
    {
        try {
            $profileImageErrorMsg   = null;
            $signatureImageErrorMsg = null;

            $profileImageFilename   = null;
            $signatureImageFilename = null;

            $mfnMemberConfig = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);
            $mfnLoanConfig   = json_decode(DB::table('mfn_config')->where('title', 'loan')->first()->content);

            if ($operationType == 'update' || $this->isOpening) {
                $mfnLoanConfig->isMemberProfileImageMandatory   = 'no';
                $mfnLoanConfig->isMemberSignatureImageMandatory = 'no';
            }
            // profile image
            if ($mfnLoanConfig->isMemberProfileImageMandatory == 'yes' || ($req->profileImageText != '' || $req->hasFile('profileImage'))) {

                if ($req->profileImageText == '') {
                    $extension = $req->file('profileImage')->getClientOriginalExtension();
                } else {
                    $extension = 'jpg';
                }

                // generate profile file name
                $profileImageFilename = 'mp' . Carbon::now()->format('YmdHis') . $memberId . '.' . $extension;

                // check is file exists or not
                if ($operationType != 'update' && file_exists(public_path('images/members/profile/' . $profileImageFilename))) {
                    $profileImageErrorMsg = 'File already exists, file name: ' . $profileImageFilename;
                }
            }

            // signature image
            if ($mfnLoanConfig->isMemberSignatureImageMandatory == 'yes' || ($req->signatureImageText != '' || $req->hasFile('signatureImage'))) {
                if ($req->signatureImageText == '') {
                    // $signatureImage = Image::make($req->file('signatureImage'));
                    $extension = $req->file('signatureImage')->getClientOriginalExtension();
                } else {
                    // $signatureImage = Image::make(file_get_contents($req->signatureImageText));
                    $extension = 'jpg';
                }

                // generate signature image file name
                $signatureImageFilename = 'ms' . Carbon::now()->format('YmdHis') . $memberId . '.' . $extension;

                // check is file exists or not
                if ($operationType != 'update' && file_exists(public_path('images/members/signature/' . $signatureImageFilename))) {
                    $signatureImageErrorMsg = 'File already exists, file name: ' . $signatureImageFilename;
                }
            }

            if ($profileImageErrorMsg == null && $signatureImageErrorMsg == null) {
                // store images

                if ($profileImageFilename != null) {

                    if ($req->profileImageText != '') {
                        $image_parts = explode(";base64,", $req->profileImageText);
                        file_put_contents(public_path('images/members/profile/' . $profileImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $profileImageSizes = explode(':', $mfnMemberConfig->profileImageSize);
                        $width             = $profileImageSizes[0];
                        $height            = $profileImageSizes[1];

                        $profileImage = new ResizeImage($req->file('profileImage'));
                        $profileImage->resizeTo($width, $height, 'exact');
                        $profileImage->saveImage(public_path('images/members/profile/' . $profileImageFilename), "100", false);
                    }
                }

                if ($signatureImageFilename != null) {

                    if ($req->signatureImageText != '') {
                        $image_parts = explode(";base64,", $req->signatureImageText);
                        file_put_contents(public_path('images/members/signature/' . $signatureImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $signatureImageSizes = explode(':', $mfnMemberConfig->signatureImageSize);
                        $width               = $signatureImageSizes[0];
                        $height              = $signatureImageSizes[1];

                        $signatureImage = new ResizeImage($req->file('signatureImage'));
                        $signatureImage->resizeTo($width, $height, 'exact');
                        $signatureImage->saveImage(public_path('images/members/signature/' . $signatureImageFilename), "100", false);
                    }
                }

                $data = array(
                    'errorMsg'               => null,
                    'profileImageFilename'   => $profileImageFilename,
                    'signatureImageFilename' => $signatureImageFilename,
                );
            } else {
                // return error messages
                $data = array(
                    'errorMsg' => $profileImageErrorMsg . '  ' . $signatureImageErrorMsg,
                );
            }

            return $data;
        } catch (\Exception $e) {
            // return error messages
            $data = array(
                'errorMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return $data;
        }
    }

    public function storeGuarantorImage($req, $loanId, $operationType = null)
    {
        try {
            $firstGurantorProfileImageErrorMsg   = null;
            $firstGurantorSignatureImageErrorMsg = null;

            $secondGurantorProfileImageErrorMsg   = null;
            $secondGurantorSignatureImageErrorMsg = null;

            $firstGurantorProfileImageFilename   = null;
            $firstGurantorSignatureImageFilename = null;

            $secondGurantorProfileImageFilename   = null;
            $secondGurantorSignatureImageFilename = null;

            $mfnMemberConfig = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);
            $mfnLoanConfig   = json_decode(DB::table('mfn_config')->where('title', 'loan')->first()->content);

            if ($operationType == 'update' || $this->isOpening) {
                $mfnLoanConfig->isGuarantorProfileImageMandatory   = 'no';
                $mfnLoanConfig->isGuarantorSignatureImageMandatory = 'no';
            }

            //// for first guarantor
            // profile image
            if ($mfnLoanConfig->isGuarantorProfileImageMandatory == 'yes' || ($req->firstGuarantorProfileImageText != '' || $req->hasFile('firstGuarantorProfileImage'))) {

                if ($req->firstGuarantorProfileImageText == '') {
                    $extension = $req->file('firstGuarantorProfileImage')->getClientOriginalExtension();
                } else {
                    $extension = 'jpg';
                }

                // generate profile file name
                $firstGurantorProfileImageFilename = 'fg' . Carbon::now()->format('YmdHis') . $loanId . '.' . $extension;

                // check is file exists or not
                if ($operationType != 'update' && file_exists(public_path('images/loans/guarantors/' . $firstGurantorProfileImageFilename))) {
                    $firstGurantorProfileImageErrorMsg = 'File already exists, file name: ' . $firstGurantorProfileImageFilename;
                }
            }

            // signature image
            if ($mfnLoanConfig->isGuarantorSignatureImageMandatory == 'yes' || ($req->firstGuarantorSignatureImageText != '' || $req->hasFile('firstGuarantorSignatureImage'))) {
                if ($req->firstGuarantorSignatureImageText == '') {
                    $extension = $req->file('firstGuarantorSignatureImage')->getClientOriginalExtension();
                } else {
                    $extension = 'jpg';
                }

                // generate signature image file name
                $firstGurantorSignatureImageFilename = 'fgs' . Carbon::now()->format('YmdHis') . $loanId . '.' . $extension;

                // check is file exists or not
                if ($operationType != 'update' && file_exists(public_path('images/loans/guarantor_signatures/' . $firstGurantorSignatureImageFilename))) {
                    $firstGurantorSignatureImageErrorMsg = 'File already exists, file name: ' . $firstGurantorSignatureImageFilename;
                }
            }

            /// for second guarantor
            if ($req->guarantorNames[1] != '' && $req->guarantorRelations[1] != '' && $req->guarantorAddresses[1] != '' && $req->guarantorPhones[1] != '') {

                // profile image
                if ($mfnLoanConfig->isGuarantorProfileImageMandatory == 'yes' || ($req->secondGuarantorProfileImageText != '' || $req->hasFile('secondGuarantorProfileImage'))) {

                    if ($req->secondGuarantorProfileImageText == '') {
                        $extension = $req->file('secondGuarantorProfileImage')->getClientOriginalExtension();
                    } else {
                        $extension = 'jpg';
                    }

                    // generate profile file name
                    $secondGurantorProfileImageFilename = 'sg' . Carbon::now()->format('YmdHis') . $loanId . '.' . $extension;

                    // check is file exists or not
                    if ($operationType != 'update' && file_exists(public_path('images/loans/guarantors/' . $secondGurantorProfileImageFilename))) {
                        $secondGurantorProfileImageErrorMsg = 'File already exists, file name: ' . $secondGurantorProfileImageFilename;
                    }
                }

                // signature image
                if ($mfnLoanConfig->isGuarantorSignatureImageMandatory == 'yes' || ($req->secondGuarantorSignatureImageText != '' || $req->hasFile('secondGuarantorSignatureImage'))) {
                    if ($req->secondGuarantorSignatureImageText == '') {
                        $extension = $req->file('secondGuarantorSignatureImage')->getClientOriginalExtension();
                    } else {
                        $extension = 'jpg';
                    }

                    // generate signature image file name
                    $secondGurantorSignatureImageFilename = 'sgs' . Carbon::now()->format('YmdHis') . $loanId . '.' . $extension;

                    // check is file exists or not
                    if ($operationType != 'update' && file_exists(public_path('images/loans/guarantor_signatures/' . $secondGurantorSignatureImageFilename))) {
                        $secondGurantorSignatureImageErrorMsg = 'File already exists, file name: ' . $secondGurantorSignatureImageFilename;
                    }
                }
            }

            // If no error then save images
            if ($firstGurantorProfileImageErrorMsg == null && $firstGurantorSignatureImageErrorMsg == null && $secondGurantorProfileImageErrorMsg == null && $secondGurantorSignatureImageErrorMsg == null) {
                // store images

                // for first guarantor
                if ($firstGurantorProfileImageFilename != null) {

                    if ($req->firstGuarantorProfileImageText != '') {
                        $image_parts = explode(";base64,", $req->firstGuarantorProfileImageText);
                        file_put_contents(public_path('images/loans/guarantors/' . $firstGurantorProfileImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $profileImageSizes = explode(':', $mfnMemberConfig->profileImageSize);
                        $width             = $profileImageSizes[0];
                        $height            = $profileImageSizes[1];

                        $profileImage = new ResizeImage($req->file('firstGuarantorProfileImage'));
                        $profileImage->resizeTo($width, $height, 'exact');
                        $profileImage->saveImage(public_path('images/loans/guarantors/' . $firstGurantorProfileImageFilename), "100", false);
                    }
                }

                if ($firstGurantorSignatureImageFilename != null) {

                    if ($req->firstGuarantorSignatureImageText != '') {
                        $image_parts = explode(";base64,", $req->firstGuarantorSignatureImageText);
                        file_put_contents(public_path('images/loans/guarantor_signatures/' . $firstGurantorSignatureImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $signatureImageSizes = explode(':', $mfnMemberConfig->signatureImageSize);
                        $width               = $signatureImageSizes[0];
                        $height              = $signatureImageSizes[1];

                        $signatureImage = new ResizeImage($req->file('firstGuarantorSignatureImage'));
                        $signatureImage->resizeTo($width, $height, 'exact');
                        $signatureImage->saveImage(public_path('images/loans/guarantor_signatures/' . $firstGurantorSignatureImageFilename), "100", false);
                    }
                }

                // for second guarantor
                if ($secondGurantorProfileImageFilename != null) {

                    if ($req->secondGuarantorProfileImageText != '') {
                        $image_parts = explode(";base64,", $req->secondGuarantorProfileImageText);
                        file_put_contents(public_path('images/loans/guarantors/' . $secondGurantorProfileImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $profileImageSizes = explode(':', $mfnMemberConfig->profileImageSize);
                        $width             = $profileImageSizes[0];
                        $height            = $profileImageSizes[1];

                        $profileImage = new ResizeImage($req->file('secondGuarantorProfileImage'));
                        $profileImage->resizeTo($width, $height, 'exact');
                        $profileImage->saveImage(public_path('images/loans/guarantors/' . $secondGurantorProfileImageFilename), "100", false);
                    }
                }

                if ($secondGurantorSignatureImageFilename != null) {

                    if ($req->secondGuarantorSignatureImageText != '') {
                        $image_parts = explode(";base64,", $req->secondGuarantorSignatureImageText);
                        file_put_contents(public_path('images/loans/guarantor_signatures/' . $secondGurantorSignatureImageFilename), base64_decode($image_parts[1]));
                    } else {
                        // get the profile image size from setting
                        $signatureImageSizes = explode(':', $mfnMemberConfig->signatureImageSize);
                        $width               = $signatureImageSizes[0];
                        $height              = $signatureImageSizes[1];

                        $signatureImage = new ResizeImage($req->file('secondGuarantorSignatureImage'));
                        $signatureImage->resizeTo($width, $height, 'exact');
                        $signatureImage->saveImage(public_path('images/loans/guarantor_signatures/' . $secondGurantorSignatureImageFilename), "100", false);
                    }
                }

                $data = array(
                    'errorMsg'                             => null,
                    'firstGuarantorProfileImageFilename'   => $firstGurantorProfileImageFilename,
                    'firstGurantorSignatureImageFilename'  => $firstGurantorSignatureImageFilename,
                    'secondGuarantorProfileImageFilename'  => $secondGurantorProfileImageFilename,
                    'secondGurantorSignatureImageFilename' => $secondGurantorSignatureImageFilename,
                );
            } else {
                // return error messages
                $data = array(
                    'errorMsg' => $firstGurantorProfileImageErrorMsg . '  ' . $firstGurantorSignatureImageErrorMsg,
                );
            }

            return $data;
        } catch (\Exception $e) {
            // return error messages
            $data = array(
                'errorMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return $data;
        }
    }

    public function getPassport($req, $operationType, $loan = null)
    {
        $errorMsg      = null;
        $mfnLoanConfig = json_decode(DB::table('mfn_config')->where('title', 'loan')->first()->content);

        if ($operationType == 'update') {
            // add vales with request
            $req->request->add(['memberId' => $loan->memberId, 'productId' => $loan->productId]);
        }

        if ($operationType != 'delete') {

            $rules = array(
                'memberId'             => 'required|bail',
                'disbursementDate'     => 'required',
                'productId'            => 'required|bail',
                'loanCode'             => 'required',
                'firstRepayDate'       => 'required',
                'loanCycle'            => 'required',
                'loanAmount'           => 'required',
                'insuranceAmount'      => 'required',
                'loanPurpose'          => 'required',
                'interestRate'         => 'required',
                'additionalFee'        => 'required',
                'loanFormFee'          => 'required',
                'paymentType'          => 'required',
                'ledgerId'             => 'required_if:paymentType,Bank',
                'chequeNo'             => 'required_if:paymentType,Bank',

            );

            if ($this->isOpening == true) {
                if ($this->loanType == 'Onetime') {
                    $rules['openingCollectionAmountPrincipal'] = 'required|numeric';
                    $rules['openingCollectionAmountInterest'] = 'required|numeric';
                } else {
                    $rules['openingCollectionAmount'] = 'required|numeric';
                }
            }

            if (!$this->isOpening) {
                $rules = array_merge($rules, array(
                    'guarantorNames.0'     => 'required',
                    'guarantorRelations.0' => 'required',
                    'guarantorAddresses.0' => 'required',
                    'guarantorPhones.0'    => ['required', new MobileNo],
                    'guarantorNames.1'     => 'required_with:guarantorRelations.1,guarantorAddresses.1,guarantorPhones.1',

                    'guarantorRelations.1' => 'required_with:guarantorNames.1,guarantorAddresses.1,guarantorPhones.1',

                    'guarantorAddresses.1' => 'required_with:guarantorNames.1,guarantorRelations.1,guarantorPhones.1',

                    'guarantorPhones.1'    => ['required_with:guarantorNames.1,guarantorRelations.1,guarantorAddresses.1', 'nullable', new MobileNo],
                    'familyFTM'            => 'required|numeric',
                    'familyFTF'            => 'required|numeric',
                    'familyPTM'            => 'required|numeric',
                    'familyPTF'            => 'required|numeric',
                    'outsideFTM'           => 'required|numeric',
                    'outsideFTF'           => 'required|numeric',
                    'outsidePTM'           => 'required|numeric',
                    'outsidePTF'           => 'required|numeric',
                    'fullTimeMaleWage'     => 'required|numeric',
                    'fullTimeFemaleWage'   => 'required|numeric',
                    'partTimeMaleWage'     => 'required|numeric',
                    'partTimeFemaleWage'   => 'required|numeric',
                    'businessName'         => 'required',
                    'businessLocation'     => 'required',
                ));
            }

            if ($this->loanType == 'Regular') {
                $rules = array_merge($rules, array(
                    'repaymentFrequencyId'    => 'required',
                    'numberOfInstallment'     => 'required|numeric',
                    'repayAmount'             => 'required|numeric',
                    'ineterestAmount'         => 'required|numeric',
                    'installmentAmount'       => 'required|numeric',
                    'extraInstallmentAmount'  => 'required|numeric',
                    'actualInstallmentAmount' => 'required|numeric',
                    'lastInstallmentAmount'   => 'required|numeric',
                ));
            }
            if ($this->loanType == 'Onetime') {
                $rules = array_merge($rules, array(
                    'loanDurationInMonth' => 'required|numeric',
                ));
            }

            // validate image is requies or not on store operation and not opening
            if ($operationType == 'store' && !$this->isOpening) {
                $memberDetails = DB::table('mfn_member_details')
                    ->where('memberId', $req->memberId)
                    ->select('profileImage', 'signatureImage')
                    ->first();

                if ($memberDetails->profileImage == '' && $mfnLoanConfig->isMemberProfileImageMandatory == 'yes') {
                    $rules['profileImage'] = 'required_without:profileImageText|image|mimes:jpeg,bmp,png,gif,svg';
                }
                if ($memberDetails->signatureImage == '' && $mfnLoanConfig->isMemberSignatureImageMandatory == 'yes') {
                    $rules['signatureImage'] = 'required_without:signatureImageText|image|mimes:jpeg,bmp,png,gif,svg';
                }

                if ($mfnLoanConfig->isGuarantorProfileImageMandatory == 'yes') {
                    $rules['firstGuarantorProfileImage'] = 'required_without:firstGuarantorProfileImageText|image';

                    // if any information of second guarantors is given then image is mandatory
                    if ($req->guarantorNames[1] != '' && $req->guarantorRelations[1] != '' && $req->guarantorAddresses[1] != '' && $req->guarantorPhones[1] != '') {
                        $rules['secondGuarantorProfileImage'] = 'required_without:secondGuarantorProfileImageText|image';
                    }
                }
                if ($mfnLoanConfig->isGuarantorSignatureImageMandatory == 'yes') {
                    $rules['firstGuarantorSignatureImage'] = 'required_without:firstGuarantorSignatureImageText|image';

                    // if any information of second guarantors is given then image is mandatory
                    if ($req->guarantorNames[1] != '' && $req->guarantorRelations[1] != '' && $req->guarantorAddresses[1] != '' && $req->guarantorPhones[1] != '') {
                        $rules['secondGuarantorSignatureImage'] = 'required_without:secondGuarantorSignatureImageText|image';
                    }
                }
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'memberId'                          => 'Member',
                'disbursementDate'                  => 'Disbursement Date',
                'productId'                         => 'Product',
                'loanCode'                          => 'Loan Code',
                'repaymentFrequencyId'              => 'Repayment Frequency',
                'numberOfInstallment'               => 'Number Of Installment',
                'firstRepayDate'                    => 'First RepayDate',
                'loanCycle'                         => 'Loan Cycle',
                'loanAmount'                        => 'Loan Amount',
                'insuranceAmount'                   => 'Insurance Amount',
                'loanPurpose'                       => 'Loan Purpose',
                'interestRate'                      => 'Interest Rate',
                'additionalFee'                     => 'Additional Fee',
                'loanFormFee'                       => 'Loan FormFee',
                'paymentType'                       => 'Payment Type',
                'repayAmount'                       => 'Repay Amount',
                'ineterestAmount'                   => 'Ineterest Amount',
                'installmentAmount'                 => 'Installment Amount',
                'extraInstallmentAmount'            => 'Extra Installment Amount',
                'actualInstallmentAmount'           => 'Actual Installment Amount',
                'lastInstallmentAmount'             => 'Last Installment Amount',
                'guarantorNames.0'                  => 'First Gurantor Name',
                'guarantorRelations.0'              => 'First Gurantor Relation',
                'guarantorAddresses.0'              => 'First Gurantor Address',
                'guarantorPhones.0'                 => 'First Gurantor Contact No',
                'guarantorNames.1'                  => 'Second Gurantor Name',
                'guarantorRelations.1'              => 'Second Gurantor Relation',
                'guarantorAddresses.1'              => 'Second Gurantor Address',
                'guarantorPhones.1'                 => 'Second Gurantor Contact No',
                'familyFTM'                         => 'Family Employment Full Time Male',
                'familyFTF'                         => 'Family Employment Full Time Female',
                'familyPTM'                         => 'Family Employment Part Time Male',
                'familyPTF'                         => 'Family Employment Part Time Female',
                'outsideFTM'                        => 'Outside Family Employment Full Time Male',
                'outsideFTF'                        => 'Outside Family Employment Full Time Female',
                'outsidePTM'                        => 'Outside Family Employment Part Time Male',
                'outsidePTF'                        => 'Outside Family Employment Part Time Female',
                'fullTimeMaleWage'                  => 'Full Time Male Wage',
                'fullTimeFemaleWage'                => 'Full Time Female Wage',
                'partTimeMaleWage'                  => 'Part Time Male Wage',
                'partTimeFemaleWage'                => 'Part Time Female Wage',
                'businessName'                      => 'Business Name',
                'businessLocation'                  => 'Business Location',
                'profileImage'                      => 'Member Profile Image',
                'profileImageText'                  => 'Member Profile Image',
                'signatureImage'                    => 'Member Signature Image',
                'signatureImageText'                => 'Member Signature Image',
                'firstGuarantorProfileImage'        => 'First Guarantor Profile Image',
                'firstGuarantorProfileImageText'    => 'First Guarantor Profile Image',
                'firstGuarantorSignatureImage'      => 'First Guarantor Signature Image',
                'firstGuarantorSignatureImageText'  => 'First Guarantor Signature Image',
                'secondGuarantorProfileImage'       => 'Second Guarantor Profile Image',
                'secondGuarantorProfileImageText'   => 'Second Guarantor Profile Image',
                'secondGuarantorSignatureImage'     => 'Second Guarantor Signature Image',
                'secondGuarantorSignatureImageText' => 'Second Guarantor Signature Image',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // revalidate data
            if ($errorMsg == null) {
                $reValidateMsg = $this->reValidateData($req, $operationType);

                if ($reValidateMsg != null) {
                    $errorMsg = $reValidateMsg;
                }
            }

            if ($this->isOpening == true) {
                // opening collection amount could not be equal or greater than lona repay amount
                if ($this->loanType == 'Onetime') {
                    if ($req->openingCollectionAmountPrincipal >= $req->loanAmount) {
                        $errorMsg = 'Opening Collection Principal amount could not be greater than Loan amount';
                    }
                } else {
                    if ($req->openingCollectionAmount >= $req->repayAmount) {
                        $errorMsg = 'Opening Collection amount could not be greater than Loan Reapay amount';
                    }
                }
            }
        }

        // set required valiables
        if ($operationType == 'store') {
            $branchId           = Auth::user()->branch_id;
            $sysDate            = MfnService::systemCurrentDate($branchId);
            $disbursementDate   = date('Y-m-d', strtotime($req->disbursementDate));
            $member             = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $samityId           = $member->samityId;
        } else {
            $branchId           = $loan->branchId;
            $sysDate            = MfnService::systemCurrentDate($branchId);
            $disbursementDate   = $loan->disbursementDate;
            $samityId           = $loan->samityId;
        }

        // first repay date could not be less than disbursement Date
        if ($operationType != 'delete') {
            if (date('Y-m-d', strtotime($req->firstRepayDate)) < $disbursementDate) {
                $errorMsg = 'First Repay date could not be less than Disbursement Date';
            }

            // first repay date should be in samity day
            $isFirstReapayDateInSamityDay = MfnService::isSamityDay($samityId, $req->firstRepayDate);

            if (!$isFirstReapayDateInSamityDay) {
                $errorMsg = 'First Repay date shoud be in samity day.';
            }
        }

        // if it is from opening
        if ($this->isOpening) {
            $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'This is from opening, Branch should be on Software start date, i.e. ' . date('d-m-Y', strtotime($branchSoftwareStartDate));
            }

            if ($disbursementDate > $branchSoftwareStartDate) {
                $errorMsg = 'Branch is on Opening date, Disbursement Date should be maximum ' . date('d-m-Y', strtotime($branchSoftwareStartDate));
            }
        } elseif ($sysDate != $disbursementDate) {
            $errorMsg = 'Disbursement Date is not matched with Branch date.';
        }

        // check this can be deleted/updated or not
        if ($operationType != 'store' && $errorMsg == null) {
            $canEditOrDeleteMsg = $this->canUpdateDelete($loan->id, $operationType);

            if ($canEditOrDeleteMsg !== true) {
                $errorMsg = $canEditOrDeleteMsg;
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function getData(Request $req)
    {
        if ($req->context == 'member') {

            $member = DB::table('mfn_members')
                ->where('id', $req->memberId)
                ->select('id', 'primaryProductId', 'branchId')
                ->first();
            $memberFundingOrgId     = DB::table('mfn_loan_products')->where('id', $member->primaryProductId)->value('fundingOrgId');
            $memberPrimaryProductId = $member->primaryProductId;

            $disbursementDate = Carbon::parse($req->disbursementDate)->format('Y-m-d');

            $branchAssingedProductIds = json_decode(DB::table('mfn_branch_products')
                ->where('branchId', $member->branchId)
                ->value('loanProductIds'));

            $loanProducts = $this->getMemberEligibleLoanProducts($member, $disbursementDate, $this->loanType);

            $loanCycle = (int)DB::table('mfn_loans')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $member->id],
                ])
                ->max('loanCycle') + 1;

            // get image  source from member details
            $profileImage  = $signatureImage  = null;
            $memberDetails = DB::table('mfn_member_details')->where('memberId', $member->id)->select('profileImage', 'signatureImage')->first();

            if ($memberDetails->profileImage != '') {
                $profileImage = asset('images/members/profile') . '/' . $memberDetails->profileImage;
            }
            if ($memberDetails->signatureImage != '') {
                $signatureImage = asset('images/members/signature') . '/' . $memberDetails->signatureImage;
            }

            $data = array(
                'loanProducts'   => $loanProducts,
                'loanCycle'      => $loanCycle,
                'profileImage'   => $profileImage,
                'signatureImage' => $signatureImage,
            );
        }

        if ($req->context == 'product') {
            if ($this->isOpening) {
                $loanCode  = self::generateLoanCode($req->memberId, $req->productId, $req->loanCycle);
                $loanCycle = $req->loanCycle;
            } else {
                $loanCode  = self::generateLoanCode($req->memberId, $req->productId);
                $loanCycle = (int)DB::table('mfn_loans')
                    ->where([
                        ['is_delete', 0],
                        ['memberId', $req->memberId],
                    ])
                    ->max('loanCycle') + 1;
            }

            $product = DB::table('mfn_loan_products')->where('id', $req->productId)->first();

            $repaymentInfos = [];

            // get product repayment informations
            $repayments = json_decode($product->repaymentInfo);

            $repaymentFrequencies = DB::table('mfn_loan_repayment_frequency')->get();

            // if product is regular
            if ($product->productTypeId == 1) {
                foreach ($repayments as $key => $repayment) {
                    $repaymentInfos['repaymentFrequencies'][$repayment->repaymentFrequencyId]         = $repaymentFrequencies->where('id', $repayment->repaymentFrequencyId)->first()->name;
                    $repaymentInfos['eligibleNumberOfInstallments'][$repayment->repaymentFrequencyId] = $repayment->eligibleNumberOfInstallments;
                }
            }
            // if product is onetime
            elseif ($product->productTypeId == 2) {
                $repaymentInfos['eligibleMonths'] = explode(',', $repayments->eligibleMonths);
            }

            $additionalFee = $loanCycle > 1 ? $product->additionalFee : $product->additionalFreeForFirstTime;

            $data = array(
                'product'        => $product,
                'loanCode'       => $loanCode,
                'repaymentInfos' => $repaymentInfos,
                'additionalFee'  => $additionalFee,
            );

            if ($this->loanType == 'Onetime') {
                $sysDate           = MfnService::systemCurrentDate(Auth::user()->branch_id);
                $interestRate      = MfnService::getInterestRateForOnetimeLoan($req->productId, $sysDate);
                $interestCalMethod = null;

                if ($interestRate != null) {
                    $interestCalMethod = DB::table('mfn_loan_interest_calculation_methods')->where('id', $interestRate->interestCalculationMethodId)->value('name');
                    $interestRate      = $interestRate->interestRatePerYear;
                }

                $data = array_merge($data, array(
                    'interestCalMethod' => $interestCalMethod,
                    'interestRate'      => $interestRate,
                ));
            }
        }

        if ($req->context == 'loanCycle') {
            $product       = DB::table('mfn_loan_products')->where('id', $req->productId)->first();
            $loanCode      = self::generateLoanCode($req->memberId, $req->productId, $req->loanCycle);
            $additionalFee = $req->loanCycle > 1 ? $product->additionalFee : $product->additionalFreeForFirstTime;
            $data          = array(
                'loanCode'      => $loanCode,
                'additionalFee' => $additionalFee,
            );
        }

        if ($req->context == 'repaymentFrequency') {
            $member         = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $firstRepayDate = MfnService::getFirstRepayDate($member->samityId, $req->productId, $req->disbursementDate, $req->repaymentFrequencyId);

            $firstRepayDate = Carbon::parse($firstRepayDate)->format('d-m-Y');

            $data = array(
                'firstRepayDate' => $firstRepayDate,
            );
        }

        if ($req->context == 'InterestNInstallment' && $this->loanType == 'Regular') {
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

            $interestRate = MfnService::getInterestRateForRegularLoan($req->productId, $req->repaymentFrequencyId, $req->numberOfInstallment, $sysDate);

            $data = array(
                'interestRate' => $interestRate,
            );

            if ($interestRate != null) {
                $interestCalMethod = DB::table('mfn_loan_interest_calculation_methods')->where('id', $interestRate->interestCalculationMethodId)->value('name');

                $repayAmount       = round($req->loanAmount * $interestRate->interestRateIndex);
                $ineterestAmount   = $repayAmount - $req->loanAmount;
                $installmentDetils = MfnService::generateInstallmentDetails($req->loanAmount, $req->numberOfInstallment, $interestRate->interestRateIndex, $this->loanType);
                $installmentAmount = $extraInstallmentAmount = $lastInstallmentAmount = $actualInastallmentAmount = 0;
                if ($installmentDetils != null) {
                    $installmentAmount        = $installmentDetils['installmentAmount'];
                    $extraInstallmentAmount   = round($installmentDetils['extraInstallmentAmount'], 2);
                    $lastInstallmentAmount    = $installmentDetils['lastInstallmentAmount'];
                    $actualInastallmentAmount = round($installmentDetils['actualInastallmentAmount'], 2);
                }

                $data = array_merge($data, array(
                    'interestCalMethod'        => $interestCalMethod,
                    'repayAmount'              => $repayAmount,
                    'ineterestAmount'          => $ineterestAmount,
                    'installmentAmount'        => $installmentAmount,
                    'extraInstallmentAmount'   => $extraInstallmentAmount,
                    'lastInstallmentAmount'    => $lastInstallmentAmount,
                    'lastInstallmentAmount'    => $lastInstallmentAmount,
                    'actualInastallmentAmount' => $actualInastallmentAmount,
                ));
            }
        }

        if ($req->context == 'onetimeLoanDuration') {
            $member         = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $firstRepayDate = MfnService::getFirstRepayDate($member->samityId, $req->productId, $req->disbursementDate, null, $req->loanDurationInMonth);
            $firstRepayDate = date('d-m-Y', strtotime($firstRepayDate));

            $data = array(
                'firstRepayDate' => $firstRepayDate,
            );
        }

        return response()->json($data);
    }

    public static function generateLoanCode($memberId, $productId, $loanCycle = null)
    {
        $member  = DB::table('mfn_members')->where('id', $memberId)->first();
        $product = DB::table('mfn_loan_products')->where('id', $productId)->first();

        $mfnGnlConfig  = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $mfnLoanConfig = json_decode(DB::table('mfn_config')->where('title', 'loan')->first()->content);

        if ($loanCycle == null) {
            $loanCycle = (int)DB::table('mfn_loans')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $member->id],
                ])
                ->max('loanCycle') + 1;
        }

        $loanCode = $member->memberCode . $mfnGnlConfig->codeSeperator . str_pad($loanCycle, $mfnLoanConfig->loanCodeLengthItSelf, "0", STR_PAD_LEFT);

        if ($mfnLoanConfig->isProductPrefixRequiredInLoanCode == 'yes') {
            $loanCode = $product->shortName . $mfnGnlConfig->codeSeperator . $loanCode;
        }

        return $loanCode;
    }

    public function reValidateData($req, $operationType)
    {
        $errorMsg = null;
        if ($operationType == 'store') {
            // check this member is eligible to take this product loan or not
            $eligibleProducts = $this->getMemberEligibleLoanProducts((int)$req->memberId, $req->disbursementDate, $this->loanType);
            if (!in_array($req->productId, $eligibleProducts->pluck('id')->toArray())) {
                $errorMsg = 'Member is not Eligible to take this product Loan';
            }

            // validate loan code
            if ($this->isOpening) {
                $loanCode = self::generateLoanCode($req->memberId, $req->productId, $req->loanCycle);
            } else {
                $loanCode = self::generateLoanCode($req->memberId, $req->productId);

                // validate loan cycle
                $loanCycle = (int)DB::table('mfn_loans')
                    ->where([
                        ['is_delete', 0],
                        ['memberId', $req->memberId],
                    ])
                    ->max('loanCycle') + 1;

                if ($loanCycle != $req->loanCycle) {
                    $errorMsg = 'Something went wrong: Loan Cycle may not be correct.';
                }
            }

            if ($loanCode != $req->loanCode) {
                $errorMsg = 'Something went wrong: Loan Code may not be correct.';
            }
        }

        $product    = DB::table('mfn_loan_products')->where('id', $req->productId)->first();
        $repayments = json_decode($product->repaymentInfo);

        // when it is a egular product
        // check this product satisfies repaymentFrequency and  numberOfInstallment or not
        if ($product->productTypeId == 1) {
            $repaymentDataFlag = false;
            // get product repayment informations

            foreach ($repayments as $key => $repayment) {
                if ($req->repaymentFrequencyId == $repayment->repaymentFrequencyId) {
                    $eligibleNumberOfInstallments = explode(',', $repayment->eligibleNumberOfInstallments);
                    if (in_array($req->numberOfInstallment, $eligibleNumberOfInstallments)) {
                        $repaymentDataFlag = true;
                        break;
                    }
                }
            }

            if ($repaymentDataFlag == false) {
                $errorMsg = 'Something went wrong: Repayment Frequency or Number Of Installment may not be correct.';
            }
        }

        // in case of onetime loan
        // check this product satisfies loandurationInMonth or not
        if ($product->productTypeId == 2) {
            $eligibleMonths = explode(',', $repayments->eligibleMonths);
            if (!in_array((int)$req->loanDurationInMonth, $eligibleMonths)) {
                $errorMsg = 'Something went wrong: Loan Duration may not be correct.';
            }
        }

        $disbursementDate = date('Y-m-d', strtotime($req->disbursementDate));
        // check total repay amount is correct or not if it is regular loan
        if ($this->loanType == 'Regular') {
            $interestRate = MfnService::getInterestRateForRegularLoan($req->productId, $req->repaymentFrequencyId, $req->numberOfInstallment, $disbursementDate);

            if ($interestRate == null) {
                $errorMsg = 'Interest rate is not defined at this disbursement date.';
                return $errorMsg;
            }

            $repayAmount  = round($req->loanAmount * $interestRate->interestRateIndex);

            if ($repayAmount != $req->repayAmount) {
                $errorMsg = 'Something went wrong: Repay amount may not be correct.';
            }

            // validate installment amount, extra installment Amount, actual Inastallment Amount
            $installmentDetils = MfnService::generateInstallmentDetails($req->loanAmount, $req->numberOfInstallment, $interestRate->interestRateIndex, $this->loanType);
            $installmentAmount = $extraInstallmentAmount = $lastInstallmentAmount = $actualInastallmentAmount = 0;
            if ($installmentDetils != null) {
                $installmentAmount        = $installmentDetils['installmentAmount'];
                $extraInstallmentAmount   = round($installmentDetils['extraInstallmentAmount'], 2);
                $lastInstallmentAmount    = $installmentDetils['lastInstallmentAmount'];
                $actualInastallmentAmount = round($installmentDetils['actualInastallmentAmount'], 2);
            }

            if ($installmentAmount != $req->installmentAmount) {
                $errorMsg = 'Something went wrong: Installment amount may not be correct.';
            }
            if ($extraInstallmentAmount != $req->extraInstallmentAmount) {
                $errorMsg = 'Something went wrong: Extra Installment amount may not be correct.';
            }
            if ($lastInstallmentAmount != $req->lastInstallmentAmount) {
                $errorMsg = 'Something went wrong: Last Installment amount may not be correct.';
            }
            if ($actualInastallmentAmount != $req->actualInstallmentAmount) {
                $errorMsg = 'Something went wrong: Actual Installment amount may not be correct.';
            }
        }

        if (!$this->isOpening) {
            // check additionalFee and loanformFee
            $additionalFee = $req->loanCycle == 1 ? $product->additionalFreeForFirstTime : $product->additionalFee;
            if ($additionalFee != $req->additionalFee) {
                $errorMsg = 'Something went wrong: Additional Fee may not be correct.';
            }
            if ($product->formFee != $req->loanFormFee) {
                $errorMsg = 'Something went wrong: Loan Form Fee may not be correct.';
            }
        }

        return $errorMsg;
    }

    public function getMemberEligibleLoanProducts($member, $date, $loanType)
    {
        if ((bool)strtotime($date) == false) {
            return false;
        }

        if (is_int($member)) {
            $member = DB::table('mfn_members')->where('id', $member)->first();
        }
        $date = date('Y-m-d', strtotime($date));

        $memberPrimaryProductId = $member->primaryProductId;
        $memberFundingOrgId     = DB::table('mfn_loan_products')->where('id', $member->primaryProductId)->value('fundingOrgId');

        $branchAssingedProductIds = json_decode(DB::table('mfn_branch_products')
            ->where('branchId', $member->branchId)
            ->value('loanProductIds'));

        $runnigLonProductIds = DB::table('mfn_loans')
            ->where([
                ['is_delete', 0],
                ['memberId', $member->id],
            ])
            ->where(function ($query) use ($date) {
                $query->where('loanCompleteDate', null)
                    ->orWhere('loanCompleteDate', '0000-00-00')
                    ->orWhere('loanCompleteDate', '>', $date);
            })
            ->pluck('productId')
            ->toArray();

        $loanProducts = DB::table('mfn_loan_products')
            ->where([
                ['is_delete', 0],
                ['fundingOrgId', $memberFundingOrgId],
                ['startDate', '<=', $date],
            ])
            ->where(function ($query) use ($memberPrimaryProductId) {
                $query->where('isPrimaryProduct', 0)
                    ->orWhere('id', $memberPrimaryProductId);
            })
            ->where(function ($query) use ($runnigLonProductIds) {
                $query->whereNotIn('id', $runnigLonProductIds)
                    ->orWhere('isMultipleLoanAllowed', 1);
            })
            ->whereIn('id', $branchAssingedProductIds);

        if ($loanType == 'Regular') {
            $loanProducts->where('productTypeId', 1); // 1 for regular
        }
        if ($loanType == 'Onetime') {
            $loanProducts->where('productTypeId', 2); // 2 for onetime
        }

        $loanProducts = $loanProducts
            ->select(DB::raw("id, CONCAT(productCode, ' - ', name) AS name"))
            ->get();

        return $loanProducts;
    }

    /**
     * It returns a msg, if msg === true, then it can be updated, deleted
     *
     * @param   [int]  $loanId         [id of the loan]
     * @param   [string]  $operationType  ['update' or 'delete]
     *
     * @return  [boolean or a strinng]
     */
    public function canUpdateDelete($loanId, $operationType)
    {
        $msg = true;

        // we will ignore OB type collection if it is in opening
        $anyCollectionExists = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
                ['loanId', $loanId],
            ]);

        if ($this->isOpening) {
            $anyCollectionExists->where('paymentType', '!=', 'OB');
        }

        $anyCollectionExists = $anyCollectionExists->exists();

        if ($anyCollectionExists) {
            $msg = 'Transaction Exists, you can not ' . $operationType . ' it.';
        }

        return $msg;
    }
}
