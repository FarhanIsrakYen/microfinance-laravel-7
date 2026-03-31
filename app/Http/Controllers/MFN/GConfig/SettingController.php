<?php
namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();

    }

    public function index(Request $req)
    {
        // if the config is already done, then redirect to the home page
        $isConfigCompleted = DB::table('mfn_config')->where('title', 'isConfigCompleted')->first()->content;

        if ($isConfigCompleted == 'yes') {
            return redirect('mfn');
        }

        $countries = DB::table('gnl_country')->select('id', 'name')->get();

        $general = json_decode(DB::table('mfn_config')
                ->where('title', 'general')
                ->first()->content);

        $branch = json_decode(DB::table('mfn_config')
                ->where('title', 'branch')
                ->first()->content);
        $samity = json_decode(DB::table('mfn_config')
                ->where('title', 'samity')
                ->first()->content);
        $member = json_decode(DB::table('mfn_config')
                ->where('title', 'member')
                ->first()->content);
        $fieldOfficers = json_decode(DB::table('mfn_config')
                ->where('title', 'fieldOfficerHrDesignationIds')
                ->first()->content);

        $savings = json_decode(DB::table('mfn_config')
                ->where('title', 'savings')
                ->first()->content);

        $loan = json_decode(DB::table('mfn_config')
                ->where('title', 'loan')
                ->first()->content);
        $regularLoan = json_decode(DB::table('mfn_config')
                ->where('title', 'regularLoan')
                ->first()->content);

        $data = array(
            'countries'     => $countries,
            'general'       => $general,
            'branch'        => $branch,
            'samity'        => $samity,
            'member'        => $member,
            'fieldOfficers' => $fieldOfficers,
            'savings'       => $savings,
            'loan'          => $loan,
            'regularLoan'   => $regularLoan,
        );

        return view('MFN.Settings.index', $data);
    }

    public function add(Request $req)
    {
        // if the config is already done, then redirect to the home page
        $isConfigCompleted = DB::table('mfn_config')->where('title', 'isConfigCompleted')->first()->content;

        if ($isConfigCompleted == 'yes') {
            return redirect('mfn');
        }
        
        if (Auth::user()->branch_id != 1) {
            echo "Sorry, This service is not availabe from branch.";
            exit();
        }
        if ($req->isMethod('post')) {

            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message'    => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $general_arrs = array(
                'companyType'      => $req->companyType,
                'mfiCode'          => $req->mfiCode,
                'codeSeperator'    => $req->codeSeperator,
                'useWebCam'        => $req->useWebCam,
                'defaultCountryId' => $req->defaultCountryId,
                'countryCode'      => $req->countryCode,
                'mobileNoLength'   => $req->mobileNoLength,
            );

            $isUpdate1 = DB::table('mfn_config')
                ->where('title', 'general')
                ->update(['content' => json_encode($general_arrs)]);

            $branch_arrs = array('branchCodeLengthItSelf' => $req->branchCodeLengthItSelf);
            $isUpdate2   = DB::table('mfn_config')
                ->where('title', 'branch')
                ->update(['content' => json_encode($branch_arrs)]);

            if (isset($req->samityCodeLengthItSelf)) {
                $samity_arrs = array('samityCodeLengthItSelf' => $req->samityCodeLengthItSelf);
                $isUpdate3   = DB::table('mfn_config')
                    ->where('title', 'samity')
                    ->update(['content' => json_encode($samity_arrs)]);
            }

            $nationalIdLengths = explode(',', trim($req->nationalIdLength, ','));
            sort($nationalIdLengths);
            $member_arrs = array(
                'memberCodeLengthItSelf'    => $req->memberCodeLengthItSelf,
                'mraCodeMaxLength'          => $req->mraCodeMaxLength,
                'minAge'                    => $req->minAge,
                'maxAge'                    => $req->maxAge,
                'admissionFee'              => $req->admissionFee,
                // 'countryCode'               => $req->countryCode,
                // 'mobileNoLength'            => $req->mobileNoLength,
                'passportLength'            => $req->passportLength,
                'nationalIdLength'          => $nationalIdLengths,
                'profileImageSize'          => $req->profileImageSize,
                'signatureImageSize'        => $req->signatureImageSize,
                'isProfileImageMandatory'   => $req->isProfileImageMandatory,
                'isSignatureImageMandatory' => $req->isSignatureImageMandatory,
            );
            $isUpdate4 = DB::table('mfn_config')
                ->where('title', 'member')
                ->update(['content' => json_encode($member_arrs)]);

            $desig_arrs = isset($req['desig_arr']) ? $req['desig_arr'] : array();

            $isUpdate5 = DB::table('mfn_config')
                ->where('title', 'fieldOfficerHrDesignationIds')
                ->update(['content' => json_encode($desig_arrs)]);

            $savings_arrs = array(
                'probationFrequency'                   => $req->probationFrequency,
                'savingsCodeLengthItSelf'              => $req->savingsCodeLengthItSelf,
                'isProductPrefixRequiredInSavingsCode' => $req->isProductPrefixRequiredInSavingsCode,
                'allowAutoProcess'                     => $req->allowAutoProcess,
                'allowMultipleTransaction'             => $req->allowMultipleTransaction,
            );
            $isUpdate6 = DB::table('mfn_config')
                ->where('title', 'savings')
                ->update(['content' => json_encode($savings_arrs)]);

            $loan_arrs = array(
                'loanCodeLengthItSelf'               => $req->loanCodeLengthItSelf,
                'isProductPrefixRequiredInLoanCode'  => $req->isProductPrefixRequiredInLoanCode,
                'isMemberProfileImageMandatory'      => $req->isMemberProfileImageMandatory,
                'isMemberSignatureImageMandatory'    => $req->isMemberSignatureImageMandatory,
                'isGuarantorProfileImageMandatory'   => $req->isGuarantorProfileImageMandatory,
                'isGuarantorSignatureImageMandatory' => $req->isGuarantorSignatureImageMandatory,
            );
            $isUpdate7 = DB::table('mfn_config')
                ->where('title', 'loan')
                ->update(['content' => json_encode($loan_arrs)]);

            $arr1          = [0];
            $arr2          = [100];
            $arr3          = [];
            $mergedAmounts = [];
            $amtArray      = explode(',', trim($req->preferedAmounts, ','));
            foreach ($amtArray as $key => $value) {
                array_push($arr3, (int) $value);
            }
            $mergedAmounts = array_merge($arr1, $arr3, $arr2);
            sort($mergedAmounts);
            $regular_loan_arrs = array(
                'installmentAmountGeneratePolicies' => $req['installmentAmountGeneratePolicies'],
                'preferedAmounts'                   => $mergedAmounts,
                'monthlyLoanMonthOverflow'          => $req->monthlyLoanMonthOverflow,
            );

            $isUpdate8 = DB::table('mfn_config')
                ->where('title', 'regularLoan')
                ->update(['content' => json_encode($regular_loan_arrs)]);

            $savingProvisionConf = array(
                'provisionFrequency'              => $req->provisionFrequency,
                'generateMethod'                  => $req->generateMethod,
                'generateProvisionHavingWithdraw' => $req->generateProvisionHavingWithdraw,
            );

            $isUpdate9 = DB::table('mfn_config')
                ->where('title', 'provision')
                ->update(['content' => json_encode($savingProvisionConf)]);

            $isUpdateMail = DB::table('mfn_config')
                ->where('title', 'mail')
                ->update(['content' => $req->mailMethod]);

            $isUpdateMail = DB::table('mfn_config')
                ->where('title', 'sms')
                ->update(['content' => $req->smsMethod]);

            $isUpdate10 = DB::table('mfn_config')
                ->where('title', 'isConfigCompleted')
                ->update(['content' => 'yes']);

            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        }
        $fieldOfficers = json_decode(DB::table('mfn_config')
                ->where('title', 'fieldOfficerHrDesignationIds')
                ->first()->content);
        $countries = DB::table('gnl_country')->select('id', 'name')->get();
        return view('MFN.Settings.add', compact('fieldOfficers', 'countries'));
    }

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;

        if ($operationType != 'delete') {
            $rules = array(
                'companyType'                          => 'required',
                'codeSeperator'                        => 'required',
                'useWebCam'                            => 'required',
                'defaultCountryId'                     => 'required',
                'countryCode'                          => 'required',
                'mobileNoLength'                       => 'required',
                'branchCodeLengthItSelf'               => 'required',
                'memberCodeLengthItSelf'               => 'required',
                'mraCodeMaxLength'                     => 'required',
                'minAge'                               => 'required',
                'maxAge'                               => 'required',
                'admissionFee'                         => 'required',
                'countryCode'                          => 'required',
                'mobileNoLength'                       => 'required',
                'passportLength'                       => 'required',
                'nationalIdLength'                     => 'required',
                'profileImageSize'                     => 'required',
                'signatureImageSize'                   => 'required',
                'isProfileImageMandatory'              => 'required',
                'isSignatureImageMandatory'            => 'required',
                'desig_arr'                            => 'required',
                'probationFrequency'                   => 'required',
                'savingsCodeLengthItSelf'              => 'required',
                'isProductPrefixRequiredInSavingsCode' => 'required',
                'allowAutoProcess'                     => 'required',
                'allowMultipleTransaction'             => 'required',
                'loanCodeLengthItSelf'                 => 'required',
                'isProductPrefixRequiredInLoanCode'    => 'required',
                'isMemberProfileImageMandatory'        => 'required',
                'isMemberSignatureImageMandatory'      => 'required',
                'isGuarantorProfileImageMandatory'     => 'required',
                'isGuarantorSignatureImageMandatory'   => 'required',
                'installmentAmountGeneratePolicies'    => 'required',
                'preferedAmounts'                      => 'required',
                'monthlyLoanMonthOverflow'             => 'required',
                'provisionFrequency'                   => 'required',
                'generateMethod'                       => 'required',
                'generateProvisionHavingWithdraw'      => 'required',
            );

            if ($req->companyType == 'ngo' && $operationType == 'store') {
                $rules = array_merge($rules, array(
                    'mfiCode'                => 'required',
                    'samityCodeLengthItSelf' => 'required',
                ));
            }

            $validator = Validator::make($req->all(), $rules);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

}
