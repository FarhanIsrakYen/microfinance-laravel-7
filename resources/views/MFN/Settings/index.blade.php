<!DOCTYPE html>
<html class="no-js css-menubar js-menubar disable-scrolling" lang="en">

<head>

    <?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    ?>
    <!-- Meta Start  -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="bootstrap admin template">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="tabTitle">MFN Setting</title>

    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-extend.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/site.min.css')}}">

    <!-- Plugins -->
    <link rel="stylesheet" href="{{asset('assets/vendor/animsition/animsition.css')}}">

    <!-- Plugins -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/flag-icon-css/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.net-bs4/dataTables.bootstrap4.css') }}">


    <!-- Fonts -->
    <link rel="stylesheet" href="{{asset('assets/fonts/web-icons/web-icons.min.css')}}">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    <script src="{{asset('assets/vendor/jquery/jquery.js')}}"></script>

    <!-- For jquery datepicker  -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


        <![endif]-->

    <!-- Scripts -->
    <script src="{{asset('assets/vendor/breakpoints/breakpoints.js')}}"></script>
    <script>
    Breakpoints();
    </script>

    <!-- JS Load  -->

    <script src="{{asset('assets/vendor/babel-external-helpers/babel-external-helpers.js')}}"></script>



    <!-- /////////////  -->
    <script src="{{asset('assets/js/Plugin.js')}}"></script>

    <!-- font icon link  -->
    <link rel="stylesheet" href="{{asset('assets/fonts/font-awesome/font-awesome.css')}}">
    <link rel="stylesheet" href="{{asset('assets/fonts/brand-icons/brand-icons.min.css')}}">

    <!--- For select option start --->
    <link rel="stylesheet" href="{{asset('assets/vendor/bootstrap-select/bootstrap-select.css')}}">
    <script src="{{asset('assets/vendor/bootstrap-select/bootstrap-select.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/bootstrap-select.js')}}"></script>
    <!--- For select option End --->

    <!--- Form Validation Start --->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/forms/form-validation.css')}}">
    <script src="{{asset('assets/js/forms/validator.min.js')}}"></script>
    <!--- Form Validation End --->

    <!-------------------- toastr start ---------------------->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/toastr.css')}}">
    <script type="text/javascript" src="{{asset('assets/js/toastr.min.js')}}"></script>
    <!-------------------- toastr end ---------------------->


    <!-------------------- Sweetalert ---------------------->
    <script type="text/javascript" src="{{asset('assets/js/sweetalert.min.js')}}"></script>

    <!-- Meta End -->

    <!-- For File Upload input feild design  -->
    <script src="{{ asset('assets/js/Plugin/input-group-file.js')}}"></script>

    <!-- Custom Ajax JS FILE  -->
    <script src="{{asset('assets/js/custom-ajax.js')}}"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/custom-print.css')}}">

    {{-- CSRF token added to ajax --}}
    <script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    </script>

</head>

<?php 
    use App\Services\CommonService as Common;
    $designationList = Common::ViewTableOrder('hr_designations',
                    ['is_delete' => 0],
                    ['id', 'name'],
                    ['name', 'ASC']);
?>

