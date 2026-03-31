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
    @include('elements.topbar')
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
                                                    <select class="form-control round clsSelect2"
                                                     name="companyType" id="companyType"
                                                     required data-error="Please Select Company Type">
                                                        <option value="">Select</option>
                                                        <option value="ngo">NGO</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group" id="mfiCodeRow" style="display: none">
                                                <label class="input-title RequiredStar">MFI Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="mfiCode"
                                                    id="mfiCode" placeholder="Enter Mfi Code">
                                                </div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Code Seperator</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="codeSeperator"
                                                    id="codeSeperator" placeholder="Enter Code Seperator" required
                                                    data-error="Please Enter Code Seperator" maxlength="1">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Use WebCam</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="useWebCam" id="useWebCam"
                                                    required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Default Country</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="defaultCountryId"
                                                    id="defaultCountryId" required data-error="Please Select A Country">
                                                        <option value="">Select</option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Country Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="countryCode"
                                                    id="countryCode" placeholder="Enter Country Code" required
                                                    data-error="Please Enter Country Code">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Mobile No Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mobileNoLength"
                                                    id="mobileNoLength" placeholder="Enter Mobile No Length" required
                                                    data-error="Please Enter Mobile No Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    placeholder="Enter Branch Code Length" required
                                                    data-error="Please Enter Code Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    placeholder="Enter Samity Code Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    placeholder="Enter Member Code Length" required
                                                    data-error="Please Enter Code Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group" id="mraCodeRow" style="display: none">
                                                <label class="input-title RequiredStar">MRA Code Max Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mraCodeMaxLength"
                                                    id="mraCodeMaxLength" placeholder="Enter Mra Code Max Length" required
                                                    data-error="Please Enter Mra Code Max Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Min Age</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="minAge"
                                                    id="minAge" placeholder="Enter Member Min Age" required
                                                    data-error="Please Enter Min Age">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Max Age</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="maxAge"
                                                    id="maxAge" placeholder="Enter Member Max Age" required
                                                    data-error="Please Enter Max Age">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Admission Fee</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="admissionFee"
                                                    id="admissionFee" placeholder="Enter Member's Admission Fee" required
                                                    data-error="Please Enter Admission Fee">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            {{-- <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Country Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="countryCode"
                                                    id="countryCode" placeholder="Enter Member's Country Code" required
                                                    data-error="Please Enter Country Code">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div> --}}
                                            {{-- <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Mobile No Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="mobileNoLength"
                                                    id="mobileNoLength" placeholder="Enter Member's Mobile No Length" required
                                                    data-error="Please Enter Mobile No Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div> --}}
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Passport Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="passportLength"
                                                    id="passportLength" placeholder="Enter Member's Passport Length" required
                                                    data-error="Please Enter Passport Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's National Id Length</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="nationalIdLength"
                                                    id="nationalIdLength" placeholder="Enter Member's National Id Length" required
                                                    data-error="Please Enter National Id Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Profile Image Size</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="profileImageSize"
                                                    id="profileImageSize" placeholder="Width : Height" required
                                                    data-error="Please Enter Profile Image Size">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Member's Signature Image Size</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="signatureImageSize"
                                                    id="signatureImageSize" placeholder="Width : Height" required
                                                    data-error="Please Enter Signature Image Size">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member's Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isProfileImageMandatory" id="isProfileImageMandatory" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member's Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isSignatureImageMandatory" id="isSignatureImageMandatory" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    <select class="form-control round cls-select2-mul" multiple="multiple"
                                                     name="desig_arr[]" id="desig_arr"
                                                     required data-error="Please Select Designation">
                                                        <option value="">Select</option>
                                                        @foreach($designationList as $des)
                                                            <option value="{{ $des->id }}" @if(in_array($des->id, $fieldOfficers))
                                                                {{ 'selected' }}
                                                                @endif>{{ $des->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    <select class="form-control clsSelect2" name="probationFrequency" id="probationFrequency" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="daily">Daily</option>
                                                        <option value="weekly">Weekly</option>
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="halfyearly">Halfyearly</option>
                                                        <option value="yearly">Yearly</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Savings Code Length (Itself)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round textNumber" name="savingsCodeLengthItSelf" id="savingsCodeLengthItSelf"
                                                    placeholder="Enter Savings Code Length" required
                                                    data-error="Please Enter Savings Code Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Product Prefix Required In Savings Code</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isProductPrefixRequiredInSavingsCode" id="isProductPrefixRequiredInSavingsCode" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Allow Auto Process</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="allowAutoProcess" id="allowAutoProcess" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Allow Multiple Transaction</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="allowMultipleTransaction" id="allowMultipleTransaction" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    name="loanCodeLengthItSelf" id="loanCodeLengthItSelf"
                                                    placeholder="Enter Loan Code Length" required
                                                    data-error="Please Enter Loan Code Length">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Product Prefix Required In Loan Code</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isProductPrefixRequiredInLoanCode" id="isProductPrefixRequiredInLoanCode" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isMemberProfileImageMandatory" id="isMemberProfileImageMandatory" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Member Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isMemberSignatureImageMandatory" id="isMemberSignatureImageMandatory" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Guarantor Profile Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isGuarantorProfileImageMandatory" id="isGuarantorProfileImageMandatory" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Is Guarantor Signature Image Mandatory</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="isGuarantorSignatureImageMandatory"
                                                    id="isGuarantorSignatureImageMandatory" required
                                                    data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
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
                                                    <li class="ui-state-default list-group-item mb-2">
                                                        <input type="checkbox" id="percent" name="installmentAmountGeneratePolicies[]" value="2.5Percent" checked  style="display: none;">
                                                        <label for="percent">2.5 Percent</label>
                                                   </li>
                                                    <li class="ui-state-default list-group-item mb-2">
                                                        <input type="checkbox" id="higestPreferedAmount" name="installmentAmountGeneratePolicies[]" value="higestPreferedAmount" checked
                                                        style="display: none;">
                                                        <label for="higestPreferedAmount">Highest Prefered Amount</label>
                                                    </li>
                                                    <li class="ui-state-default list-group-item mb-2">
                                                        <input type="checkbox" id="higestPreferedAmount" name="installmentAmountGeneratePolicies[]" value="nearestPreferedAmount" checked
                                                        style="display: none;">
                                                        <label for="nearestPreferedAmount">Nearest Prefered Amount</label>
                                                    </li>
                                                    <li class="ui-state-default list-group-item mb-2" data-value="">
                                                        <input type="checkbox" id="higestPreferedAmount" name="installmentAmountGeneratePolicies[]" value="roundToDecade" checked
                                                        style="display: none;">
                                                        <label for="roundToDecade">Round To Decade</label>
                                                    </li>
                                                    <li class="ui-state-default list-group-item mb-2">
                                                        <input type="checkbox" id="higestPreferedAmount" name="installmentAmountGeneratePolicies[]" value="roundToOne" checked
                                                        style="display: none;">
                                                        <label for="roundToOne">Round To One</label>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Prefered Amounts</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" name="preferedAmounts"
                                                    id="preferedAmounts" placeholder="Enter Prefered Amounts" required
                                                    data-error="Please Enter Prefered Amounts" min="1" max="99">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Monthly Loan Month Overflow</label>
                                                <div class="input-group">
                                                    <select class="form-control clsSelect2" name="monthlyLoanMonthOverflow" id="monthlyLoanMonthOverflow" required data-error="Please Select An Option">
                                                        <option value="">Select</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Regular Loan End -->

                                {{-- savings provision start --}}
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Saving Provision</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Provision Frequency</label>
                                                <div class="input-group">
                                                    <select name="provisionFrequency" id="provisionFrequency" class="form-control clsSelect2" required data-error="Please select!!">
                                                        <option value="">Select Option</option>
                                                        <option value="daily">Daily</option>
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="half-yearly">Half-Yearly</option>
                                                        <option value="yearly">Yearly</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group" id="generatedMethodDiv">
                                                <label class="input-title RequiredStar">Generated Method</label>
                                                <div class="input-group">
                                                    <select name="generateMethod" id="generateMethod" class="form-control clsSelect2" required data-error="Please select!!">
                                                        <option value="">Select Option</option>
                                                        <option value="daily">Daily</option>
                                                        <option value="average">Average</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Generated Provision Having WithDraw?</label>
                                                <div class="input-group">
                                                    <select name="generateProvisionHavingWithdraw" id="generateProvisionHavingWithdraw" class="form-control clsSelect2" required data-error="Please select!!">
                                                        <option value="">Select Option</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- savings provision end --}}


                                {{-- Notification start --}}
                                <div class="panel panel-default">
                                    <div class="panel-heading p-2 mb-4">Notification System</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">Mail Method</label>
                                                <div class="input-group">
                                                    <select name="mailMethod" id="mailMethod" class="form-control clsSelect2" required data-error="Please select!!">
                                                        <option value="">Select Option</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>

                                            <div class="col-lg-3 form-group">
                                                <label class="input-title RequiredStar">SMS Method</label>
                                                <div class="input-group">
                                                    <select name="smsMethod" id="smsMethod" class="form-control clsSelect2" required data-error="Please select!!">
                                                        <option value="">Select Option</option>
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Notification end --}}

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                        <button type="submit" class="btn btn-primary btn-round">Save</button>
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
        $( function() {
            $( "#sortable" ).sortable();
            $( "#sortable" ).disableSelection();
         });
        $(".cls-select2-mul").select2({
            placeholder: "Select Designation"
        });
        //After form submit actions
        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize(),
                })
                .done(function (response) {
                    if (response['alert-type'] == 'error') {
                        swal({
                            icon: 'error',
                            title: 'Oops...',
                            text: response['message'],
                        });
                        $('form').find(':submit').prop('disabled', false);
                    } else {

                        swal({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'Once Submitted, these values cant be changed !',
                            confirmButtonText: "Ok"
                        }).then((isConfirm) => {
                            if (isConfirm) {
                                $('form').trigger("reset");
                                swal({
                                    icon: 'success',
                                    title: 'Success...',
                                    text: response['message'],
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(function () {
                                    window.location.href = "{{ url('mfn') }}";
                                });
                            }
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                })
                .always(function () {
                    console.log("complete");
                });
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

        $('#nationalIdLength').on('change, input', function(e) {
            var currentInput = $(this).val();
            var fixedInput = currentInput.replace(/[A-Za-z!@#$%^&*().-/]/, '');
            var fixedInput = fixedInput.replace(' ,',',');
            fixedInput = fixedInput.replace(/,{2,}/g, ',');
            $(this).val(fixedInput);

        });

        $('#preferedAmounts').on('change, input', function(e) {
            var currentInput = $(this).val();
            var fixedInput = currentInput.replace(/[A-Za-z!@#$%^&*().-/]/, '');
            var fixedInput = fixedInput.replace(' ,',',');
            // fixedInput = fixedInput.replace(/,+/g,',');
            fixedInput = fixedInput.replace(/,{2,}/g, ',');
            $(this).val(fixedInput);

            if (e.keyCode == 188) { // KeyCode For comma is 188
                var str = $('#preferedAmounts').val();
                str.substring(0, str.length - 2);
                var arr = str.split(",");
                for (var i = 0; i < arr.length; i++) {

                    if (parseInt(arr[i]) < 1) {
                        // arr[i] = arr[i].replace(arr[i], "");
                        $(this).val('');
                        alert("Preferred Amount must be Greater than Zero");

                    }
                    else if (parseInt(arr[i]) > 99) {
                        alert("Preferred Amount must be Less than 100");
                        arr[i] = arr[i].replace(arr[i], "");
                        $(this).val('');
                    }
                }
            }
        });

        $( "#preferedAmounts" ).blur(function() {

            var str = $('#preferedAmounts').val();
            str.substring(0, str.length - 2);
            var arr = str.split(",");
            for (var i = 0; i < arr.length; i++) {

                if (parseInt(arr[i]) < 1) {
                    // arr[i] = arr[i].replace(arr[i], "");
                    $(this).val('');
                    alert("Preferred Amount must be Greater than Zero");
                    $('#preferedAmounts').focus();

                }
                else if (parseInt(arr[i]) > 99) {
                    alert("Preferred Amount must be Less than 100");
                    arr[i] = arr[i].replace(arr[i], "");
                    $(this).val('');
                    $('#preferedAmounts').focus();
                }
            }

        });

        $( "#profileImageSize,#signatureImageSize" ).on('change, input', function(e) {

            var currentInput = $(this).val();
            var fixedInput = currentInput.replace(/[A-Za-z!@#$%^&*()-.,;/''""]/, '');
            var fixedInput = fixedInput.replace(' ,',',');
            fixedInput = fixedInput.replace(/,{2,}/g, ',');
            $(this).val(fixedInput);

        });

        $( "#profileImageSize,#signatureImageSize" ).blur(function() {
            var flag = 0;
            var currentInput = $(this).val();
            var res = currentInput.split(":");
            if (typeof res[1] == 'undefined' || res[1] == '') {
                // alert('Image Height Cant be empty');
                $(this).parent().parent().find('.with-errors').html("Please Enter width : height");
                $(this).focus();
                $(this).css("border","1px solid red");
            }
            else {
                $(this).css("border","1px solid #e4eaec");
                $(this).parent().parent().find('.with-errors').html("");
            }

        });

    });

    $('#provisionFrequency').change(function(){

        if ($(this).val() == 'daily') {
            $('#generateMethod').val('daily');
            $('#generatedMethodDiv').hide('slow');
        } else {
            $('#generateMethod').val('');
            $('#generatedMethodDiv').show('slow');
        }
    });

</script>
</html>
