<?php

namespace App\Http\Controllers\MFN\Reports\Register\Regular;

use App\Services\MfnService;
use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class DueRegisterReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function getDueRegister(Request $req)
    {
        if ($req->ajax()) {

            $start = microtime(true);

            $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');

            $endDate = date('Y-m-d', strtotime($req->input('endDate')));

            $fieldOfficer = (empty($req->input('fieldOfficer'))) ? null : $req->input('fieldOfficer');

            $fundingOrg = (empty($req->input('fundingOrg'))) ? null : $req->input('fundingOrg');

            $loanProdCategory = (empty($req->input('loanProdCategory'))) ? null : $req->input('loanProdCategory');

            $loanProduct = (empty($req->input('loanProduct'))) ? null : $req->input('loanProduct');

            $serviceCharge = (empty($req->input('serviceCharge'))) ? null : $req->input('serviceCharge');

            $prodCatWise = (empty($req->input('prodCatWise'))) ? null : $req->input('prodCatWise');

            $dueType = (empty($req->input('dueType'))) ? null : $req->input('dueType');

            $loans = DB::table('mfn_samity as ms')
                ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
                ->join('mfn_members as mm', function ($loans) {
                    $loans->on('mm.samityId', '=', 'ms.id')
                        ->where([['mm.is_delete', 0]]);
                })
                ->join('mfn_loans as ml', function ($loans) {
                    $loans->on('ml.memberId', '=', 'mm.id')
                        ->where([['ml.is_delete', 0]]);
                })
                ->join('mfn_loan_products as lp', function ($loans) {
                    $loans->on('ml.productId', '=', 'lp.id')
                        ->where([['lp.is_delete', 0]]);
                })
                ->join('mfn_loan_product_category as lc', function ($loans) {
                    $loans->on('lp.productCategoryId', '=', 'lc.id')
                        ->where([['lc.is_delete', 0]]);
                })
                ->where(function ($loans) use ($fieldOfficer) {
                    if (!empty($fieldOfficer)) {
                        $loans->where('ms.fieldOfficerEmpId', '=', $fieldOfficer);
                    }
                })
                ->where(function ($loans) use ($fundingOrg) {
                    if (!empty($fundingOrg)) {
                        $loans->where('lp.fundingOrgId', '=', $fundingOrg);
                    }
                })
                ->where(function ($loans) use ($loanProdCategory) {
                    if (!empty($loanProdCategory)) {
                        $loans->where('lp.productCategoryId', '=', $loanProdCategory);
                    }
                })
                ->where(function ($loans) use ($loanProduct) {
                    if (!empty($loanProduct)) {
                        $loans->where('ml.productId', '=', $loanProduct);
                    }
                })
                ->orderBy('ml.memberId')
                ->orderBy('lp.isPrimaryProduct', 'desc');

            if ($serviceCharge == 1) {
                $loans->select('ml.id', 'ml.memberId', 'ml.loanCode', 'ml.disbursementDate', 'ms.name as samity', 'mm.memberCode', 'mm.name as member', 'lp.shortName as component', 'lc.shortName as category', DB::Raw('IFNULL( ml.repayAmount , 0 ) as disburseAmount'));
            } else {
                $loans->select('ml.id', 'ml.memberId', 'ml.loanCode', 'ml.disbursementDate', 'ms.name as samity', 'mm.memberCode', 'mm.name as member', 'lp.shortName as component', 'lc.shortName as category', DB::Raw('IFNULL( ml.loanAmount , 0 ) as disburseAmount'));
            }

            $loans = $loans->get();

            if (!empty($dueType)) {
                $loanSchedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray());
                $loanSchedules = collect($loanSchedules);

                foreach ($loans as $key => $loan) {
                    $loans[$key]->lastInastallmentDate = $loanSchedules->where('loanId', $loan->id)->max('installmentDate');
                }

                if ($dueType == 1) {
                    // Current due: those last installment date is not crossed on report date
                    $loans = $loans->where('lastInastallmentDate', '>', $endDate);
                } else if ($dueType == 2) {
                    $loans = $loans->where('lastInastallmentDate', '<', $endDate);
                }
            }

            $ttl_disburse_amount = 0;
            $ttl_loan_amount = 0;
            $ttl_due_amount = 0;
            $ttl_sav_balance = 0;
            $ttl_member_claim = 0;
            $ttl_org_claim = 0;

            $ttl_disburse = 0;
            $ttl_loan = 0;
            $ttl_due = 0;
            $ttl_sav = 0;
            $ttl_member = 0;
            $ttl_org = 0;

            if (count($loans) > 0) {

                $loanStatuses = Mfnservice::getLoanStatus($loans->pluck('id')->toArray(), $endDate);
                $loanStatuses = collect($loanStatuses)->where('dueAmount', '>', 0);
                $loans = $loans->whereIn('id', $loanStatuses->pluck('loanId')->toArray());

                $deposits = DB::table('mfn_savings_deposit')
                    ->where('is_delete', 0)
                    ->whereIn('memberId', $loans->pluck('memberId')->toArray())
                    ->groupBy('memberId')
                    ->select(DB::raw("memberId, SUM(amount) AS deposit"))
                    ->get();


                $withdraws = DB::table('mfn_savings_withdraw')
                    ->where('is_delete', 0)
                    ->whereIn('memberId', $loans->pluck('memberId')->toArray())
                    ->groupBy('memberId')
                    ->select(DB::raw("memberId, SUM(amount) AS withdraw"))
                    ->get();

                $currentMemberId = 0;

                foreach ($loans as $key => $loan) {
                    $loans[$key]->disbursementDate = date('d-m-Y', strtotime($loan->disbursementDate));
                    if ($serviceCharge == 1) {
                        $loans[$key]->dueAmount = $loanStatuses->where('loanId', $loan->id)->first()['dueAmount'];
                        $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstanding'];
                    } else {
                        $loans[$key]->dueAmount = $loanStatuses->where('loanId', $loan->id)->first()['dueAmountPrincipal'];
                        $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstandingPrincipal'];
                    }


                    if ($currentMemberId != $loan->memberId) {

                        $currentMemberId = $loan->memberId;
                        $loans[$key]->savBalance = $deposits->where('memberId', $loan->memberId)->sum('deposit') - $withdraws->where('memberId', $loan->memberId)->sum('withdraw');
                        $netClaim = $loans[$key]->dueAmount - $loans[$key]->savBalance;
                        if ($netClaim > 0) {
                            $loans[$key]->memberClaim = 0;
                            $loans[$key]->orgClaim = $netClaim;
                        } else {
                            $loans[$key]->memberClaim = $netClaim * -1;
                            $loans[$key]->orgClaim = 0;
                        }
                        // $loans[$key]->disburseAmount = number_format($loans[$key]->disburseAmount);
                        // $loans[$key]->dueAmount = number_format($loans[$key]->dueAmount);
                        // $loans[$key]->loanAmount = number_format($loans[$key]->loanAmount);
                        // $loans[$key]->savBalance = number_format($loans[$key]->savBalance);
                        // $loans[$key]->memberClaim = number_format($loans[$key]->memberClaim);
                        // $loans[$key]->orgClaim = number_format($loans[$key]->orgClaim);
                    } else {
                        unset($loans[$key]);
                    }
                }
                $ttl_disburse_amount = $loans->sum('disburseAmount');
                $ttl_loan_amount     = $loans->sum('loanAmount');
                $ttl_due_amount      = $loans->sum('dueAmount');
                $ttl_sav_balance     = $loans->sum('savBalance');
                $ttl_member_claim    = $loans->sum('memberClaim');
                $ttl_org_claim       = $loans->sum('orgClaim');

                $sL = 1;
                if ($prodCatWise == 1) {
                    $productData = array();
                    foreach ($loans->groupBy('component') as $key => $row) {
                        $tempSet = array();

                        $tempSet = [
                            'sL'      => $sL++,
                            'product' => $key,
                            'disburse' => number_format($row->sum('disburseAmount'), 2),
                            'loan'    => number_format($row->sum('loanAmount'), 2),
                            'due'     => number_format($row->sum('dueAmount'), 2),
                            'savings' => number_format($row->sum('savBalance'), 2),
                            'mem'     => number_format($row->sum('memberClaim'), 2),
                            'org'     => number_format($row->sum('orgClaim'), 2)
                        ];
                        $ttl_disburse += $row->sum('disburseAmount');
                        $ttl_loan += $row->sum('loanAmount');
                        $ttl_due += $row->sum('dueAmount');
                        $ttl_sav += $row->sum('savBalance');
                        $ttl_member += $row->sum('memberClaim');
                        $ttl_org += $row->sum('orgClaim');

                        $productData[] = $tempSet;
                    }
                } else {
                    $productData = array();
                    foreach ($loans->groupBy('category') as $key => $row) {
                        $tempSet = array();

                        $tempSet = [
                            'sL'      => $sL++,
                            'product' => $key,
                            'disburse' => number_format($row->sum('disburseAmount'), 2),
                            'loan'    => number_format($row->sum('loanAmount'), 2),
                            'due'     => number_format($row->sum('dueAmount'), 2),
                            'savings' => number_format($row->sum('savBalance'), 2),
                            'mem'     => number_format($row->sum('memberClaim'), 2),
                            'org'     => number_format($row->sum('orgClaim'), 2)
                        ];
                        $ttl_disburse += $row->sum('disburseAmount');
                        $ttl_loan += $row->sum('loanAmount');
                        $ttl_due += $row->sum('dueAmount');
                        $ttl_sav += $row->sum('savBalance');
                        $ttl_member += $row->sum('memberClaim');
                        $ttl_org += $row->sum('orgClaim');

                        $productData[] = $tempSet;
                    }
                }

                $collection = collect($loans)->groupBy('samity');
                $json_data = array(
                    "draw"                => intval($req->input('draw')),
                    "data"                => $collection,
                    'ttl_disburse_amount' => number_format($ttl_disburse_amount, 2),
                    'ttl_loan_amount'     => number_format($ttl_loan_amount, 2),
                    'ttl_due_amount'      => number_format($ttl_due_amount, 2),
                    'ttl_sav_balance'     => number_format($ttl_sav_balance, 2),
                    'ttl_member_claim'    => number_format($ttl_member_claim, 2),
                    'ttl_org_claim'       => number_format($ttl_org_claim, 2),
                    "prodCatData"         => $productData,
                    'ttl_disburse'        => number_format($ttl_disburse, 2),
                    'ttl_loan'            => number_format($ttl_loan, 2),
                    'ttl_due'             => number_format($ttl_due, 2),
                    'ttl_sav'             => number_format($ttl_sav, 2),
                    'ttl_member'          => number_format($ttl_member, 2),
                    'ttl_org'             => number_format($ttl_org, 2),
                );
                echo json_encode($json_data);
            } else {
                $json_data = array(
                    "draw"                => intval($req->input('draw')),
                    "data"                => '',
                    'ttl_disburse_amount' => number_format($ttl_disburse_amount, 2),
                    'ttl_loan_amount'     => number_format($ttl_loan_amount, 2),
                    'ttl_due_amount'      => number_format($ttl_due_amount, 2),
                    'ttl_sav_balance'     => number_format($ttl_sav_balance, 2),
                    'ttl_member_claim'    => number_format($ttl_member_claim, 2),
                    'ttl_org_claim'       => number_format($ttl_org_claim, 2),
                    "prodCatData"         => '',
                    'ttl_disburse'        => number_format($ttl_disburse, 2),
                    'ttl_loan'            => number_format($ttl_loan, 2),
                    'ttl_due'             => number_format($ttl_due, 2),
                    'ttl_sav'             => number_format($ttl_sav, 2),
                    'ttl_member'          => number_format($ttl_member, 2),
                    'ttl_org'             => number_format($ttl_org, 2),
                );
                echo json_encode($json_data);
            }
        } else {

            $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

            $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                ->select('id', 'branch_name', 'branch_code')->orderBy('branch_code')->get();

            $fieldOfficerData = DB::table('mfn_samity as ms')
                ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
                ->leftjoin('hr_employees as he', function ($fieldOfficerData) {
                    $fieldOfficerData->on('ms.fieldOfficerEmpId', '=', 'he.id')
                        ->where([['he.is_delete', 0], ['he.is_active', 1]]);
                })
                ->select('he.id', 'he.emp_name')
                ->groupBy('he.id')
                ->get();

            $fundingOrgData = DB::table('mfn_funding_orgs')
                ->where([['is_delete', 0]])
                ->select('id', 'name')
                ->get();

            $loanProdCategoryData = DB::table('mfn_loan_product_category')
                ->where([['is_delete', 0]])
                ->select('id', 'shortName')
                ->get();

            $loanProductData = DB::table('mfn_loan_products')
                ->where([['is_delete', 0]])
                ->select('id', 'shortName')
                ->get();

            $data = array(
                "branchData"           => $branchData,
                "fieldOfficerData"     => $fieldOfficerData,
                "fundingOrgData"       => $fundingOrgData,
                "loanProdCategoryData" => $loanProdCategoryData,
                "loanProductData"      => $loanProductData,
            );

            return view('MFN.Reports.DueRegister.get_due_register', $data);
        }
    }
}
