<?php

namespace App\Http\Controllers\MFN\Samity;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\SamityDayChange;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class SamityDayChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {
        if (!$req->ajax()) {

            $branchList = Branch::where('is_delete', 0)
                ->where('id', '>', 1)
                ->orderBy('branch_code')
                ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS branch_name"))
                ->get();

            return view('MFN.SamityDayChange.index', compact('branchList'));
        }

        $columns = ['mfn_samity.name', 'msdc.oldSamityDay', 'newSamityDay', 'effectiveDate', 'gnl_branchs.branch_code', 'gnl_branchs.branch_name'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search           = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $startDate        = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        $endDate          = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        $branchId         = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        $samityNameOrCode = (empty($req->input('samityNameOrCode'))) ? null : $req->input('samityNameOrCode');

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $samityDayChanges = DB::table('mfn_samity_day_changes as msdc')
            ->leftJoin('gnl_branchs', 'gnl_branchs.id', 'msdc.branchId')
            ->leftJoin('mfn_samity', 'mfn_samity.id', 'msdc.samityId')
            ->whereIn('msdc.branchId', $accessAbleBranchIds)
            ->where('msdc.is_delete', 0)
            ->select(DB::raw('msdc.*, CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branch, mfn_samity.name AS samity'))
            ->orderBy($order, $dir);

        if ($search != null) {
            $samityDayChanges->where(function ($query) use ($search) {
                $query->where('mfn_samity.name', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_code', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('msdc.oldSamityDay', 'LIKE', "%{$search}%")
                    ->orWhere('msdc.newSamityDay', 'LIKE', "%{$search}%")
                    ->orWhere('msdc.effectiveDate', 'LIKE', "%{$search}%");
            });
        }
        if ($branchId != null) {
            $samityDayChanges->where(function ($query) use ($branchId) {
                $query->where('msdc.branchId', '=', $branchId);
            });
        }

        if ($samityNameOrCode != null) {
            $samityDayChanges->where(function ($query) use ($samityNameOrCode) {
                $query->where('mfn_samity.name', 'LIKE', "%{$samityNameOrCode}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$samityNameOrCode}%");
            });
        }

        if ($startDate != null || $endDate != null) {

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate   = Carbon::parse($endDate)->format('Y-m-d');

            $samityDayChanges->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('msdc.effectiveDate', [$startDate, $endDate]);
            });
        }

        $totalData        = (clone $samityDayChanges)->count();
        $samityDayChanges = $samityDayChanges->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($samityDayChanges as $key => $samityDayChange) {
            $samityDayChanges[$key]->effectiveDate = Carbon::parse($samityDayChange->effectiveDate)->format('d-m-Y');
            $samityDayChanges[$key]->sl            = $sl++;
            $samityDayChanges[$key]->id            = encrypt($samityDayChange->id);
            $samityDayChanges[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $samityDayChanges[$key]->id );
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $samityDayChanges,
        );

