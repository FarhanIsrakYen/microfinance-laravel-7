<?php

namespace App\Http\Controllers\MFN\Member;

use App\Http\Controllers\Controller;
use App\Mail\MailVerification;
use App\Model\MFN\Loan;
use App\Model\MFN\Member;
use App\Model\MFN\MemberDetails;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use App\Rules\MobileNo;
use App\Rules\Unique;
use App\Services\GnlService;
use App\Services\RoleService;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\ResizeImage;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Str;
use Validator;

class MemberController extends Controller
{

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
                ->select('id', 'branch_name')
                ->get();

            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.Member.index', $data);
        }

        $columns = ['m.memberCode', 'm.name', 'md.spouseName', 'p.name', 'm.gender', 'branch.branch_name', 'samity.name', 'm.admissionDate', 'm.closingDate', 'emp.emp_name'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');

        $members = DB::table('mfn_members AS m')
            ->join('mfn_member_details AS md', 'md.memberId', 'm.id')
            ->leftJoin('mfn_loan_products AS p', 'p.id', 'm.primaryProductId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'm.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'm.samityId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'm.created_by')
            ->whereIn('m.branchId', $accessAbleBranchIds)
            ->where('m.is_delete', 0)
            ->select('p.name AS primaryProduct', 'branch.branch_name AS branchName', 'samity.name AS samityName', 'emp.emp_name AS empName', 'm.*', 'md.spouseName')
            ->orderBy($order, $dir);

        if ($search != null) {
            $members->where(function ($query) use ($search) {
                $query->where('m.name', 'LIKE', "%$search%")
                    ->orWhere('m.memberCode', 'LIKE', "%$search%")
                    ->orWhere('m.gender', 'LIKE', "%$search%")
                    ->orWhere('p.name', 'LIKE', "%$search%")
                    ->orWhere('samity.name', 'LIKE', "%$search%")
                    ->orWhere('branch.branch_name', 'LIKE', "%$search%");
            });
        }

        if ($req->filBranch != '') {
            $members->where('m.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $members->where('m.samityId', $req->filSamity);
        }
        if ($req->filStatus == 'active') {
            $members->where('m.closingDate', '0000-00-00');
        }
        if ($req->filStatus == 'inactive') {
            $members->where('m.closingDate', '!=', '0000-00-00');
        }
        if ($req->nameOrCode != '') {
            $nameOrCode = $req->nameOrCode;
            $members->where(function ($query) use ($nameOrCode) {
                $query->where('m.name', 'LIKE', "%$nameOrCode%")
                    ->orWhere('m.memberCode', 'LIKE', "%$nameOrCode%");
            });
        }
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $members->where('m.admissionDate', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $members->where('m.admissionDate', '<=', $endDate);
        }

        $totalData = (clone $members)->count();
        $members   = $members->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($members as $key => $member) {
            $members[$key]->admissionDate = Carbon::parse($member->admissionDate)->format('d-m-Y');
            $members[$key]->status        = $member->closingDate == '0000-00-00' ?'Active' : 'Inactive';
            $members[$key]->sl            = $sl++;
            $members[$key]->id            = encrypt($member->id);
            $members[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $members[$key]->id);
        }
        
        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $members,
        );

        return response()->json($data);
    }

    public function view($memberId)
    {

        $member = Member::where('id', decrypt($memberId))
            ->select('id', 'name', 'memberCode', 'admissionDate', 'gender', 'branchId', 'samityId', 'primaryProductId')->first();

        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $member->branchId) {
            return '';
        }

        $memberDetails = MemberDetails::where('memberId', $member->id)->select('*')->first();


        $nationality = DB::table('gnl_country')
            ->where('id', $memberDetails->nationalityId)
            ->first()
            ->nationality;

        $nomineeinfo        = DB::table('mfn_member_nominees')->where('memberId', $memberDetails->memberId)->select('name', 'mobileNo', 'relationshipId', 'share')->first();
        $relationWithMember = DB::table('mfn_member_relationships')->where('id', @$nomineeinfo->relationshipId)->value('name');

        $relationWithReference = DB::table('mfn_member_references')->where('memberId', $memberDetails->memberId)->select('name', 'mobileNo', 'relationship', 'organization', 'designation')->first();
        //savings  medetory will be one
        $savingsaccinfoMendetory = SavingsAccount::from('mfn_savings_accounts as sa')
            ->where([['sa.memberId', $memberDetails->memberId], ['sa.isMandatory', 1], ['closingDate', '0000-00-00']])
            ->orderBy('savingsCycle')

            // ->select('id','savingsProductId','memberId', 'accountCode', 'interestRate', 'openingDate', 'isMandatory','autoProcessAmount','savingsCycle')
            ->get();
        $savingsaccinfoAll = SavingsAccount::from('mfn_savings_accounts as sa')
            ->where([['sa.memberId', $memberDetails->memberId], ['closingDate', '0000-00-00']])
            ->orderBy('savingsCycle')

            // ->select('id','savingsProductId','memberId', 'accountCode', 'interestRate', 'openingDate', 'isMandatory','autoProcessAmount','savingsCycle')
            ->get();

        $productIDs = array_unique($savingsaccinfoAll->pluck('savingsProductId')->toArray());
        sort($productIDs);

        foreach ($savingsaccinfoAll as $key => $savingsaccinfo) {
            $filters['accountId'] = $savingsaccinfo->id;
            // $sav = $savingsaccinfoAll->where('savingsProductId',  $productID)->sum();
            $savingsaccinfoAll[$key]['total_balance'] = MfnService::getSavingsBalance($filters);
        }


        $loanaccinfoAll = Loan::from('mfn_loans as lo')
            ->where([['lo.memberId', $memberDetails->memberId]])
            ->orderBy('loanCycle')

            // ->select('id','savingsProductId','memberId', 'accountCode', 'interestRate', 'openingDate', 'isMandatory','autoProcessAmount','savingsCycle')
            ->get();
        $loanproductIDs = array_unique($loanaccinfoAll->pluck('productId')->toArray());
        sort($loanproductIDs);

        foreach ($loanaccinfoAll as $key => $value) {

            $filters['loanId'] = $value->id;
            // print_r(MfnService::getLoanCollection($filters)."---");
            $loanaccinfoAll[$key]['total_col'] = MfnService::getLoanCollection($filters);
        }

        $ArrayValue            = array();
        $ArrayValue['loan_ID'] = $loanproductIDs;
        $ArrayValue['SV_ID']   = $productIDs;

        $arrayLoan    = array();
        $arraySavings = array();
        foreach ($loanproductIDs as $key => $value) {
            $arrayLoan[$key]['loan_Amount'] = $loanaccinfoAll->where('productId', $value)->sum('loanAmount');
            $arrayLoan[$key]['LPName']      = $loanaccinfoAll->where('productId', $value)->first()->loanProduct->name;
            $arrayLoan[$key]['LPID']        = $value;
            $arrayLoan[$key]['Outstanding'] = $loanaccinfoAll->where('productId', $value)->sum('loanAmount') - $loanaccinfoAll->where('productId', $value)->sum('total_col');
            // $ArrayValue['loan_ID'][$key]['loan_Amt']= $loanaccinfoAll->where('productId',$ArrayValue['loan_ID'][$key])->sum('loanAmount');
        }
        foreach ($productIDs as $key => $value) {
            $arraySavings[$key]['balance'] = $savingsaccinfoAll->where('savingsProductId', $value)->sum('total_balance');
            $arraySavings[$key]['SPName']  = $savingsaccinfoAll->where('savingsProductId', $value)->first()->savingsProduct->name;
            $arraySavings[$key]['SPID']    = $value;
            // $ArrayValue['loan_ID'][$key]['loan_Amt']= $loanaccinfoAll->where('productId',$ArrayValue['loan_ID'][$key])->sum('loanAmount');
        }

        $data = array($arraySavings, $arrayLoan);


        return view('MFN.Member.view', compact('member', 'memberDetails', 'relationWithReference', 'nomineeinfo', 'relationWithMember', 'savingsaccinfoMendetory', 'savingsaccinfoAll', 'loanaccinfoAll', 'nationality', 'data'));
    }
    public function showsavingsDetails(Request $request)
    {
        if ($request->ajax()) {

            $RowID     = $request->RowID;
            $deposits  = SavingsDeposit::where('accountId', $RowID)->where('is_delete', 0)->get();
            $withdraws = SavingsWithdraw::where('accountId', $RowID)->where('is_delete', 0)->get();

            $transactionDates = array_unique(array_merge($deposits->pluck('date')->toArray(), $withdraws->pluck('date')->toArray()));
            sort($transactionDates);

            $html = '';

            $balance = 0;

            foreach ($transactionDates as $key => $transactionDate) {
                $deposit  = $deposits->where('date', $transactionDate)->first();
                $withdraw = $withdraws->where('date', $transactionDate)->first();

                $dip  = 0;
                $wid  = 0;
                $type = '';

                if (!empty($deposit)) {
                    $dip = $deposit->amount;
                    $balance += $deposit->amount;

                    $type = $deposit->depositeType->name;
                }

                if (!empty($withdraw)) {
                    $wid = $withdraw->amount;
                    $balance -= $withdraw->amount;
                    $type = $withdraw->depositeType->name;
                }

                $html .= '<tr>';
                $html .= '<td>' . ($key + 1) . '</td>';
                $html .= '<td>' . $transactionDate . '</td>';
                $html .= '<td>' . $type . '</td>';
                $html .= '<td>' . $dip . '</td>';
                $html .= '<td>' . $wid . '</td>';
                $html .= '<td>' . $balance . '</td>';
                $html .= '</tr>';
            }

            echo $html;
        }
    }
    public function showloanDetails(Request $request)
    {
        if ($request->ajax()) {

            $RowID = $request->RowID;
            $loan  = DB::table('mfn_loan_collections')->where('loanId', $RowID)->where('is_delete', 0)->orderBy('collectionDate', 'ASC')->get();
            $html = '';

            $balance = 0;

            foreach ($loan as $key => $Row) {
                // $deposit = $deposits->where('date', $transactionDate)->first();
                // $withdraw = $withdraws->where('date', $transactionDate)->first();

                $html .= '<tr>';
                $html .= '<td>' . ($key + 1) . '</td>';
                $html .= '<td>' . $Row->collectionDate . '</td>';
                $html .= '<td>' . $Row->paymentType . '</td>';
                $html .= '<td>' . $Row->amount . '</td>';
                $html .= '</tr>';
            }
            echo $html;
        }
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        if (Auth::user()->branch_id == 1) {
            echo "Sorry, This service is only availabe from branch.";
            exit();
        }

        $sysDate  = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $samities = DB::table('mfn_samity')
            ->where([
                ['is_delete', 0],
                ['branchId', Auth::user()->branch_id],
                ['openingDate', '<=', $sysDate],
                ['closingDate', '0000-00-00'],
            ])
            ->select(DB::raw("CONCAT(samityCode,' - ',name) AS name, id"))
            ->get();

        $branchProductIds = json_decode(DB::table('mfn_branch_products')
            ->where('branchId', Auth::user()->branch_id)
            ->value('loanProductIds'));

        // if no product is assigned, then show a message to assign product first
        if ($branchProductIds == null) {
            echo "Please assign loan product to this branch first.";
            exit();
        }

        $primaryProducts = DB::table('mfn_loan_products')
            ->where('isPrimaryProduct', 1)
            ->whereIn('id', $branchProductIds)
            ->select(DB::raw("CONCAT(productCode, '-', name) AS name, id"))
            ->get();

        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        $mandatorySavingProducts = [];
        if (!$isOpening) {
            $mandatorySavingProducts = $this->getMandatorySavingProducts();

            foreach ($mandatorySavingProducts as $savingsProduct) {
                if ($savingsProduct->productTypeId == 1) {
                    if ($savingsProduct->interestRate == null) {
                        echo "Please set all Mandatory Saving Product's Interest Rate first.";
                        exit();
                    }
                }
                if ($savingsProduct->productTypeId == 2) {
                    if (count($savingsProduct->interestRates) == 0) {
                        echo "Please set all Mandatory Saving Product's Interest Rate first.";
                        exit();
                    }
                }
            }
        }

        $mfnConfigs = DB::table('mfn_config')
            ->whereIn('title', ['member', 'general', 'memberAdmissionRequiredFields', 'openingMemberAdmissionRequiredFields'])
            ->select('title', 'content')
            ->get();

        $mfnMemberConfig = json_decode($mfnConfigs->where('title', 'member')->first()->content);

        $countries = DB::table('gnl_country')
            ->where('status', 1)
            ->orderBy('positionOrder')
            ->orderBy('name')
            ->get();

        $nationalities = DB::table('gnl_country')
            ->where('status', 1)
            ->orderBy('positionOrder')
            ->orderBy('nationality')
            ->get();

        $mfnGnlConfig = json_decode($mfnConfigs->where('title', 'general')->first()->content);

        $evidenceTypes = DB::table('mfn_member_evidence_types')->where('status', 1)->get();

        $divisions = DB::table('gnl_divisions')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ])
            ->get();

        $maritalStatus     = DB::table('mfn_member_marital_status')->where('status', 1)->get();
        $educationalLevels = DB::table('mfn_member_educational_levels')->where('status', 1)->get();
        $relationships     = DB::table('mfn_member_relationships')->where('status', 1)->get();
        $professions       = DB::table('mfn_member_professions')->where('status', 1)->get();
        $religions         = DB::table('mfn_member_religions')->where('status', 1)->get();

        if ($isOpening) {
            $requiredFields = collect(json_decode($mfnConfigs->where('title', 'openingMemberAdmissionRequiredFields')->first()->content))->toArray();
        } else {
            $requiredFields = collect(json_decode($mfnConfigs->where('title', 'memberAdmissionRequiredFields')->first()->content))->toArray();
        }

        $data = array(
            'samities'                => $samities,
            'primaryProducts'         => $primaryProducts,
            'isOpening'               => $isOpening,
            'mfnMemberConfig'         => $mfnMemberConfig,
            'mfnGnlConfig'            => $mfnGnlConfig,
            'sysDate'                 => $sysDate,
            'mandatorySavingProducts' => $mandatorySavingProducts,
            'countries'               => $countries,
            'nationalities'           => $nationalities,
            'evidenceTypes'           => $evidenceTypes,
            'divisions'               => $divisions,
            'maritalStatus'           => $maritalStatus,
            'educationalLevels'       => $educationalLevels,
            'relationships'           => $relationships,
            'professions'             => $professions,
            'religions'               => $religions,
            'requiredFields'          => $requiredFields,
        );

        return view('MFN.Member.add', $data);
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

        // generate MRA code
        $mraCode = self::generateMraCode($req->memberCode);
        if ($mraCode == 'maxLengthCrossed') {
            $notification = array(
                'message'    => 'MRA Merber Code Max Length Exceeded',
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // store data
        DB::beginTransaction();
        try {

            $isOpening = MfnService::isOpening(Auth::user()->branch_id);

            $req->merge([
                "mraCode"       => $mraCode,
                "branchId"      => Auth::user()->branch_id,
                "admissionDate" => Carbon::parse($req->admissionDate),
                "dateOfBirth"   => Carbon::parse($req->dateOfBirth),
                "created_by"    => Auth::user()->id,
                "created_at"    => Carbon::now(),
            ]);

            $member = Member::create($req->all());

            $req->merge([
                "memberId"         => $member->id,
                "isOpening"        => $isOpening == true ?1 : 0
            ]);

            if (isset($req->sameAsPreesent)) {
                $req->merge([
                    "perDivisionId"    => $req->preDivisionId,
                    "perDistrictId"    => $req->preDistrictId,
                    "perUpazilaId"     => $req->preUpazilaId,
                    "perUnionId"       => $req->preUnionId,
                    "perVillageId"     => $req->preVillageId,
                    "perStreetHolding" => $req->preStreetHolding
                ]);
            }

            if ($req->secondEvidenceValidTill != '') {
                $req->merge([
                    "secondEvidenceValidTill" => Carbon::parse($req->secondEvidenceValidTill),
                ]);
            }

            $memberDetails = MemberDetails::create($req->all());

            if (!is_null($req->email) || !empty($req->email)) {

                if (DB::table('mfn_config')->where('title', 'mail')->first()->content == 'yes') {

                    $mailVerification['memberId'] = $member->id;
                    $mailVerification['email']    = $req->email;
                    $mailVerification['eToken']   = Str::random(60);

                    $mailVerificationReq = new Request;
                    $mailVerificationReq->merge($mailVerification);
                    $response = app('App\Http\Controllers\MFN\Mail\MailVarificationController')->store($mailVerificationReq)->getData();

                    if ($response->{'alert-type'} == 'error') {
                        $notification = array(
                            'message'    => $response->message,
                            'alert-type' => 'error',
                        );
                        return response()->json($notification);
                    }

                    Mail::to($req->email)->send(new MailVerification($member->id, $mailVerification['eToken']));
                }
            }



            // store image
            $imageData = $this->storeImage($req, $member->id);

            if ($imageData['errorMsg'] == null) {
                $memberDetails                 = MemberDetails::find($member->id);
                $memberDetails->profileImage   = $imageData['profileImageFilename'];
                $memberDetails->signatureImage = $imageData['signatureImageFilename'];
                $memberDetails->save();
            } else {
                throw new \Exception($imageData['errorMsg']);
            }

            // store Nominee and Reference
            foreach ($req->nomineeNames as $key => $nomineeName) {

                $isAllDataNull = ($nomineeName == '' && $req->nomineeMobileNos[$key] == '' && $req->nomineeRelationships[$key] == '' && $req->nomineeShares[$key] == '') ?true : false;

                if ($isAllDataNull) {
                    continue;
                }

                DB::table('mfn_member_nominees')->insert([
                    'memberId'       => $member->id,
                    'name'           => $nomineeName,
                    'mobileNo'       => $req->nomineeMobileNos[$key],
                    'relationshipId' => $req->nomineeRelationships[$key],
                    'share'          => $req->nomineeShares[$key],
                    'created_at'     => Carbon::now(),
                    'created_by'     => Auth::user()->id,
                ]);
            }
            foreach ($req->referenceNames as $key => $referenceName) {

                $isAllDataNull = ($referenceName == '' && $req->referenceRelationships[$key] == '' && $req->referenceOrganizations[$key] == '' && $req->referenceDesignations[$key] == '' && $req->referenceMobileNos[$key] == '') ?true : false;

                if ($isAllDataNull) {
                    continue;
                }

                DB::table('mfn_member_references')->insert([
                    'memberId'     => $member->id,
                    'name'         => $referenceName,
                    'relationship' => $req->referenceRelationships[$key],
                    'organization' => $req->referenceOrganizations[$key],
                    'designation'  => $req->referenceDesignations[$key],
                    'mobileNo'     => $req->referenceMobileNos[$key],
                    'created_at'   => Carbon::now(),
                    'created_by'   => Auth::user()->id,
                ]);
            }

            // store savings account if mandatory
            # storing savings account code goes here

            if (!$isOpening && is_array($req->savingProducts)) {
                $savingsAccountRequest = new Request;

                $oneTimePeriodKey = 0;
                foreach ($req->savingProducts as $key => $savingProductId) {
                    $savProduct = DB::table('mfn_savings_product')->where('id', $savingProductId)->first();

                    $savingsAccountRequest->merge([
                        'memberId'         => $member->id,
                        'savingsProductId' => $savProduct->id,
                        'accountCode'      => $req->savingsCodes[$key],
                        'openingDate'      => $member->admissionDate,
                        'isMandatory'      => 1,
                    ]);
                    if ($savProduct->productTypeId == 1) { // if regular product
                        $savingsAccountRequest->merge([
                            'autoProcessAmount'   => $req->savingsAmounts[$key],
                            'regularInterestRate' => $req->interestRates[$key],
                        ]);
                    }
                    if ($savProduct->productTypeId == 2) { // if one time product
                        $savingsAccountRequest->merge([
                            'period'               => $req->maturePeriods[$oneTimePeriodKey],
                            'onetimeInterestRate'  => $req->interestRates[$key],
                            'onetimeDepositAmount' => $req->savingsAmounts[$key],
                            'transactionTypeId'    => $req->transactionTypeIds[$oneTimePeriodKey],
                            'ledgerId'             => $req->ledgerIds[$oneTimePeriodKey],
                            'chequeNo'             => $req->chequeNos[$oneTimePeriodKey],
                        ]);
                        $oneTimePeriodKey++;
                    }

                    $response = app('App\Http\Controllers\MFN\Savings\SavingsAccountController')->store($savingsAccountRequest)->getData();
                    if ($response->{'alert-type'} == 'error') {
                        $notification = array(
                            'message'    => $response->message,
                            'alert-type' => 'error',
                        );
                        return response()->json($notification);
                    }
                }
            }


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

    public function storeImage($req, $memberId, $operationType = null)
    {
        try {
            $profileImageErrorMsg   = null;
            $signatureImageErrorMsg = null;

            $profileImageFilename   = null;
            $signatureImageFilename = null;

            $mfnMemberConfig = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);

            $isOpening = MfnService::isOpening(Auth::user()->branch_id);

            if ($operationType == 'update' || $isOpening) {
                $mfnMemberConfig->isProfileImageMandatory   = 'no';
                $mfnMemberConfig->isSignatureImageMandatory = 'no';
            }
            // profile image
            if ($mfnMemberConfig->isProfileImageMandatory == 'yes' || ($req->profileImageText != '' || $req->hasFile('profileImage'))) {

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
            if ($mfnMemberConfig->isSignatureImageMandatory == 'yes' || ($req->signatureImageText != '' || $req->hasFile('signatureImage'))) {
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

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            if($req->basicInfoEdit == 'on'){
                return $this->updateBasicInfo($req);
            }
            else{
                return $this->update($req);
            }
            
        }

        $member        = DB::table('mfn_members')->where('id', decrypt($req->id))->first();
        $memberDetails = DB::table('mfn_member_details')->where('memberId', $member->id)->first();

        $samity = DB::table('mfn_samity')
            ->where('id', $member->samityId)
            ->select('name', 'samityCode', 'samityType')
            ->first();

        $genders = [];
        if ($samity->samityType == 'Both') {
            $genders = ['Male' => 'Male', 'Female' => 'Female'];
        } elseif ($samity->samityType == 'Male') {
            $genders = ['Male' => 'Male'];
        } elseif ($samity->samityType == 'Female') {
            $genders = ['Female' => 'Female'];
        }

        $primaryProductIds = json_decode(DB::table('mfn_branch_products')
            ->where('branchId', $member->branchId)
            ->first()
            ->loanProductIds);

        $primaryProducts = DB::table('mfn_loan_products')
            ->whereIn('id', $primaryProductIds)
            ->select(DB::raw("CONCAT(productCode, '-', name) AS name, id"))
            ->get();

        $mandatorySavingProducts = $this->getMandatorySavingProducts();

        $mfnConfigs = DB::table('mfn_config')
            ->whereIn('title', ['member', 'general', 'memberAdmissionRequiredFields', 'openingMemberAdmissionRequiredFields'])
            ->select('title', 'content')
            ->get();

        $mfnMemberConfig = json_decode($mfnConfigs->where('title', 'member')->first()->content);

        $countries = DB::table('gnl_country')
            ->where('status', 1)
            ->orderBy('positionOrder')
            ->orderBy('name')
            ->get();

        $nationalities = DB::table('gnl_country')
            ->where('status', 1)
            ->orderBy('positionOrder')
            ->orderBy('nationality')
            ->get();

        $mfnGnlConfig = json_decode($mfnConfigs->where('title', 'general')->first()->content);

        $evidenceTypes = DB::table('mfn_member_evidence_types')->where('status', 1)->get();

        $divisions = DB::table('gnl_divisions')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ])
            ->get();

        $maritalStatus     = DB::table('mfn_member_marital_status')->where('status', 1)->get();
        $educationalLevels = DB::table('mfn_member_educational_levels')->where('status', 1)->get();
        $relationships     = DB::table('mfn_member_relationships')->where('status', 1)->get();
        $professions       = DB::table('mfn_member_professions')->where('status', 1)->get();
        $religions         = DB::table('mfn_member_religions')->where('status', 1)->get();

        $sysDate = MfnService::systemCurrentDate($member->branchId);

        $divisions = GnlService::getDivisions();
        // get present address
        $filters['division_id']  = $memberDetails->preDivisionId;
        $filters['district_id']  = $memberDetails->preDistrictId;
        $filters['upazila_id']   = $memberDetails->preUpazilaId;
        $filters['union_id']     = $memberDetails->preUnionId;
        $preAddress['districts'] = GnlService::getDistricts($filters);
        $preAddress['upazilas']  = GnlService::getUpazilas($filters);
        $preAddress['unions']    = GnlService::getUnions($filters);
        $preAddress['villages']  = GnlService::getVillages($filters);

        // get permanent address
        $filters['division_id']  = $memberDetails->perDivisionId;
        $filters['district_id']  = $memberDetails->perDistrictId;
        $filters['upazila_id']   = $memberDetails->perUpazilaId;
        $filters['union_id']     = $memberDetails->perUnionId;
        $perAddress['districts'] = GnlService::getDistricts($filters);
        $perAddress['upazilas']  = GnlService::getUpazilas($filters);
        $perAddress['unions']    = GnlService::getUnions($filters);
        $perAddress['villages']  = GnlService::getVillages($filters);

        $sameAsPreesentAddress = false;
        if ($memberDetails->preDivisionId == $memberDetails->perDivisionId && $memberDetails->preDistrictId == $memberDetails->perDistrictId && $memberDetails->preUpazilaId == $memberDetails->perUpazilaId && $memberDetails->preUnionId == $memberDetails->perUnionId && $memberDetails->preVillageId == $memberDetails->perVillageId && $memberDetails->preStreetHolding == $memberDetails->perStreetHolding) {
            $sameAsPreesentAddress = true;
        }

        $nominees = DB::table('mfn_member_nominees')
            ->where([
                ['is_delete', 0],
                ['memberId', $member->id],
            ])
            ->get();

        $references = DB::table('mfn_member_references')
            ->where([
                ['is_delete', 0],
                ['memberId', $member->id],
            ])
            ->get();

        $isOpening = MfnService::isOpening($member->branchId) && $memberDetails->isOpening;

        if ($isOpening) {
            $requiredFields = collect(json_decode($mfnConfigs->where('title', 'openingMemberAdmissionRequiredFields')->first()->content))->toArray();
        } else {
            $requiredFields = collect(json_decode($mfnConfigs->where('title', 'memberAdmissionRequiredFields')->first()->content))->toArray();
        }

        $data = array(
            'member'                  => $member,
            'memberDetails'           => $memberDetails,
            'samity'                  => $samity,
            'sysDate'                 => $sysDate,
            'genders'                 => $genders,
            'sameAsPreesentAddress'   => $sameAsPreesentAddress,
            'nominees'                => $nominees,
            'references'              => $references,
            'divisions'               => $divisions,
            'preAddress'              => $preAddress,
            'perAddress'              => $perAddress,
            'primaryProducts'         => $primaryProducts,
            'mfnMemberConfig'         => $mfnMemberConfig,
            'mfnGnlConfig'            => $mfnGnlConfig,
            'mandatorySavingProducts' => $mandatorySavingProducts,
            'countries'               => $countries,
            'nationalities'           => $nationalities,
            'evidenceTypes'           => $evidenceTypes,
            'divisions'               => $divisions,
            'maritalStatus'           => $maritalStatus,
            'educationalLevels'       => $educationalLevels,
            'relationships'           => $relationships,
            'professions'             => $professions,
            'religions'               => $religions,
            'isOpening'               => $isOpening,
            'requiredFields'          => $requiredFields,
        );

        return view('MFN.Member.edit', $data);
    }

    public function update(Request $req)
    {
        $member = Member::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $member);
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

            $memberDetails = MemberDetails::find($member->id);

            $member->name             = $req->name;
            $member->primaryProductId = $req->primaryProductId;
            $member->gender           = $req->gender;
            $member->updated_by       = Auth::user()->id;
            $member->updated_at       = Carbon::now();

            if ($memberDetails->isOpening == 1) {
                $member->admissionDate = Carbon::parse($req->admissionDate);
            }

            $member->save();

            if (isset($req->sameAsPreesent)) {
                $req->merge([
                    "perDivisionId"    => $req->preDivisionId,
                    "perDistrictId"    => $req->preDistrictId,
                    "perUpazilaId"     => $req->preUpazilaId,
                    "perUnionId"       => $req->preUnionId,
                    "perVillageId"     => $req->preVillageId,
                    "perStreetHolding" => $req->preStreetHolding,
                ]);
            }

            if ($req->secondEvidenceValidTill != '') {
                $req->merge([
                    "secondEvidenceValidTill" => Carbon::parse($req->secondEvidenceValidTill),
                ]);
            } else {
                $req->merge([
                    "secondEvidenceValidTill" => null,
                ]);
            }

            $req->merge([
                "dateOfBirth" => Carbon::parse($req->dateOfBirth),
            ]);

            $memberDetails->surName                       = $req->surName;
            $memberDetails->dateOfBirth                   = $req->dateOfBirth;
            $memberDetails->maritalStatusId               = $req->maritalStatusId;
            $memberDetails->educationLevelId              = $req->educationLevelId;
            $memberDetails->fatherName                    = $req->fatherName;
            $memberDetails->motherName                    = $req->motherName;
            $memberDetails->sonName                       = $req->sonName;
            $memberDetails->spouseName                    = $req->spouseName;
            $memberDetails->nationalityId                 = $req->nationalityId;
            $memberDetails->mobileNo                      = $req->mobileNo;
            $memberDetails->email                         = $req->email;
            $memberDetails->formApplicationNo             = $req->formApplicationNo;
            $memberDetails->firstEvidenceTypeId           = $req->firstEvidenceTypeId;
            $memberDetails->firstEvidence                 = $req->firstEvidence;
            $memberDetails->firstEvidenceIssuerCountryId  = $req->firstEvidenceIssuerCountryId;
            $memberDetails->secondEvidenceTypeId          = $req->secondEvidenceTypeId;
            $memberDetails->secondEvidence                = $req->secondEvidence;
            $memberDetails->secondEvidenceIssuerCountryId = $req->secondEvidenceIssuerCountryId;
            $memberDetails->secondEvidenceValidTill       = $req->secondEvidenceValidTill;
            $memberDetails->admissionNo                   = $req->admissionNo;
            $memberDetails->preDivisionId                 = $req->preDivisionId;
            $memberDetails->preDistrictId                 = $req->preDistrictId;
            $memberDetails->preUpazilaId                  = $req->preUpazilaId;
            $memberDetails->preUnionId                    = $req->preUnionId;
            $memberDetails->preVillageId                  = $req->preVillageId;
            $memberDetails->preStreetHolding              = $req->preStreetHolding;
            $memberDetails->familyContactNumber           = $req->familyContactNumber;
            $memberDetails->perDivisionId                 = $req->perDivisionId;
            $memberDetails->perDistrictId                 = $req->perDistrictId;
            $memberDetails->perUpazilaId                  = $req->perUpazilaId;
            $memberDetails->perUnionId                    = $req->perUnionId;
            $memberDetails->perVillageId                  = $req->perVillageId;
            $memberDetails->perStreetHolding              = $req->perStreetHolding;
            $memberDetails->professionId                  = $req->professionId;
            $memberDetails->religionId                    = $req->religionId;
            $memberDetails->numberOfFamilyMember          = $req->numberOfFamilyMember;
            $memberDetails->yearlyIncome                  = $req->yearlyIncome;
            $memberDetails->landArea                      = $req->landArea;
            $memberDetails->note                          = $req->note;
            $memberDetails->fixedAssetDescription         = $req->fixedAssetDescription;
            $memberDetails->updated_at                    = Carbon::now();
            $memberDetails->updated_by                    = Auth::user()->id;

            // store image
            $imageData = $this->storeImage($req, $member->id, $operationType = 'update');

            if ($imageData['errorMsg'] == null) {
                if ($imageData['profileImageFilename'] != null) {
                    $memberDetails->profileImage = $imageData['profileImageFilename'];
                }
                if ($imageData['signatureImageFilename'] != null) {
                    $memberDetails->signatureImage = $imageData['signatureImageFilename'];
                }
            } else {
                throw new \Exception($imageData['errorMsg']);
            }

            if ($memberDetails->email != $req->email) {

                $mailVerification['memberId'] = $member->id;
                $mailVerification['email']    = $req->email;
                $mailVerification['eToken']   = Str::random(60);

                $mailVerificationReq = new Request;
                $mailVerificationReq->merge($mailVerification);
                $response = app('App\Http\Controllers\MFN\Mail\MailVarificationController')->update($mailVerificationReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }

                if (DB::table('mfn_config')->where('title', 'mail')->first()->content == 'yes') {

                    Mail::to($req->email)->send(new App\Mail\MailVerification($mailVerification['memberId'], $mailVerification['eToken']));
                }
            }

            $memberDetails->save();

            // update Nominee and Reference
            DB::table('mfn_member_nominees')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $member->id],
                ])
                ->update(['is_delete' => 1]);

            DB::table('mfn_member_references')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $member->id],
                ])
                ->update(['is_delete' => 1]);

            if (is_array($req->nomineeNames)) {
                foreach ($req->nomineeNames as $key => $nomineeName) {
                    DB::table('mfn_member_nominees')->insert([
                        'memberId'       => $member->id,
                        'name'           => $nomineeName,
                        'mobileNo'       => $req->nomineeMobileNos[$key],
                        'relationshipId' => $req->nomineeRelationships[$key],
                        'share'          => $req->nomineeShares[$key],
                        'created_at'     => Carbon::now(),
                        'created_by'     => Auth::user()->id,
                    ]);
                }
            }

            if (is_array($req->referenceNames)) {
                foreach ($req->referenceNames as $key => $referenceName) {
                    DB::table('mfn_member_references')->insert([
                        'memberId'     => $member->id,
                        'name'         => $referenceName,
                        'relationship' => $req->referenceRelationships[$key],
                        'organization' => $req->referenceOrganizations[$key],
                        'designation'  => $req->referenceDesignations[$key],
                        'mobileNo'     => $req->referenceMobileNos[$key],
                        'created_at'   => Carbon::now(),
                        'created_by'   => Auth::user()->id,
                    ]);
                }
            }

            // if primary product has been updated then chnage primary product to the mendatory savings account's deposit.
            DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $member->id],
                ])
                ->update(['primaryProductId' => $member->primaryProductId]);

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

    public function basicInfoValidation(Request $req){
        $notification = array(
            'alert-type' => 'error',
        );

        if($req->name == null || $req->name == ""){
            $notification['message'] = "Memebr Name Cant be empty";
            return $notification;
        }

        if($req->surName == null || $req->surName == ""){
            $notification['message'] = "Surname Cant be empty";
            return $notification;
        }

        if($req->fatherName == null || $req->fatherName == ""){
            $notification['message'] = "Father's Name Cant be empty";
            return $notification;
        }

        if($req->motherName == null || $req->motherName == ""){
            $notification['message'] = "Mother's Name Cant be empty";
            return $notification;
        }

        if($req->maritalStatusId == 1 || $req->maritalStatusId == 4){
            if($req->spouseName == null || $req->spouseName == ""){
                $notification['message'] = "Spouse Name Cant be empty";
                return $notification;
            }
        }

        return array('alert-type' => 'success');
    }

    public function updateBasicInfo(Request $req){
        
        $validation = $this->basicInfoValidation($req);
        if($validation['alert-type']=='error'){
            return response()->json($validation);
        }

        $member = Member::find(decrypt($req->id));
        $member->name = $req->name;
        $member->save();

        $memberDetails = MemberDetails::find($member->id);
        $memberDetails->surName                       = $req->surName;
        $memberDetails->educationLevelId              = $req->educationLevelId;
        $memberDetails->fatherName                    = $req->fatherName;
        $memberDetails->motherName                    = $req->motherName;
        $memberDetails->sonName                       = $req->sonName;
        $memberDetails->spouseName                    = $req->spouseName;
        $memberDetails->updated_at                    = Carbon::now();
        $memberDetails->save();

        $notification = array(
            'message'    => 'Successfully Updated',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $member   = Member::find(decrypt($req->id));
        $passport = $this->getPassport($req, $operationType = 'delete', $member);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // delete member
        $member->is_delete = 1;
        $member->save();

        // delete member details
        DB::table('mfn_member_details')
            ->where('memberId', $member->id)
            ->update(['is_delete' => 1]);

        // delete mandatory savings accounts and deposits
        DB::table('mfn_savings_accounts')
            ->where('memberId', $member->id)
            ->update(['is_delete' => 1]);

        // delete savings deposit
        DB::table('mfn_savings_deposit')
            ->where('memberId', $member->id)
            ->update(['is_delete' => 1]);

        $notification = array(
            'message'    => 'Successfully Deleted',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function getPassport($req, $operationType, $member = null)
    {
        $mfnConfigs = DB::table('mfn_config')
            ->whereIn('title', ['member', 'memberAdmissionRequiredFields', 'openingMemberAdmissionRequiredFields'])
            ->select('title', 'content')
            ->get();

        $errorMsg = null;

        $memberId = isset($member) ?$member->id : null;

        $mfnMemberConfig = json_decode($mfnConfigs->where('title', 'member')->first()->content);

        // set required valiables
        if ($operationType == 'store') {
            $memberAdmissionDate = Carbon::parse($req->admissionDate)->format('Y-m-d');
            $sysDate             = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $isOpening           = MfnService::isOpening(Auth::user()->branch_id);
            $branchId            = Auth::user()->branch_id;
            $samity              = DB::table('mfn_samity')->where('id', $req->samityId)->select('openingDate')->first();
        } else {
            $memberAdmissionDate = $member->admissionDate;
            $sysDate             = MfnService::systemCurrentDate($member->branchId);
            $isOpening           = DB::table('mfn_member_details')->where('memberId', $member->id)->value('isOpening');
            $branchId            = $member->branchId;
            $samity              = DB::table('mfn_samity')->where('id', $member->samityId)->select('openingDate')->first();
        }

        if ($isOpening && $operationType != 'delete') {
            $memberAdmissionDate = Carbon::parse($req->admissionDate)->format('Y-m-d');
        }

        if ($operationType != 'delete') {

            if ($isOpening) {
                $requiredFields = collect(json_decode($mfnConfigs->where('title', 'openingMemberAdmissionRequiredFields')->first()->content))->toArray();
            } else {
                $requiredFields = collect(json_decode($mfnConfigs->where('title', 'memberAdmissionRequiredFields')->first()->content))->toArray();
            }
            $GLOBALS['requiredFields'] = $requiredFields;

            $rules = array(
                'name'                         => 'required',
                'memberCode'                   => ['required', new Unique('mfn_members')],
                'gender'                       => $this->isRequired('gender'),
                'maritalStatusId'              => $this->isRequired('maritalStatusId'),
                'educationLevelId'             => $this->isRequired('educationLevelId'),
                'dateOfBirth'                  => $this->isRequired('dateOfBirth'),
                'fatherName'                   => $this->isRequired('fatherName'),
                'motherName'                   => $this->isRequired('motherName'),
                'nationalityId'                => $this->isRequired('nationalityId'),
                'mobileNo'                     => [$this->isRequired('mobileNo'), new MobileNo],
                'firstEvidenceTypeId'          => $this->isRequired('firstEvidenceTypeId'),
                'firstEvidence'                => $this->isRequired('firstEvidence'),
                'firstEvidenceIssuerCountryId' => $this->isRequired('firstEvidenceIssuerCountryId'),
                'primaryProductId'             => $this->isRequired('primaryProductId'),
                'preDivisionId'                => $this->isRequired('preDivisionId'),
                'preDistrictId'                => $this->isRequired('preDistrictId'),
                'preUpazilaId'                 => $this->isRequired('preUpazilaId'),
                'preUnionId'                   => $this->isRequired('preUnionId'),
                'preVillageId'                 => $this->isRequired('preVillageId'),
                'preStreetHolding'             => $this->isRequired('preStreetHolding'),
                'familyContactNumber'          => [$this->isRequired('familyContactNumber'), new MobileNo],
                'nomineeNames'                 => $this->isRequired('nomineeNames[]') . '|array|min:1',
                'nomineeMobileNos'             => $this->isRequired('nomineeMobileNos[]') . '|array|min:1',
                'nomineeMobileNos.*'           => [$this->isRequired('nomineeMobileNos[]'), new MobileNo],
                'nomineeRelationships'         => $this->isRequired('nomineeRelationships[]') . '|array|min:1',
                'nomineeRelationships.*'       => $this->isRequired('nomineeRelationships[]'),
                'nomineeShares'                => $this->isRequired('nomineeShares[]') . '|array|min:1',
                'nomineeShares.*'              => $this->isRequired('nomineeShares[]') . '|numeric|max:100',
                'referenceNames'               => $this->isRequired('referenceNames[]') . '|array|min:1',
                'referenceNames.*'             => $this->isRequired('referenceNames[]'),
                'referenceRelationships'       => $this->isRequired('referenceRelationships[]') . '|array|min:1',
                'referenceRelationships.*'     => $this->isRequired('referenceRelationships[]'),
                'referenceOrganizations'       => $this->isRequired('referenceOrganizations[]') . '|array|min:1',
                'referenceOrganizations.*'     => $this->isRequired('referenceOrganizations[]'),
                'referenceDesignations'        => $this->isRequired('referenceDesignations[]') . '|array|min:1',
                'referenceDesignations.*'      => $this->isRequired('referenceDesignations[]'),
                'referenceMobileNos'           => $this->isRequired('referenceMobileNos[]') . '|array|min:1',
                'referenceMobileNos.*'         => [$this->isRequired('referenceMobileNos[]'), new MobileNo],
                'professionId'                 => $this->isRequired('professionId'),
                'religionId'                   => $this->isRequired('religionId'),
            );

            if ($operationType == 'store') {
                $rules['samityId']      = 'required';
                $rules['admissionDate'] = 'required';
            }

            if ($operationType == 'update') {
                $rules['memberCode'] = [new Unique('mfn_members', $memberId)];
            }

            // if marital status is Married or Widow, then spouse name is required
            ($req->maritalStatusId == 1 || $req->maritalStatusId == 4) ?$rules['spouseName'] = $this->isRequired('spouseName') : false;

            // if permanent address is not same as present address
            if (!isset($req->sameAsPreesent)) {
                $rules = array_merge($rules, array(
                    'perDivisionId'    => $this->isRequired('perDivisionId'),
                    'perDistrictId'    => $this->isRequired('perDistrictId'),
                    'perUpazilaId'     => $this->isRequired('perUpazilaId'),
                    'perUnionId'       => $this->isRequired('perUnionId'),
                    'perVillageId'     => $this->isRequired('perVillageId'),
                    'perStreetHolding' => $this->isRequired('perStreetHolding'),
                ));
            }

            // if any other evidence information given, then need to fill all
            if ($req->secondEvidenceTypeId != '' || $req->secondEvidence != '' || $req->secondEvidenceIssuerCountryId != '' || $req->secondEvidenceValidTill != '') {
                $rules = array_merge($rules, array(
                    'secondEvidenceTypeId'          => $this->isRequired('secondEvidenceTypeId'),
                    'secondEvidence'                => $this->isRequired('secondEvidence'),
                    'secondEvidenceIssuerCountryId' => $this->isRequired('secondEvidenceIssuerCountryId'),
                ));

                // if secondEvidenceType is Passport / Driving License
                if ($req->secondEvidenceTypeId == 4 || $req->secondEvidenceTypeId == 5) {
                    $rules['secondEvidenceValidTill'] = $this->isRequired('secondEvidenceValidTill') . '|date';
                }
            }

            // if savings product is mandatory
            if (isset($req->savingProducts)) {
                $rules['savingProducts']   = 'required|array|min:1';
                $rules['savingsAmounts']   = 'required|array|min:1';
                $rules['savingsAmounts.*'] = 'required|numeric';
            }
            if (isset($req->maturePeriods)) {
                $rules['maturePeriods'] = 'required';
            }

            // sum of all nominee shares should be 100%
            if (is_array($req->nomineeShares)) {
                if (array_sum($req->nomineeShares) != 100 && $this->isRequired('nomineeShares[]') != 'nullable') {
                    $errorMsg = "Sahres of Nominees should be 100%";
                }
            }            

            // Image validation
            if ($operationType == 'store' && !$isOpening) {
                if ($mfnMemberConfig->isProfileImageMandatory == 'yes' && ($req->profileImageText == '' && !$req->hasFile('profileImage'))) {
                    $errorMsg = 'Member Profile Image is required.';
                }
                if ($mfnMemberConfig->isSignatureImageMandatory == 'yes' && ($req->signatureImageText == '' && !$req->hasFile('signatureImage'))) {
                    $errorMsg = 'Member Signature Image is required.';
                }
            }

            // validate image formats
            if ($req->hasFile('profileImage')) {
                $rules['profileImage'] = 'mimes:jpeg,bmp,png,gif,svg';
            }
            if ($req->hasFile('signatureImage')) {
                $rules['signatureImage'] = 'mimes:jpeg,bmp,png,gif,svg';
            }

            $validator = Validator::make($req->all(), $rules);

            // $attributes = array(
            //     'fieldOne'  => 'fieldOneName',
            //     'fieldTwo'  => 'SfieldTwoName',
            // );
            // $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // check uniqueness of some fields
            // mobileNo : should be unique with respect to current active members
            // First evidence : should be unique with respect to current active members
            // Other evidence : should be unique with respect to current active members
            $duplicatesMessages = $this->getDuplicateMemberData($req, $sysDate, $operationType, $memberId);

            if (count($duplicatesMessages) > 0) {
                $errorMsg = implode(' || ', $duplicatesMessages);
            }

            // check incoming mendatory savings products is valid or not
            if ($operationType == 'store' && !$isOpening) {
                $mendatorySavingProductIds = $this->getMandatorySavingProducts()->pluck('id')->all();
                if ($mendatorySavingProductIds != $req->savingProducts) {
                    $errorMsg = "Mendatory savings accounts not satisfy data.";
                }
            }

            // compare member primary product's effective date
            $primaryProduct = DB::table('mfn_loan_products')->where('id', $req->primaryProductId)->first();

            if ($primaryProduct->startDate > $memberAdmissionDate) {
                $errorMsg = "Member's admission date should be equal/greater than Primary Product start date. Primary Product's start date is " . Carbon::parse($primaryProduct->startDate)->format('d-m-Y');
            }

            // member admission date could not be less than samity opening date
            if ($memberAdmissionDate < $samity->openingDate) {
                $errorMsg = "Member's admission date should be equal/greater than Samity opening date. Samity opening date is " . Carbon::parse($samity->openingDate)->format('d-m-Y');
            }

            // member admission Date could not be less than branch start date
            $barnchOpenigDate = DB::table('gnl_branchs')->where('id', $branchId)->value('branch_opening_date');

            if (Carbon::parse($req->admissionDate)->format('Y-m-d') < $barnchOpenigDate) {
                $errorMsg = "Member Admission Date could not be less than Branch Opening Date.";
            }
        } // end not equal to delete

        // if it not from opening
        if (!$isOpening && $memberAdmissionDate != $sysDate) {
            $errorMsg = 'Member Admission Date is not matched with Branch date.';
        }

        // if it is from opening
        if ($isOpening) {
            $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'This is from opening, Branch should be on Software start date, i.e. ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        if ($isOpening && $memberAdmissionDate > $sysDate) {
            $errorMsg = 'Branch is on Opening date, Member Admission Date should be maximum ' . Carbon::parse($sysDate)->format('d-m-Y') . ' -- ' . $memberAdmissionDate;
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this can be updated/deleted from head office and corresponding branch
            if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $member->branchId) {
                $errorMsg = "This can be updated/deleted from head office and corresponding branch.";
            }
        }

        // if operation is update/delete, check any transaction exists (except mandatory one time savings account transaction)
        if ($errorMsg == null) {
            if ($operationType != 'store') {
                $hasAnyTransaction = $this->hasAnyTransaction($member);

                if ($hasAnyTransaction) {
                    $errorMsg = "Transaction exists, you can not ";
                    $errorMsg .= $operationType == 'update' ?'update' : 'delete';
                }
            }
        }

        $isValid = $errorMsg == null ?true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    /**
     * This function define the required rules from database mfn_config table
     *
     * @param   string  $fieldName  [field name of member]
     *
     * @return  [string]      [it will return either 'required' or 'nullable']
     */
    public function isRequired($fieldName)
    {
        $validation = 'nullable';
        if (isset($GLOBALS['requiredFields'][$fieldName])) {
            if ($GLOBALS['requiredFields'][$fieldName] == 'required') {
                $validation = 'required';
            }
        }

        return $validation;
    }

    public static function generateMemberCode($samityId)
    {
        $samityCode     = DB::table('mfn_samity')->where('id', $samityId)->first()->samityCode;
        $lastmemberCode = DB::table('mfn_members')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
            ])
            ->max('memberCode');

        $lastMemberCodeFromTransfers = DB::table('mfn_member_samity_transfers')
        ->where([
            ['is_delete', 0],
            ['oldSamityId', $samityId],
        ])
        ->max('oldMemberCode');

        $lastmemberCode = max($lastmemberCode, $lastMemberCodeFromTransfers);

        $mfnGnlConfig  = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $codeSeperator = $mfnGnlConfig->codeSeperator;

        $mfnMemberConfig        = json_decode(DB::table('mfn_config')->where('title', 'member')->first()->content);
        $memberCodeLengthItSelf = $mfnMemberConfig->memberCodeLengthItSelf;

        if ($lastmemberCode == null) {
            $memberNumber = 1;
        } else {
            $lastmemberCode = explode($codeSeperator, $lastmemberCode);
            $memberNumber   = (int)end($lastmemberCode) + 1;
        }

        $memberCode = $samityCode . $codeSeperator . str_pad($memberNumber, $memberCodeLengthItSelf, "0", STR_PAD_LEFT);

        return $memberCode;
    }

    public function getMandatorySavingProducts()
    {
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $mandatorySavingProducts = DB::table('mfn_savings_product AS sp')
            ->join('mfn_savings_product_type AS spt', 'spt.id', 'sp.productTypeId')
            ->where([
                ['sp.is_delete', 0],
                ['sp.status', 1],
                ['sp.isMandatoryOnMemberAdmission', 'Yes'],
            ])
            ->orderBy('productTypeId')
            ->select('sp.*', 'spt.name AS productType')
            ->get();

        // get the interest rates of the savings products
        foreach ($mandatorySavingProducts as $key => $savingsProduct) {
            if ($savingsProduct->collectionFrequencyId == 0) {
                $mandatorySavingProducts[$key]->collectionFrequency = 'One Time';
            } else {
                $mandatorySavingProducts[$key]->collectionFrequency = DB::table('mfn_savings_collection_frequency')->where('id', $savingsProduct->collectionFrequencyId)->value('name');
            }

            if ($savingsProduct->productTypeId == 1) { // if it is regular product
                $interestRate = DB::table('mfn_savings_product_interest_rates')
                    ->where([
                        ['is_delete', 0],
                        ['productId', $savingsProduct->id],
                        ['effectiveDate', '<=', $sysDate],
                    ])
                    ->orderBy('effectiveDate', 'desc')
                    ->limit(1)
                    ->value('interestRate');

                $mandatorySavingProducts[$key]->interestRate = $interestRate;
            } elseif ($savingsProduct->productTypeId == 2) { // if it is one time product
                $interestRates = DB::table('mfn_savings_product_interest_rates')
                    ->where([
                        ['is_delete', 0],
                        ['productId', $savingsProduct->id],
                        ['effectiveDate', '<=', $sysDate],
                    ])
                    ->where(function ($query) use ($sysDate) {
                        $query->where('validTill', '0000-00-00')
                            ->orWhere('validTill', '>=', $sysDate);
                    })
                    ->orderBy('durationMonth')
                    ->get();

                $mandatorySavingProducts[$key]->interestRates = [];

                foreach ($interestRates as $interestRate) {
                    $mandatorySavingProducts[$key]->interestRates[$interestRate->interestRate] = $interestRate->durationMonth;
                }
            }
        }

        return $mandatorySavingProducts;
    }

    public function getData(Request $req)
    {
        if ($req->context == 'samity') {
            // return member code, mandatory savings codes
            $memberCode = self::generateMemberCode($req->samityId);
            $samity     = DB::table('mfn_samity')->where('id', $req->samityId)->select('id', 'samityType')->first();

            $genders = [];
            if ($samity->samityType == 'Both') {
                $genders = ['Male' => 'Male', 'Female' => 'Female'];
            } elseif ($samity->samityType == 'Male') {
                $genders = ['Male' => 'Male'];
            } elseif ($samity->samityType == 'Female') {
                $genders = ['Female' => 'Female'];
            }

            $savingsProducts = $this->getMandatorySavingProducts();

            $savingsCodes = [];

            $savingsCycle = 1;
            foreach ($savingsProducts as $savingsProduct) {
                $savData                           = [];
                $savData['memberCode']             = $memberCode;
                $savData['productId']              = $savingsProduct->id;
                $savData['savingsCycle']           = $savingsCycle++;
                $savingsCode                       = app('App\Http\Controllers\MFN\Savings\SavingsAccountController')->generateSavingsCode($savData);
                $savingsCodes[$savingsProduct->id] = $savingsCode;
            }

            $data = array(
                'memberCode'   => $memberCode,
                'savingsCodes' => $savingsCodes,
                'genders'      => $genders,
            );
        }

        return response()->json($data);
    }

    public function getDuplicateMemberData($req, $sysDate, $operationType, $memberId)
    {
        // check uniqueness of some fields
        // mobileNo : should be unique with respect to current active members
        // National Id : should be unique with respect to current active members

        $mobileNo                     = $req->mobileNo;
        $firstEvidenceTypeId          = $req->firstEvidenceTypeId;
        $firstEvidence                = $req->firstEvidence;
        $firstEvidenceIssuerCountryId = $req->firstEvidenceIssuerCountryId;

        $secondEvidenceTypeId          = $req->secondEvidenceTypeId;
        $secondEvidence                = $req->secondEvidence;
        $secondEvidenceIssuerCountryId = $req->secondEvidenceIssuerCountryId;

        $duplicates = DB::table('mfn_member_details AS md')
            ->join('mfn_members AS m', 'm.id', 'md.memberId')
            ->where('m.is_delete', 0)
            ->where('md.is_delete', 0)
            ->where(function ($query) use ($sysDate) {
                $query->where('closingDate', '0000-00-00')
                    ->orWhere('closingDate', '>', $sysDate);
            })
            ->where(function ($query) use ($mobileNo, $firstEvidence, $firstEvidenceTypeId, $firstEvidenceIssuerCountryId, $secondEvidenceTypeId, $secondEvidence, $secondEvidenceIssuerCountryId) {

                $query->where('mobileNo', $mobileNo)
                    ->orWhere([
                        ['firstEvidenceTypeId', $firstEvidenceTypeId],
                        ['firstEvidence', $firstEvidence],
                        ['firstEvidenceIssuerCountryId', $firstEvidenceIssuerCountryId],
                    ]);

                if ($secondEvidence != '') {
                    $query->orWhere([
                        ['secondEvidenceTypeId', $secondEvidenceTypeId],
                        ['secondEvidence', $secondEvidence],
                        ['secondEvidenceIssuerCountryId', $secondEvidenceIssuerCountryId],
                    ]);
                }
            });

        if ($operationType == 'update') {
            $duplicates->where('memberId', '!=', $memberId);
        }
        $duplicates = $duplicates->select('md.*')->get();

        $messages = [];

        if ($duplicates->contains('mobileNo', $mobileNo) && $mobileNo != null) {
            $messages['mobileNo'] = 'Duplicate Mobile Number Found.';
        }

        if ($duplicates->contains('firstEvidence', $firstEvidence) && $firstEvidence != null) {
            $messages['firstEvidence'] = 'Duplicate First Evidence Found.';
        }

        if ($duplicates->contains('secondEvidence', $secondEvidence) && $secondEvidence != null) {
            $messages['secondEvidence'] = 'Duplicate Other Evidence Found.';
        }

        return $messages;
    }

    public static function generateMraCode($memberCode)
    {
        $mfnConfigs = DB::table('mfn_config')
            ->whereIn('title', ['member', 'general'])
            ->select('title', 'content')
            ->get();

        $mfnGnlConfig = json_decode($mfnConfigs->where('title', 'general')->first()->content);
        if ($mfnGnlConfig->companyType != 'ngo') {
            return '';
        }

        $codeSeperator = $mfnGnlConfig->codeSeperator;
        // $memberMraCode = $mfnGnlConfig->mfiCode . str_replace($codeSeperator, '', $memberCode);
        $memberMraCode = str_replace($codeSeperator, '', $memberCode);

        $mfnMemberConfig = json_decode($mfnConfigs->where('title', 'member')->first()->content);

        if (strlen($memberMraCode) > $mfnMemberConfig->mraCodeMaxLength) {
            return 'maxLengthCrossed';
        }

        return $memberMraCode;
    }

    public function hasAnyTransaction($member)
    {
        $mendatoryOneTimeAccountIds = DB::table('mfn_savings_accounts AS sa')
            ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
            ->where([
                ['sa.is_delete', 0],
                ['sa.isMandatory', 1],
                ['sa.memberId', $member->id],
                ['sp.productTypeId', 2],
            ])
            ->pluck('sa.id')
            ->toArray();

        $mandatoryDepositIds = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
                ['memberId', $member->id],
                ['date', $member->admissionDate],
            ])
            ->groupBy('accountId')
            ->whereIn('accountId', $mendatoryOneTimeAccountIds)
            ->pluck('id')
            ->toArray();

        $depositExists = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
                ['memberId', $member->id],
            ])
            ->whereNotIn('id', $mandatoryDepositIds)
            ->exists();

        $withdrawExists = DB::table('mfn_savings_withdraw')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
                ['memberId', $member->id],
            ])
            ->exists();

        $otherSavingsAccountExists = DB::table('mfn_savings_accounts')
            ->where([
                ['is_delete', 0],
                ['isMandatory', 0],
                ['memberId', $member->id],
            ])
            ->exists();

        $loanExists = DB::table('mfn_loans')
            ->where([
                ['is_delete', 0],
                ['memberId', $member->id],
            ])
            ->exists();

        $productTransferExists = DB::table('mfn_member_primary_product_transfers')
            ->where([
                ['is_delete', 0],
                ['memberId', $member->id],
            ])
            ->exists();

        $transactionExists = false;

        if ($depositExists || $withdrawExists || $otherSavingsAccountExists || $loanExists || $productTransferExists) {
            $transactionExists = true;
        }

        return $transactionExists;
    }
}