<body class="animsition site-navbar-small dashboard">
    @include('elements.navbar')
    <div class="page">
        <div class="page-header d-print-none">
            <h4 class="" id="pageName">Settings</h4>
            <ol class="breadcrumb text-uppercase">
                <li class="breadcrumb-item">
                    <a href="{{url('modules')}}">Home</a>
                </li>
            </ol>
        </div>

        <div class="page-content" id="details">
            <div class="panel">
                <div class="panel-body">
                    <form enctype="multipart/form-data" method="post" class="form-horizontal" 
                        data-toggle="validator" novalidate="true" autocomplete="off"> 
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                
                                <!-- General Start-->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">General</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Company Type</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="companyType" 
                                                    id="companyType" placeholder="Company Typer" value="{{ $general->companyType }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group" id="mfiCodeRow" style="display: none">
                                                <label class="input-title RequiredStar">MFI Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="mfiCode" 
                                                    id="mfiCode" placeholder="Mfi Code" value="{{ $general->mfiCode }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Code Seperator</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="codeSeperator" 
                                                    id="codeSeperator" placeholder="Code Seperator" value="{{ $general->codeSeperator }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Use WebCam</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="useWebCam" 
                                                    id="useWebCam" placeholder="Use WebCam" value="{{ $general->useWebCam }}" readonly>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Default Country</label>
                                                <div class="input-group">
                                                    <?php 
                                                        $defaultCountry = '';
                                                        foreach ($countries as $country) {
                                                            if ($general->defaultCountryId == $country->id) {
                                                                $defaultCountry = $country->name;
                                                            }
                                                        }
                                                     ?>
                                                    <input type="text" class="form-control round" name="defaultCountryId" 
                                                    id="defaultCountryId" placeholder="Default Country" value="{{ $defaultCountry }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Country Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="countryCode" 
                                                    id="countryCode" placeholder="Country Code" value="{{ $general->countryCode }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Mobile No Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mobileNoLength" 
                                                    id="mobileNoLength" placeholder="Mobile No Length" value="{{ $general->mobileNoLength }}" readonly>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- General End -->

                                <!-- Branch Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Branch</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Branch Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="branchCodeLengthItSelf" id="branchCodeLengthItSelf" 
                                                    placeholder="Branch Code Length" value="{{ $branch->branchCodeLengthItSelf }}" readonly>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Branch End -->

                                <!-- Samity Start -->
                                <div class="panel panel-default" id="samityRow" style="display: none">
                                    <div class="panel-heading p-2 mb-4">Samity</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Samity Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="samityCodeLengthItSelf" id="samityCodeLengthItSelf" 
                                                    placeholder="Samity Code Length" value="{{ $samity->samityCodeLengthItSelf }}" readonly>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Samity End -->

                                <!-- Member Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Member</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="memberCodeLengthItSelf" id="memberCodeLengthItSelf" 
                                                    placeholder="Member Code Length" value="{{ $member->memberCodeLengthItSelf }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group" id="mraCodeRow" style="display: none">
                                                <label class="input-title RequiredStar">MRA Code Max Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mraCodeMaxLength" 
                                                    id="mraCodeMaxLength" placeholder="Mra Code Max Length" value="{{ $member->mraCodeMaxLength }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Min Age</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="minAge" 
                                                    id="minAge" placeholder="Member Min Age" value="{{ $member->minAge }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Max Age</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="maxAge" 
                                                    id="maxAge" placeholder="Member Max Age" value="{{ $member->maxAge }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Admission Fee</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="admissionFee" 
                                                    id="admissionFee" placeholder="Member's Admission Fee" 
                                                    value="{{ $member->admissionFee }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Country Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="countryCode" 
                                                    id="countryCode" placeholder="Member's Country Code" 
                                                    value="{{ $member->countryCode }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Mobile No Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mobileNoLength" 
                                                    id="mobileNoLength" placeholder="Member's Mobile No Length" 
                                                    value="{{ $member->mobileNoLength }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Passport Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="passportLength" 
                                                    id="passportLength" placeholder="Member's Passport Length" 
                                                    value="{{ $member->passportLength }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's National Id Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="nationalIdLength" 
                                                    id="nationalIdLength" placeholder="Member's National Id Length" 
                                                    value="{{ is_array($member->nationalIdLength) ? implode(' , ', $member->nationalIdLength) : $member->nationalIdLength }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Profile Image Size</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="profileImageSize" 
                                                    id="profileImageSize" placeholder="Width : Height" 
                                                    value="{{ $member->profileImageSize }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Signature Image Size</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="signatureImageSize" 
                                                    id="signatureImageSize" placeholder="Width : Height" 
                                                    value="{{ $member->signatureImageSize }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member's Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="isProfileImageMandatory" 
                                                    id="isProfileImageMandatory" placeholder="Is Member's Profile Image Mandatory" 
                                                    value="{{ $member->isProfileImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member's Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="isSignatureImageMandatory" 
                                                    id="isSignatureImageMandatory" placeholder="Is Member's Signature Image Mandatory" 
                                                    value="{{ $member->isSignatureImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Member End -->

                                <!-- Field Officer Designation Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Designations of Field Officer</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Designations of Field Officer</label>
                                                <div class="input-group">
                                                    <?php 
                                                        $designation = [];
                                                        foreach ($designationList as $des) {
                                                            if(in_array($des->id, $fieldOfficers)){
                                                                array_push($designation, $des->name);
                                                            }
                                                        }
                                                     ?>
                                                    <textarea type="text" class="form-control round textNumber" rows="2" 
                                                    name="desig_arr[]" id="desig_arr" placeholder="Designations of Field Officer" value="{{ implode(' , ', $designation) }}" readonly>{{ implode(' , ', $designation) }}</textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Field Officer Designation End -->

                                <!-- Savings Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Savings</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Probation Frequency</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="probationFrequency" 
                                                    id="probationFrequency" placeholder="Probation Frequency" 
                                                    value="{{ $savings->probationFrequency }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Savings Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="savingsCodeLengthItSelf" id="savingsCodeLengthItSelf" 
                                                    placeholder="Savings Code Length" value="{{ $savings->savingsCodeLengthItSelf }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Product Prefix Required In Savings Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="isProductPrefixRequiredInSavingsCode" id="isProductPrefixRequiredInSavingsCode" 
                                                    placeholder="Is Product Prefix Required In Savings Code" value="{{ $savings->isProductPrefixRequiredInSavingsCode }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Allow Auto Process</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="allowAutoProcess" 
                                                    id="allowAutoProcess" placeholder="Allow Auto Process" 
                                                    value="{{ $savings->allowAutoProcess }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Allow Multiple Transaction</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="allowMultipleTransaction" id="allowMultipleTransaction" 
                                                    placeholder="Allow Multiple Transaction" 
                                                    value="{{ $savings->allowMultipleTransaction }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Savings End -->

                                <!-- Loan Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Loan</div>
                                    <div class="panel-body">

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Loan Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="loanCodeLengthItSelf" id="loanCodeLengthItSelf" placeholder="Loan Code Length" value="{{ $loan->loanCodeLengthItSelf }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Product Prefix Required In Loan Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="isProductPrefixRequiredInLoanCode" id="isProductPrefixRequiredInLoanCode" placeholder="Is Product Prefix Required In Loan Code" value="{{ $loan->isProductPrefixRequiredInLoanCode }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="isMemberProfileImageMandatory" id="isMemberProfileImageMandatory" placeholder="Is Member Profile Image Mandatory" value="{{ $loan->isMemberProfileImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="isMemberSignatureImageMandatory" id="isMemberSignatureImageMandatory" placeholder="Is Member Signature Image Mandatory" value="{{ $loan->isMemberSignatureImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Guarantor Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="isGuarantorProfileImageMandatory" id="isGuarantorProfileImageMandatory" placeholder="Is Guarantor Profile Image Mandatory" value="{{ $loan->isGuarantorProfileImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Guarantor Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="isGuarantorSignatureImageMandatory" id="isGuarantorSignatureImageMandatory" placeholder="Is Guarantor Signature Image Mandatory" value="{{ $loan->isGuarantorSignatureImageMandatory }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Loan End -->

                                <!-- Regular Loan Start -->
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Regular Loan</div>
                                    <div class="panel-body">

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Installment Amount Generate Policies</label>
                                                <ul id="sortable" class="list-group">
                                                    @foreach($regularLoan->installmentAmountGeneratePolicies as $policy)
                                                    <input type="text" class="form-control round mb-2" name="installmentAmountGeneratePolicies[]" 
                                                    id="installmentAmountGeneratePolicies" placeholder="Prefered Amounts" 
                                                    value="{{ $policy }}" readonly>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Prefered Amounts</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="preferedAmounts" 
                                                    id="preferedAmounts" placeholder="Prefered Amounts" 
                                                    value="{{ implode(' , ', $regularLoan->preferedAmounts) }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Monthly Loan Month Overflow</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" 
                                                    name="monthlyLoanMonthOverflow" id="monthlyLoanMonthOverflow" placeholder="Monthly Loan Month Overflow" value="{{ $regularLoan->monthlyLoanMonthOverflow }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Regular Loan End -->
                                
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                        <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('elements.footer')

    <!-- Meta 2 -->
    <!-- Core  -->
    <!-- <script src="assets/vendor/babel-external-helpers/babel-external-helpers.js"></script>
       <script src="assets/vendor/jquery/jquery.js"></script> -->
    <script src="{{asset('assets/vendor/popper-js/umd/popper.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/bootstrap.js')}}"></script>
    <script src="{{asset('assets/vendor/animsition/animsition.js')}}"></script>
    <script src="{{asset('assets/vendor/mousewheel/jquery.mousewheel.js')}}"></script>
    <script src="{{asset('assets/vendor/asscrollbar/jquery-asScrollbar.js')}}"></script>
    <script src="{{asset('assets/vendor/asscrollable/jquery-asScrollable.js')}}"></script>

    <!-- Plugins -->
    <script src="{{asset('assets/vendor/switchery/switchery.js')}}"></script>
    <script src="{{asset('assets/vendor/intro-js/intro.js')}}"></script>
    <script src="{{asset('assets/vendor/screenfull/screenfull.js')}}"></script>
    <script src="{{asset('assets/vendor/slidepanel/jquery-slidePanel.js')}}"></script>
    <script src="{{asset('assets/vendor/chartist/chartist.js')}}"></script>
    <script src="{{asset('assets/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.js')}}"></script>
    <script src="{{asset('assets/vendor/aspieprogress/jquery-asPieProgress.js')}}"></script>
    <script src="{{asset('assets/vendor/matchheight/jquery.matchHeight-min.js')}}"></script>
    <script src="{{asset('assets/vendor/jquery-selective/jquery-selective.min.js')}}"></script>
    <!-- <script src="{{asset('assets/vendor/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script> -->
    <script src="{{asset('assets/vendor/clockpicker/bootstrap-clockpicker.js')}}"></script>

    <!-- Scripts -->
    <script src="{{asset('assets/js/Component.js')}}"></script>
    <!-- <script src="assets/js/Plugin.js"></script> -->
    <script src="{{asset('assets/js/Base.js')}}"></script>
    <script src="{{asset('assets/js/Config.js')}}"></script>

    <script src="{{asset('assets/js/Section/Menubar.js')}}"></script>
    <script src="{{asset('assets/js/Section/Sidebar.js')}}"></script>
    <script src="{{asset('assets/js/Section/PageAside.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/menu.js')}}"></script>

    <!-- Config -->
    <script src="{{asset('assets/js/config/colors.js')}}"></script>
    <script src="{{asset('assets/js/config/tour.js')}}"></script>
    <!-- <script>Config.set('assets', '../assets');</script> -->

    <!-- Page -->
    <script src="{{asset('assets/js/Site.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/asscrollable.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/slidepanel.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/switchery.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/matchheight.js')}}"></script>
    <script src="{{asset('assets/js/Plugin/aspieprogress.js')}}"></script>
    <!-- <script src="{{asset('assets/js/Plugin/bootstrap-datepicker.js')}}"></script> -->
    <script src="{{asset('assets/js/Plugin/asscrollable.js')}}"></script>

    <script src="{{asset('assets/js/dashboard/team.js')}}"></script>


    <!-- Plugins -->
    <script src="{{ asset('assets/vendor/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>

    <script src="{{ asset('assets/vendor/asrange/jquery-asRange.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootbox/bootbox.js') }}"></script>


    <!-- Page -->
    <script src="{{ asset('assets/js/Plugin/datatables.js') }}"></script>
    <!-- <script src="{{ asset('assets/js/tables/datatable.js') }}"></script> -->

    <!-- Custom JS FILE  -->
    <script src="{{asset('assets/js/custom-js.js')}}"></script>

</body>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        
        $(".cls-select2-mul").select2({
            placeholder: "Select Designation"
        });
        $('#companyType').change(function () {
            if ($('#companyType').val() == 'ngo') {
                $('#samityRow,#mraCodeRow').show('slow');
                $('#mfiCodeRow').show('slow');
            } else {
                $('#samityRow,#mraCodeRow').hide('slow');
                $('#mfiCodeRow').hide('slow');
            }
        });

        var companyType = $('#companyType').val();
        if (companyType == 'ngo') {
           $('#mfiCodeRow,#samityRow,#mraCodeRow').show('slow'); 
        } 

    });
</script>
</html>