        return response()->json($data);
    }

    public function view($samityDC)
    {
        $samityDayChange = SamityDayChange::where('id', decrypt($samityDC))->first();
        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $samityDayChange->branchId) {
            return '';
        }

        $branch = DB::table('gnl_branchs')
            ->where('id', $samityDayChange->branchId)
            ->select('branch_name', 'branch_code')
            ->first();

        $samity = Samity::where('id', $samityDayChange->samityId)->first()->name;

        $data = array(
            'samity'          => $samity,
            'branch'          => $branch,
            'samityDayChange' => $samityDayChange,
            'effectiveDate'   => Carbon::parse($samityDayChange->effectiveDate)->format('d-m-Y'),
        );

        return view('MFN.SamityDayChange.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (count($accessAbleBranchIds) > 1) {
            $branches = DB::table('gnl_branchs')
                ->whereIn('id', $accessAbleBranchIds)
                ->where('id', '!=', 1)
                ->select(DB::raw("CONCAT(branch_code, ' - ', branch_name) AS name, id"))
                ->get();
            $samities = [];
        } else {
            $branches = [];
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['openingDate', '<=', $sysDate],
                ])
                ->whereIn('branchId', $accessAbleBranchIds)
                ->select(DB::raw("CONCAT(samityCode, ' - ', name) AS name, id"))
                ->get();
        }

        $workingDays = MfnService::getWorkingWeekDays();

        $data = array(
            'branches'    => $branches,
            'samities'    => $samities,
            'workingDays' => $workingDays,
            'sysDate'     => $sysDate,
        );

        return view('MFN.SamityDayChange.add', $data);
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

        DB::beginTransaction();

        try {
            $samity = Samity::find($req->samity);

            $samityDC                = new SamityDayChange;
            $samityDC->samityId      = $samity->id;
            $samityDC->branchId      = $samity->branchId;
            $samityDC->oldSamityDay  = $samity->samityDay;
            $samityDC->newSamityDay  = $req->newSamityDay;
            $samityDC->effectiveDate = Carbon::parse($req->effectiveDate);
            $samityDC->created_at    = Carbon::now();
            $samityDC->created_by    = Auth::user()->id;
            $samityDC->save();

            $samity->samityDay = $req->newSamityDay;
            $samity->save();

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
                'message'    => 'Something went wrong.',
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

        $samityDayChange = SamityDayChange::where('id', decrypt($req->samityDC))->first();
        $samities        = Samity::where('is_delete', 0)->get();
        $workingDays     = MfnService::getWorkingWeekDays();
        $effectiveDate   = Carbon::parse($samityDayChange->effectiveDate)->format('d-m-Y');
        $data            = array(
            'samities'        => $samities,
            'workingDays'     => $workingDays,
            'samityDayChange' => $samityDayChange,
            'effectiveDate'   => $effectiveDate,
        );

        return view('MFN.SamityDayChange.edit', $data);
    }

    public function update(Request $req)
    {
        $samityDC = SamityDayChange::find(decrypt($req->samityDC));
        $passport = $this->getPassport($req, $operationType = 'update', $samityDC);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // $samity = SamityDayChange::where('id', $req->samityDC)->first();

        // $samity               = SamityDayChange::find($samityDC->id);
        $samityDC->newSamityDay = $req->newSamityDay;
        $samityDC->updated_by   = Auth::user()->id;
        $samityDC->updated_at   = Carbon::now();
        $samityDC->save();

        $notification = array(
            'message'    => 'Successfully Updated',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $samityDayChangeData = DB::table('mfn_samity_day_changes')->where('id', decrypt($req->samity_day_id))->first();

        $passport = $this->getPassport($req, $operationType = 'delete', $samityDayChangeData);

        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $samityDayChangeData            = SamityDayChange::find($samityDayChangeData->id);
        $samityDayChangeData->is_delete = 1;
        $samityDayChangeData->save();

        DB::table('mfn_samity')->where('id', $samityDayChangeData->samityId)->update(['samityDay' => $samityDayChangeData->oldSamityDay]);

        $notification = array(
            'message'    => 'Successfully Deleted',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function getPassport($req, $operationType, $samityDC = null)
    {
        $errorMsg = null;
        if ($operationType == 'store') {
            $branchId         = Auth::user()->branch_id;
            $samityId         = $req->samity;
            $effectiveDate    = Carbon::parse($req->effectiveDate)->format('Y-m-d');
            $currentSamityDay = DB::table('mfn_samity')->where('id', $req->samity)->first()->samityDay;

            $validator = Validator::make($req->all(), [
                'samity'       => 'required',
                'newSamityDay' => 'required',
            ]);

            $attributes = array(
                'samity'       => 'Samity',
                'newSamityDay' => 'New Samity Day',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        } else {
            $branchId         = $samityDC->branchId;
            $samityId         = $samityDC->samityId;
            $effectiveDate    = $samityDC->effectiveDate;
            $currentSamityDay = $samityDC->oldSamityDay;
        }
        $sysDate = MfnService::systemCurrentDate($branchId);

        if ($operationType != 'delete') {

            // CHECK THAT NEW SAMITY DAY IS SAME AS CURRENT SAMITY DAY OR NOT
            if ($currentSamityDay == $req->newSamityDay) {
                $errorMsg = 'New Samity Day should not be same as current Samity Day.';
            }
        }

        if ($operationType == 'update') {
            $validator = Validator::make($req->all(), [
                'newSamityDay' => 'required',
            ]);

            $attributes = array(
                'newSamityDay' => 'New Samity Day',
            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // CHECK THE TRANSACTION DATE IS EQUAL TO THE BRANCH DATE OR NOT
        if ($effectiveDate != $sysDate) {
            $errorMsg = 'Branch date is not on ' . Carbon::parse($effectiveDate)->format('d-m-Y');
        }

        // CHECK ANY TRANSACTION IS EXISTS TODAY/ AFTER SYSTEMDATE
        // here we checked $errorMsg is null or not, if it is null then we can continue
        if ($errorMsg == null) {
            $anyLoanExists = DB::table('mfn_loans')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samityId],
                    ['disbursementDate', '>=', $sysDate],
                ])
                ->exists();

            $anyLonCollectionExists = DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['samityId', $samityId],
                    ['collectionDate', '>=', $sysDate],
                ])
                ->exists();

            if ($anyLoanExists || $anyLonCollectionExists) {
                $errorMsg = 'Loan/Collection exits today/after this date.';
            }

            $anySavingsDepositExists = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['samityId', $samityId],
                    ['date', '>=', $sysDate],
                ])
                ->exists();

            if ($anySavingsDepositExists) {
                $errorMsg = 'Savings Deposit exits today/after this date.';
            }
        }

        // IF CHANGING SAMITY DAY MAKES ANY LOAN SCHEDULE DATE INAPPROPRIATE
        // THEN IT COULD NOT BE DONE
        // THIS CODE WILL BE DONE AFTER LOAN SCHEDULE FINISED
        /* code will be here */
        $isValid = $errorMsg == null ? true : false;

        if ($isValid == true) {
            $dayChange = self::isDayChangeEffectLoanSchedule();
            if ($dayChange['isEffected'] == false) {
                $isValid  = $dayChange['isEffected'];
                $errorMsg = $dayChange['errorMsg'];
            }
        }

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function isDayChangeEffectLoanSchedule()
    {
        $errorMsg = null;
        $sysDate  = MfnService::systemCurrentDate(Auth::user()->branch_id);
        // $sysDate        = '2000-03-22';
        $monthStartDate = date('Y-m', strtotime($sysDate)) . '-01';
        $monthEndDate   = date('Y-m-t', strtotime($monthStartDate));

        $loanInfo = DB::table('mfn_loans')
            ->where('is_delete', 0)
            ->where('branchId', Auth::user()->branch_id)
            ->select('id', 'loanCode')
            ->get();

        $loanIds = $loanInfo->pluck('id')->toArray();

        MfnService::$requirement = 'installments';
        $loanSchedule            = MfnService::generateLoanSchedule($loanIds);

        $thisMonthLoanSchedule = array_filter($loanSchedule, function ($schedule) use ($monthStartDate, $monthEndDate, $sysDate) {

            if ($schedule['installmentDate'] >= $monthStartDate && $schedule['installmentDate'] <= $monthEndDate) {
                if ($schedule['installmentDate'] < $sysDate) {
                    return true;
                }
            }
        });

        $thisMonthLoanIds = array_column($thisMonthLoanSchedule, 'loanId');

        $loanCodes = implode(', ', array_column($loanInfo->whereIn('id', $thisMonthLoanIds)->all(), 'loanCode'));

        if (count($thisMonthLoanSchedule) > 0) {
            $errorMsg = "Sorry, Samity day can't be changed for (" . $loanCodes . ") loans!!";
        }

        $returnData = array(
            'isEffected' => ($errorMsg == null) ? true : false,
            'errorMsg'   => $errorMsg,
        );

        return $returnData;
    }
}
