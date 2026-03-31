<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MfnService;
use App\Services\RoleService;
use Auth;
use DB;
use Carbon\Carbon;
use Validator;
use App\Model\MFN\LoanReschedule;
use App\Services\HrService;

class LoanRescheduleController extends Controller
{
    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {
            $branches = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select('id', 'branch_name')
                ->get();

            $data = array(
                'branches' => $branches,
            );

            return view('MFN.Loan.Reschedule.index', $data);
        }


        $columns = ['loan.loanCode', 'reschedule.installmentNo', 'reschedule.numberOfTerm', 'reschedule.previosDate', 'reschedule.rescheduleDate', 'hr_employees.emp_name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        $reschedules = DB::table('mfn_loan_reschedules AS reschedule')
            ->join('mfn_loans AS loan', 'loan.id', 'reschedule.loanId')
            ->leftJoin('hr_employees', 'hr_employees.user_id', 'reschedule.created_by')
            ->where('reschedule.is_delete', 0)
            ->whereIn('loan.branchId', $accessAbleBranchIds)
            ->orderBy($order, $dir)
            ->select('reschedule.*', 'loan.loanCode', 'hr_employees.emp_name AS empName');

        $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');
        if ($search != null) {
            $reschedules->where(function ($query) use ($search) {
                $query->Where('loan.loanCode', 'LIKE', "%{$search}%")
                    ->orWhere('hr_employees.emp_name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $reschedules)->count();
        $sl = (int)$req->start + 1;

        $reschedules = $reschedules->limit($limit)->offset($req->start)->get();

        foreach ($reschedules as $key => $reschedule) {
            $reschedules[$key]->sl = $sl++;
            $reschedules[$key]->id = encrypt($reschedule->id);
            $reschedules[$key]->previosDate = date('d-m-Y', strtotime($reschedule->previosDate));
            $reschedules[$key]->rescheduleDate = date('d-m-Y', strtotime($reschedule->rescheduleDate));
            $reschedules[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $reschedules[$key]->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $reschedules,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $loan = DB::table('mfn_loans')->where('id', decrypt($req->loanId))->first();

        $schedules = Mfnservice::generateLoanSchedule($loan->id);
        $scheduleDate = $schedules[$req->installmentNumber - 1]['installmentDate'];

        $data = array(
            'loan' => $loan,
            'installmentNumber' => $req->installmentNumber,
            'scheduleDate' => $scheduleDate,
        );

        return view('MFN.Loan.Reschedule.add', $data);
    }

    public function store($req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            $reschedule = new LoanReschedule;
            $reschedule->loanId         = decrypt($req->loanId);
            $reschedule->installmentNo  = $req->installmentNo;
            $reschedule->numberOfTerm   = $req->numberOfTerm;
            $reschedule->previosDate    = date('Y-m-d', strtotime($req->previosDate));
            $reschedule->rescheduleDate = date('Y-m-d', strtotime($req->rescheduleDate));
            $reschedule->note           = $req->note;
            $reschedule->created_by     = Auth::user()->id;
            $reschedule->created_at     = Carbon::now();
            $reschedule->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $reschedule = LoanReschedule::find(decrypt($req->id));

        $loanCode = DB::table('mfn_loans')->where('id', $reschedule->loanId)->first()->loanCode;

        $data = array(
            'reschedule' => $reschedule,
            'loanCode' => $loanCode,
        );

        return view('MFN.Loan.Reschedule.edit', $data);
    }

    public function update($req)
    {
        $reschedule = LoanReschedule::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $reschedule);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            $reschedule->numberOfTerm   = $req->numberOfTerm;
            $reschedule->previosDate    = date('Y-m-d', strtotime($req->previosDate));
            $reschedule->rescheduleDate = date('Y-m-d', strtotime($req->rescheduleDate));
            $reschedule->note           = $req->note;
            $reschedule->updated_by     = Auth::user()->id;
            $reschedule->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Updated',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function delete(Request $req)
    {
        $reschedule = LoanReschedule::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'delete', $reschedule);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $reschedule->is_delete = 1;
            $reschedule->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Deleted',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function getPassport($req, $operationType, $reschedule = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            # code...
        } else {
            # code...
        }

        if ($operationType != 'delete') {

            $rules = array(
                'installmentNo'     => 'required|numeric',
                'numberOfTerm'      => 'required|numeric',
                'previosDate'       => 'required|date',
                'rescheduleDate'    => 'required|date',
            );

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'installmentNo'     => 'Installment Number',
                'numberOfTerm'      => 'Number Of Term to Reschedule',
                'previosDate'       => 'Current Installment Date',
                'rescheduleDate'    => 'New Installment Date',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // revalidate data
            if ($errorMsg == null) {
                $reValidateMsg = $this->reValidateData($req, $operationType, $reschedule);

                if ($reValidateMsg != null) {
                    $errorMsg = $reValidateMsg;
                }
            }
        }

        $isValid = $errorMsg == null ?true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

    public function reValidateData($req, $operationType, $reschedule)
    {
        $errorMsg = null;
        if ($operationType != 'delete') {
            if ($operationType == 'store') {
                $loanId = decrypt($req->loanId);
                $installmentNo = $req->installmentNo;
            } else {
                $loanId = $reschedule->loanId;
                $installmentNo = $reschedule->installmentNo;
            }

            $loan = DB::table('mfn_loans')->where('id', $loanId)->select('id', 'branchId')->first();

            $sysDate = MfnService::systemCurrentDate($loan->branchId);

            // check the installment is applicate to reschedule or not
            // if the schedule date is less than the branch software date
            // or the installment is already been completed
            // then it could not be rescheduled
            $collectionAmount = DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['loanId', $loanId],
                ])
                ->sum('amount');

            $schedules = MfnService::generateLoanSchedule($loanId);
            $scheduleDate = $schedules[$installmentNo - 1]['installmentDate'];

            if ($scheduleDate < $sysDate) {
                $errorMsg = "Current Installment Date is less than Branch Date.";
            }

            $schedules = collect($schedules);
            $payableAmount = $schedules->where('installmentNo', '<=', $installmentNo)->sum('installmentAmount');

            if ($collectionAmount > $payableAmount) {
                $errorMsg = "Loan Installment already paid.";
            }

            // check incomming dates are correct or not
            if ($scheduleDate != date('Y-m-d', strtotime($req->previosDate))) {
                $errorMsg = "Something went wrong: Current Installment Date may not be correct.";
            }

            if ($operationType == 'store') {
                $reschedulableDate = MfnService::getLoanReschedulableDate($loanId, $installmentNo, $req->numberOfTerm);
            } else {
                $reschedulableDate = MfnService::getLoanReschedulableDate($loanId, $installmentNo, $req->numberOfTerm, $reschedule->id);
            }

            if ($reschedulableDate != date('Y-m-d', strtotime($req->rescheduleDate))) {
                $errorMsg = "Something went wrong: New Installment Date may not be correct.";
            }
        }

        return $errorMsg;
    }

    public function getData(Request $req)
    {
        $data = [];

        if ($req->context == 'numberOfTerm') {
            $loanId = decrypt($req->loaId);
            if (isset($req->exceptRescheduleId)) {
                $reschedulableDate = MfnService::getLoanReschedulableDate($loanId, $req->installmentNumber, $req->numberOfTerm, $req->exceptRescheduleId);
            } else {
                $reschedulableDate = MfnService::getLoanReschedulableDate($loanId, $req->installmentNumber, $req->numberOfTerm);
            }

            $data = array(
                'reschedulableDate' => date('d-m-Y', strtotime($reschedulableDate))
            );
        }

        return $data;
    }
}
