@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php 
    $designationData = Common::ViewTableOrder('hr_designations',
                            [['is_delete', 0]],
                            ['id', 'name'],
                            ['name', 'ASC']);

    $departmentData = Common::ViewTableOrder('hr_departments',
                            [['is_delete', 0]],
                            ['id', 'dept_name'],
                            ['dept_name', 'ASC']);
?>

<!-- Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(true) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="nav-tabs-horizontal" >

                <ul class="nav nav-tabs nav-tabs-reverse nav-fill d-print-none" role="tablist">
                
                    <li class="nav-item mr-3" ><a class="nav-link active" data-toggle="tab" role="tab" href="#General">General</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Organization">Organization</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Account">Account</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Education">Education</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Traning">Traning</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Experience">Experience</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link " data-toggle="tab" role="tab" href="#Guarantor">Guarantor</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Nominee">Nominee</a></li>
                    <li class="nav-item mr-3" ><a class="nav-link" data-toggle="tab" role="tab" href="#Reference">Reference</a></li>
    

            
            
                </ul>
            </div>
            <div class="tab-content"  >
                {{-- General --}}
                <div id="General" class="tab-pane show active"  role="tabpanel">

                    <div class="row">
                            <div class="col-lg-6">
                
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Employee Code</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_code" name="emp_code"
                                                placeholder="Enter Employee Code" required
                                                data-error="Please enter Employee Code."
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeError', 
                                                    'employee code');">
                                        </div>
                                        <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Employee Name (In English)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_name" name="emp_name"
                                                placeholder="Enter Employee Name (In English)" required
                                                data-error="Please enter Employee name.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Father's Name (In English)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_father_name"
                                                name="emp_father_name" placeholder="Enter Father's Name (In English)">
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Mother's Name (In English)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_mother_name"
                                                name="emp_mother_name" placeholder="Enter Mother's Name (In English)">
                                        </div>
                                    </div>
                                </div>
        
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Gender</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" id="g1" name="emp_gender" value="male" checked="">
                                                <label for="g1">Male &nbsp &nbsp </label>
                                            </div>
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" id="g2" name="emp_gender" value="female">
                                                <label for="g2">Female &nbsp &nbsp </label>
                                            </div>
                                            <div class="radio-custom radio-primary">
                                                <input type="radio" id="g3" name="emp_gender" value="others">
                                                <label for="g3">Others &nbsp &nbsp</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Date of Birth</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round datepicker" id="emp_dob"
                                                name="emp_dob" autocomplete="off" placeholder="DD-MM-YYYY">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Marital Status</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="maritalStatusId"
                                            id="maritalStatusId" required data-error="Please Select Marital Status">
                                            <option value="">Select</option>
                                            @foreach ($maritalStatus as $ms)
                                            <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                                            @endforeach
                                        </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row form-group align-items-center" style="display: none;" id="spouseNameDiv">
                                    <label class="col-lg-4 input-title RequiredStar">{{ "Spouse's Name" }}</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="spouseName"
                                                data-error="Enter Spouse Name" placeholder="Enter Spouse Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                               

                        
                                
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Religion</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" required data-error="Please select Religion"
                                            name="religion" id="religion">
                                                <option value="">Select Religion</option>
                                                @foreach ($religions as $religion)
                                                <option value="{{ $religion->id }}">{{ $religion->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Blood Group</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="blood_group" name="blood_group"
                                                placeholder="Blood Group." required
                                                data-error="Please enter Blood Group.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                       
        
                            </div>
                    
                            <div class="col-lg-6">
        
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title"></label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            
                                        </div>
                                    </div>
                                </div>
        
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Employee Name (In Bangla)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_name_bn" name="emp_name_bn"
                                                placeholder="Enter Employee Name (In Bangla)" required
                                                data-error="Please enter Employee name.">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Father's Name (In Bangla)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_father_name_bn"
                                                name="emp_father_name_bn" placeholder="Enter Father's Name (In Bangla)">
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Mother's Name (In Bangla)</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="emp_mother_name_bn"
                                                name="emp_mother_name_bn" placeholder="Enter Mother's Name (In Bangla)">
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">NID</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="nid"
                                                name="nid" placeholder="Enter NID number">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Birth Certificate</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="birthCertificate"
                                                name="birthCertificate" placeholder="Enter Birth Certificate number">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Passport</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="passport"
                                                name="passport" placeholder="Enter Passport number">
                                        </div>
                                    </div>
                                </div>
        
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">TIN</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" id="tin"
                                                name="tin" placeholder="Enter TIN number">
                                        </div>
                                    </div>
                                </div>
                    
        
                            
                    
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                    <div class="col-lg-7">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="emp_mobile"
                                                id="emp_mobile" placeholder="Mobile Number (01*********)" required
                                                data-error="Please enter mobile number (01*********)" 
                                                minlength="11" maxlength="11"
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeErrorM', 
                                                    'mobile number');">
                                        <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                    </div>
                                </div>
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Phone</label>
                                    <div class="col-lg-7">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="emp_phone"
                                                id="emp_phone" placeholder="Phone Number (01*********)"
                                                data-error="Please enter Phone number (01*********)" >
                                        <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                    </div>
                                </div>
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title">Email</label>
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="email" class="form-control round" id="emp_email" name="emp_email"
                                                placeholder="Enter Email">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                    
                            </div>
                        
                    </div>

                 

                        <!-- Contact Details -->
                        <div class="panel panel-default">
                            <div class="panel-heading p-2">Contact Details</div>
                            <div class="panel-body">
        
                                <!-- Present Address -->
                                <div class="input-title form-group mt-4 border-bottom">Present Address</div>
                                <div class="presentAddressDiv">
                                    <div class="row">
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Division</label>
                                            <div class="input-group">
                                                <select name="preDivisionId" class="form-control clsSelect2 division"
                                                    required>
                                                    <option value="">Select</option>
                                                    @foreach ($divisions as $division)
                                                    <option value="{{ $division->id }}">{{ $division->division_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">District</label>
                                            <div class="input-group">
                                                <select name="preDistrictId" class="form-control clsSelect2 district"
                                                    required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Thana/Upazila</label>
                                            <div class="input-group">
                                                <select name="preUpazilaId" class="form-control clsSelect2 upazila"
                                                    required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Ward/Union</label>
                                            <div class="input-group">
                                                <select name="preUnionId" class="form-control clsSelect2 union" required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                    </div>
        
                                    <div class="row">
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Village/Ward</label>
                                            <div class="input-group">
                                                <select name="preVillageId" class="form-control clsSelect2 village"
                                                    required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Street & Holding No</label>
                                            <div class="input-group">
                                                <textarea class="form-control round streetHolding" name="preStreetHolding"
                                                    rows="2" placeholder="Enter Address" rows="3" required
                                                    data-error="Please Enter Address"></textarea>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                       
                                    </div>
                                </div>
        
                                <!-- Permanent Address -->
                                <div class="input-title form-group mt-2 border-bottom">Permanent Address</div>
        
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="input-group checkbox-custom checkbox-primary">
                                            <input type="checkbox" name="sameAsPreesent" id="sameAsPreesent">
                                            <label class="input-title">Same As Present Address</label>
                                        </div>
                                    </div>
                                </div>
        
                                <div id="sameAsPreesentDiv" style="pointer-events: none;">
                                </div>
        
                                <div class="permanentAddressDiv">
                                    <div class="row">
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Division</label>
                                            <div class="input-group">
                                                <select name="perDivisionId" class="form-control clsSelect2 division">
                                                    <option value="">Select</option>
                                                    @foreach ($divisions as $division)
                                                    <option value="{{ $division->id }}">{{ $division->division_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">District</label>
                                            <div class="input-group">
                                                <select name="perDistrictId" class="form-control clsSelect2 district">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Thana/Upazila</label>
                                            <div class="input-group">
                                                <select name="perUpazilaId" class="form-control clsSelect2 upazila">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Ward/Union</label>
                                            <div class="input-group">
                                                <select name="perUnionId" class="form-control clsSelect2 union">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                        
        
                                    </div>
        
                                    <div class="row">
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Village/Ward</label>
                                            <div class="input-group">
                                                <select name="perVillageId" class="form-control clsSelect2 village">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
        
                                        <div class="col-lg-3 form-group">
                                            <label class="input-title RequiredStar">Street & Holding No</label>
                                            <div class="input-group">
                                                <textarea class="form-control round streetHolding" name="perStreetHolding"
                                                    rows="2" placeholder="Enter Address" rows="3"
                                                    data-error="Please Enter Address"></textarea>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row"> 
                                    <div class="col-lg-6">
                    
                                        <div class="form-row align-items-center">
                                            <label class="col-lg-4 input-title">Photo</label>
                                            <div class="col-lg-7 form-group">
                                                <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                                    <input type="text" class="form-control round" readonly="">
                                                    <div class="input-group-append">
                                                        <span class="btn btn-success btn-file">
                                                            <i class="icon wb-upload" aria-hidden="true"></i>
                                                            <input type="file" id="govt_gurantor_image" name="govt_gurantor_image" onchange="validate_fileupload(this.id);">
                                                        </span>
                                                    </div>
                                                </div>
                                                <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                            </div>
                                        </div>
            
                                        <div class="form-row align-items-center">
                                            <label class="col-lg-4 input-title">Signature</label>
                                            <div class="col-lg-7 form-group">
                                                <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                                    <input type="text" class="form-control round" readonly="">
                                                    <div class="input-group-append">
                                                        <span class="btn btn-success btn-file">
                                                            <i class="icon wb-upload" aria-hidden="true"></i>
                                                            <input type="file" id="govt_gurantor_signature" name="govt_gurantor_signature" onchange="validate_fileupload(this.id);">
                                                        </span>
                                                    </div>
                                                </div>
                                                <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                            </div>
                                        </div>
            
            
                
                                    </div>
                            
                                    <div class="col-lg-6">
                                        
            
                                        <div class="form-row align-items-center">
                                            <label class="col-lg-4 input-title">NID Signature</label>
                                            <div class="col-lg-7 form-group">
                                                <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                                    <input type="text" class="form-control round" readonly="">
                                                    <div class="input-group-append">
                                                        <span class="btn btn-success btn-file">
                                                            <i class="icon wb-upload" aria-hidden="true"></i>
                                                            <input type="file" id="govt_gurantor_signature" name="govt_gurantor_signature" onchange="validate_fileupload(this.id);">
                                                        </span>
                                                    </div>
                                                </div>
                                                <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                            </div>
                                        </div>
            
            
                
                                       
                                    </div>
                                    
                                </div>

                                
        
                            </div>
                        </div>

                        
        
        

                </div>
                {{-- Organization --}}
                <div id="Organization" class="tab-pane show">
                
                </div>
                {{-- Account --}}
                <div id="Account" class="tab-pane show">
                    <div class="input-title form-group mt-4 border-bottom"></div>
                    <div class="row">
                        <div class="col-lg-6">
            
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Bank</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="emp_bank_name" name="emp_bank_name"
                                            placeholder="Enter Bank Name" required
                                            data-error="Please enter Bank Name."
                                           >
                                    </div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                </div>
                            </div>    
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Branch</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="emp_bank_branch" name="emp_bank_branch"
                                            placeholder="Enter Bank Branch Name" required
                                            data-error="Please enter Bank Branch Name."
                                           >
                                    </div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                </div>
                            </div>            
    
                        </div>
                
                        <div class="col-lg-6">
    
                        
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Account Type</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="emp_acc_type" name="emp_acc_type"
                                            placeholder="Enter Account Type" required
                                            data-error="Please enter Account Type.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Account Number</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="emp_acc_number" name="emp_acc_number"
                                            placeholder="Enter Account Number" required
                                            data-error="Please enter Account Number.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                
                
                          
                        </div>
                    
                    </div>

                </div>
                {{-- Education --}}
                <div id="Education" class="tab-pane show">

                    <div>
                        <table class="table w-full table-hover table-bordered table-striped educationTable">
                            <thead>
                                <tr>
                                    <th>Exam Title</th>
                                    <th>Department</th>
                                    <th>Institute Name</th>
                                    <th>Board/University</th>
                                    <th>Result Type</th>
                                    <th>Result</th>
                                    <th>Out Of</th>
                                    <th>Passing Year</th>
                                    <th>Action</th>
                               </tr>
                            </thead>
                            <tbody>
                                <tr>
                                   <td><input type="text" id="edu_exam_title" name="edu_exam_title[]" class="form-control round" placeholder="Title" value="" required ></td>
                                    <td><input type="text" id="edu_department" name="edu_department[]" class="form-control round" placeholder="Department" value="" required ></td>
                                    <td><input type="text" id="edu_institute" name="edu_institute[]" class="form-control round" placeholder="Institute" value="" required ></td>
                                   <td><input type="text" id="edu_board" name="edu_board[]" class="form-control round" placeholder="Board/University" value="" required ></td>
                                    <td><input type="text" id="edu_result_type" name="edu_result_type[]" class="form-control round" placeholder="Result Type" value="" required ></td>
                                     <td><input type="text" id="edu_result" name="edu_result[]" class="form-control round" placeholder="Result" value="" required ></td> 
                                     <td><input type="text" id="edu_outOf" name="edu_outOf[]" class="form-control round" placeholder="Out Of" value="" required ></td> 
                                     <td><input type="text" id="edu_year" name="edu_year[]" class="form-control round" placeholder="Year" value="" required ></td>
                                    <td></td>
                                  
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" id="education_table_row" value="0">
                    </div>

                    <div class="input-title form-group mt-4 border-bottom"></div>

                    <span style="float: right" > <a href="javascript:void(0);"
                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                        onclick="btnAddNewRowEducation();">
                        <i class="icon wb-plus  align-items-center"></i>
                        </a>
                    </span>
                   
              


                </div>
                {{-- Traning --}}
                <div id="Traning" class="tab-pane show">
                    <div id="divTraning">
                        <input type="hidden" id="Traning_row_num" value="0">

                        <div  id="Traning_0">
                            <div class="input-title form-group mt-4 border-bottom"></div>
                        
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Traning Title</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_title" name="traning_title[]"
                                        placeholder="Enter Traning Title" required
                                        data-error="Please enter Traning Title."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Organizar</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_organizar" name="traning_organizar[]"
                                        placeholder="Enter Organizar" required
                                        data-error="Please enter Organizar."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">County</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_county" name="traning_county[]"
                                        placeholder="Enter Traning County" required
                                        data-error="Please enter Traning County."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Address</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control round" id="nominee_address" name="nominee_address[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        rows="2"
                                        >
                                        </textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>


                            </div>

                            <div class="row">

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Topic</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_topic" name="traning_topic[]"
                                        placeholder="Enter Traning Topic" required
                                        data-error="Please enter Traning Topic."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Traning Year</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_year" name="traning_year[]"
                                        placeholder="Enter Traning Year" required
                                        data-error="Please enter Traning Year."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Duration</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="traning_duration" name="traning_duration[]"
                                        placeholder="Enter Traning Duration" required
                                        data-error="Please enter Traning Duration."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                
                            </div>
                        </div>

                    </div>

                    <div class="input-title form-group mt-4 border-bottom"></div>

                    <span style="float: right" > <a href="javascript:void(0);"
                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                        onclick="btnAddNewRowTraning();">
                        <i class="icon wb-plus  align-items-center"></i>
                        </a>
                    </span>
                   
                </div>
                {{-- Experience --}}
                <div id="Experience" class="tab-pane show">

                    <div id="divExperience">
                        <input type="hidden" id="Experience_row_num" value="0">

                        <div id="Experience_0">
                            <div class="input-title form-group mt-4 border-bottom"></div>
                        
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Organization Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_name" name="exp_org_name[]"
                                        placeholder="Enter Organization Name" required
                                        data-error="Please enter Organization Name."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Organization Type</label>
                                    <div class="input-group">
                                        <select type="text" class="form-control round" id="exp_org_type" name="exp_org_type[]"
                                        placeholder="Enter Organization Type" required
                                        data-error="Please enter Organization Type."
                                        >
                                        <option>Select</option>
                                        <option value="Domestic">Domestic</option>
                                        <option value="International">International</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Location</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_location" name="exp_org_location[]"
                                        placeholder="Enter Location" required
                                        data-error="Please enter Location."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Designation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_designation" name="exp_org_designation[]"
                                        placeholder="Enter Designation" required
                                        data-error="Please enter Designation."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            

                            </div>

                            <div class="row">


                                
                            
                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Department/Project Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_department" name="exp_org_department[]"
                                        placeholder="Enter Department" 
                                        data-error="Please enter Department."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Job Responsibility</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_responsibility" name="exp_org_responsibility[]"
                                        placeholder="Enter Responsibility" 
                                        data-error="Please enter Responsibility."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar"> Area of Experience</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_AreaOfEx" name="exp_org_AreaOfEx[]"
                                        placeholder="Enter Area of Experience" 
                                        data-error="Please enter Area of Experience."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Address</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control round" id="exp_org_address" name="exp_org_address[]"
                                        placeholder="Enter Address" required
                                        data-error="Please enter Address.">
                                        </textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Start Date</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_date_start"
                                                    name="exp_org_date_start[]" placeholder="DD-MM-YYYY"
                                                    value="{{  \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                                    >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">End Date</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_date_end"
                                                    name="exp_org_date_end[]" placeholder="DD-MM-YYYY"
                                                    value="{{  \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                                    >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Duration</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="exp_org_duration" name="exp_org_duration[]"
                                        placeholder="Enter Duration" required
                                        data-error="Please enter Duration."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                        
                                
                            </div>
                        </div>
                        <br><br>
                    </div>

                    <div class="input-title form-group mt-4 border-bottom"></div>

                    <span style="float: right" > <a href="javascript:void(0);"
                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                        onclick="btnAddNewRowExperience();">
                        <i class="icon wb-plus  align-items-center"></i>
                        </a>
                    </span>

                </div>
                {{-- Guarantor --}}
                <div id="Guarantor" class="tab-pane show">
                    <div class="row">
                        <div class="col-lg-6">

                            <h4>Government Guarantor</h4>
            
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Name</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="govt_gurantor_name" name="govt_gurantor_name"
                                            placeholder="Enter Govt. Guarantor Name" required
                                            data-error="Please enter Govt. Guarantor Name."
                                            >
                                    </div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                </div>
                            </div>
                
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Designation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="govt_gurantor_designation" name="govt_gurantor_designation"
                                            placeholder="Enter Govt. Guarantor Designation" required
                                            data-error="Please enter Govt. Guarantor Designation.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Occupation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="govt_gurantor_occupation" name="govt_gurantor_occupation"
                                            placeholder="Enter Govt. Guarantor Occupation" required
                                            data-error="Please enter Govt. Guarantor Occupation.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Email</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="email" class="form-control round" id="govt_gurantor_email" name="govt_gurantor_email"
                                            placeholder="Enter Email">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Working Address</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <textarea class="form-control round " name="govt_gurantor_work_add" id="govt_gurantor_work_add"
                                        rows="2" placeholder="Enter Working Address" rows="3"
                                        data-error="Please Enter Address"></textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Permanent Address</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <textarea class="form-control round " name="govt_gurantor_per_add" id="govt_gurantor_per_add"
                                        rows="2" placeholder="Enter Permanent Address" rows="3"
                                        data-error="Please Enter Address"></textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                 
                        

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">NID</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="govt_gurantor_nid"
                                            name="govt_gurantor_nid" placeholder="Enter NID number">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Relation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control " required data-error="Please select Relation"
                                        name="govt_gurantor_relation" id="govt_gurantor_relation">
                                            <option value="">Select Religion</option>
                                        
                                        </select>
                                    </div>
                                </div>
                            </div>
                


                            
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                <div class="col-lg-7">
                                    <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="govt_gurantor_mobile"
                                            id="govt_gurantor_mobile" placeholder="Mobile Number (01*********)" required
                                            data-error="Please enter mobile number (01*********)" 
                                            minlength="11" maxlength="11"
                                            onblur="fnCheckDuplicate(
                                                '{{base64_encode('hr_employees')}}', 
                                                this.name+'&&is_delete', 
                                                this.value+'&&0',
                                                '{{url('/ajaxCheckDuplicate')}}',
                                                this.id,
                                                'txtCodeErrorM', 
                                                'mobile number');">
                                    <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Phone</label>
                                <div class="col-lg-7">
                                    <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="govt_gurantor_phone"
                                            id="govt_gurantor_phone" placeholder="Phone Number (01*********)"
                                            data-error="Please enter Phone number (01*********)" >
                                    <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Photo</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="govt_gurantor_image" name="govt_gurantor_image" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Signature</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="govt_gurantor_signature" name="govt_gurantor_signature" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                </div>
                            </div>



                    
                          
                   
                   
    
                        </div>
                
                        <div class="col-lg-6">
                            <h4>Relative Guarantor</h4>
            
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Name</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="relative_gurantor_name" name="relative_gurantor_name"
                                            placeholder="Enter Relative Guarantor Name" required
                                            data-error="Please enter Relative Guarantor Name."
                                            >
                                    </div>
                                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                                </div>
                            </div>
                
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Designation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="relative_gurantor_designation" name="relative_gurantor_designation"
                                            placeholder="Enter Relative Guarantor Designation" required
                                            data-error="Please enter Relative Guarantor Designation.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Occupation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="relative_gurantor_occupation" name="relative_gurantor_occupation"
                                            placeholder="Enter Relative Guarantor Occupation" required
                                            data-error="Please enter Relative Guarantor Occupation.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Email</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="email" class="form-control round" id="relative_gurantor_email" name="relative_gurantor_email"
                                            placeholder="Enter Email">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Working Address</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <textarea class="form-control round " name="relative_gurantor_work_add" id="relative_gurantor_work_add"
                                        rows="2" placeholder="Enter Working Address" rows="3"
                                        data-error="Please Enter Address"></textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">Permanent Address</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <textarea class="form-control round " name="relative_gurantor_per_add" id="relative_gurantor_per_add"
                                        rows="2" placeholder="Enter Permanent Address" rows="3"
                                        data-error="Please Enter Address"></textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                 
                        

                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title">NID</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="relative_gurantor_nid"
                                            name="relative_gurantor_nid" placeholder="Enter NID number">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Relation</label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <select class="form-control " required data-error="Please select Relation"
                                        name="relative_gurantor_relation" id="relative_gurantor_relation">
                                            <option value="">Select Religion</option>
                                        
                                        </select>
                                    </div>
                                </div>
                            </div>
                


                            
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                <div class="col-lg-7">
                                    <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="relative_gurantor_mobile"
                                            id="relative_gurantor_mobile" placeholder="Mobile Number (01*********)" required
                                            data-error="Please enter mobile number (01*********)" 
                                            minlength="11" maxlength="11"
                                            onblur="fnCheckDuplicate(
                                                '{{base64_encode('hr_employees')}}', 
                                                this.name+'&&is_delete', 
                                                this.value+'&&0',
                                                '{{url('/ajaxCheckDuplicate')}}',
                                                this.id,
                                                'txtCodeErrorM', 
                                                'mobile number');">
                                    <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                </div>
                            </div>
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Phone</label>
                                <div class="col-lg-7">
                                    <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="relative_gurantor_phone"
                                            id="relative_gurantor_phone" placeholder="Phone Number (01*********)"
                                            data-error="Please enter Phone number (01*********)" >
                                    <div class="help-block with-errors is-invalid" id="txtCodeErrorM"></div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Photo</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="relative_gurantor_image" name="relative_gurantor_image" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title">Signature</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="relative_gurantor_signature" name="relative_gurantor_signature" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                                </div>
                            </div>



    
                           
                        </div>
                    
                </div>


                </div>
                {{-- Nominee --}}
                <div id="Nominee" class="tab-pane show">

                       <!-- name 1 -->

                        <div>
                            <div class="input-title form-group mt-4 border-bottom"></div>
                        
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_name" name="nominee_name[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Relation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_relation" name="nominee_relation[]"
                                        placeholder="Enter Nominee Relation" required
                                        data-error="Please enter Nominee Relation."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Percentage</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_percentage" name="nominee_percentage[]"
                                        placeholder="Enter Nominee Percentage" required
                                        data-error="Please enter Nominee Percentage."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>



                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">NID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_nid" name="nominee_nid[]"
                                        placeholder="Enter Nominee NID" required
                                        data-error="Please enter Nominee NID."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Address</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control round" id="nominee_address" name="nominee_address[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        rows="2"
                                        >
                                        </textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>


                            
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                    <div class="input-group">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="nominee_mobile[]"
                                                id="nominee_mobile" placeholder="Mobile Number (01*********)" required
                                                data-error="Please enter mobile number (01*********)" 
                                                minlength="11" maxlength="11"
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeErrorM', 
                                                    'mobile number');">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            


                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title">Photo</label>

                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="nominee_photo" name="nominee_photo[]" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>


                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title">Signature</label>

                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="nominee_signature" name="nominee_signature[]" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                
                            </div>
                        </div>
                        
                       <!-- name 2 -->

                        <div>
                            <div class="input-title form-group mt-4 border-bottom"></div>
                        
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_name1" name="nominee_name[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Relation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_relation1" name="nominee_relation[]"
                                        placeholder="Enter Nominee Relation" required
                                        data-error="Please enter Nominee Relation."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Percentage</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_percentage1" name="nominee_percentage[]"
                                        placeholder="Enter Nominee Percentage" required
                                        data-error="Please enter Nominee Percentage."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>



                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">NID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="nominee_nid1" name="nominee_nid[]"
                                        placeholder="Enter Nominee NID" required
                                        data-error="Please enter Nominee NID."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Address</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control round" id="nominee_address1" name="nominee_address[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        rows="2"
                                        >
                                        </textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>


                            
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                    <div class="input-group">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="nominee_mobile[]"
                                                id="nominee_mobile1" placeholder="Mobile Number (01*********)" required
                                                data-error="Please enter mobile number (01*********)" 
                                                minlength="11" maxlength="11"
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeErrorM', 
                                                    'mobile number');">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            


                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title">Photo</label>

                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="nominee_photo1" name="nominee_photo[]" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>


                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title">Signature</label>

                                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                                        <input type="text" class="form-control round" readonly="">
                                        <div class="input-group-append">
                                            <span class="btn btn-success btn-file">
                                                <i class="icon wb-upload" aria-hidden="true"></i>
                                                <input type="file" id="nominee_signature1" name="nominee_signature[]" onchange="validate_fileupload(this.id);">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                
                            </div>
                        </div>

                       
                    

                </div>
                {{-- Reference --}}
                <div id="Reference" class="tab-pane show">

                    <div id="divReference">
                        <input type="hidden" id="reference_row_num" value="0">
                        <div id="reference_0">
                            <div class="input-title form-group mt-4 border-bottom"></div>
                        
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="ref_name" name="ref_name[]"
                                        placeholder="Enter Reference Name" required
                                        data-error="Please enter Reference Name."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Designation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="ref_designation" name="ref_designation[]"
                                        placeholder="Enter Designation Name" required
                                        data-error="Please enter Designation Name.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
    
    
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Relation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="ref_relation" name="ref_relation[]"
                                        placeholder="Enter Relation " required
                                        data-error="Please enter Relation ."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
    
    
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">NID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="ref_nid" name="ref_nid[]"
                                        placeholder="Enter NID" required
                                        data-error="Please enter NID."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
                            </div>
    
                            <div class="row">
    
    
                                
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                    <div class="input-group">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="ref_mobile[]"
                                                id="ref_mobile" placeholder="Mobile Number (01*********)" required
                                                data-error="Please enter mobile number (01*********)" 
                                                minlength="11" maxlength="11"
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeErrorM', 
                                                    'mobile number');">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Phone</label>
                                    <div class="input-group">
                                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="ref_phone[]"
                                                id="ref_phone" placeholder="Phone Number (01*********)" required
                                                data-error="Please enter Phone number (01*********)" 
                                                minlength="11" maxlength="11"
                                                onblur="fnCheckDuplicate(
                                                    '{{base64_encode('hr_employees')}}', 
                                                    this.name+'&&is_delete', 
                                                    this.value+'&&0',
                                                    '{{url('/ajaxCheckDuplicate')}}',
                                                    this.id,
                                                    'txtCodeErrorM', 
                                                    'mobile number');">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
                            
    
    
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Email</label>
                                    <div class="input-group">
                                        <input type="email" class="form-control round" id="ref_email" name="ref_email[]"
                                        placeholder="Enter Email" required
                                        data-error="Please enter Email."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
    
    
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Occupation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" id="ref_occupation" name="ref_occupation[]"
                                        placeholder="Enter Occupation " required
                                        data-error="Please enter Occupation ."
                                        >
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
                                
                                <div class="col-lg-3 form-group">
                                    <label class="col-lg-4 input-title RequiredStar">Working Address</label>
                                    <div class="input-group">
                                        <textarea type="text" class="form-control round" id="ref_working_add" name="ref_working_add[]"
                                        placeholder="Enter Nominee Name" required
                                        data-error="Please enter Nominee Name."
                                        rows="2"
                                        >
                                        </textarea>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
    
    
                            
    
                          
                                
                            </div>
                        </div>
    

                    </div>

                    <div class="input-title form-group mt-4 border-bottom"></div>

                    <span style="float: right" > <a href="javascript:void(0);"
                        class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"
                        onclick="btnAddNewRowReference();">
                        <i class="icon wb-plus  align-items-center"></i>
                        </a>
                    </span>
                   

             
                </div>

            </div>

        </div>
    </div>


    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="btnSubmit">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>



<!-- End Page -->

<script>
    $( document ).ready(function() {
        $('form').submit(function (event) {
            $(this).find(':submit').attr('disabled', 'disabled');
        });

           /* show/hide spouse name base on marital Status */
        $('#maritalStatusId').change(function (e) {
            if ($(this).val() == 1 || $(this).val() == 4) {
                $('#spouseNameDiv').show('slow');
            } else {
                $('#spouseNameDiv').hide('slow');
            }
        });
        /* end show/hide spouse name base on marital Status */


              /* address */
               // $('.division,.district,.upazila,.union').change(function (e) {
            $('.division,.district,.upazila,.union').on("select2:select", function (e) {
            var source = $(this);
            var url = '';

            if ($(this).hasClass('division')) {
                var target = source.parents().eq(2).find('.district');
                var url = 'getDistricts';
                var data = {
                    divisionId: source.val()
                };

                source.parents().eq(2).find('.district,.upazila,.union').find('option:gt(0)').remove();
                source.parents().eq(3).find('.village').find('option:gt(0)').remove();
            } else if ($(this).hasClass('district')) {
                var target = source.parents().eq(2).find('.upazila');
                var url = 'getUpazilas';
                var data = {
                    districtId: source.val()
                };

                source.parents().eq(2).find('.upazila,.union').find('option:gt(0)').remove();
                source.parents().eq(3).find('.village').find('option:gt(0)').remove();
            } else if ($(this).hasClass('upazila')) {
                var target = source.parents().eq(2).find('.union');
                var url = 'getUnions';
                var data = {
                    upazilaId: source.val()
                };

                source.parents().eq(2).find('.union').find('option:gt(0)').remove();
                source.parents().eq(3).find('.village').find('option:gt(0)').remove();
            } else if ($(this).hasClass('union')) {
                var target = source.parents().eq(3).find('.village');
                var url = 'getVillages';
                var data = {
                    unionId: source.val()
                };

                target.find('option:gt(0)').remove();
            }

            if (source.val() == '' || url == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../" + url,
                data: data,
                dataType: "json",
                success: function (options) {
                    $.each(options, function (index, value) {
                        target.append("<option value=" + index + ">" + value +
                            "</option>");
                    });
                },
                error: function () {
                    alert('error!');
                }
            });

            $('#sameAsPreesent').trigger('change');

        });

        $('.village').on("select2:select", function (e) {
            $('#sameAsPreesent').trigger('change');
        });

        $('.streetHolding').on('input', function () {
            $('#sameAsPreesent').trigger('change');
        });
        /* end address */

        /* when click same as present address */
        $('#sameAsPreesent').change(function (e) {
            $('#sameAsPreesentDiv').empty();

            if ($(this).is(":checked")) {
                $(".permanentAddressDiv").hide();

                var presentAddressDiv = $('.presentAddressDiv').clone();
                presentAddressDiv.find('.familyContactDiv').remove();
                presentAddressDiv.removeClass('presentAddressDiv');
                presentAddressDiv.find('*').removeAttr('name');
                presentAddressDiv.find('*').removeAttr(
                'required'); // remove reqired attribute for validation
                $('#sameAsPreesentDiv').append(presentAddressDiv);
                $('#sameAsPreesentDiv').show();
            } else {
                $(".permanentAddressDiv").show();

                $('#sameAsPreesentDiv').hide();
            }
        });
        /* end when click same as present address */


        if ($('.identification').is(':checked')){
            var idTxt = $('.identification:checked').val();

            // Natinal ID Validation
            if(idTxt === 'nid'){
                $(this).attr("placeholder", "Enter NID No");
                $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                $(".identificationInput").on("input", function(event){
                    var nidNo = $(this).val();
                    if (nidNo.length > 0) {
                        if (nidNo.length != 13) {
                            if (nidNo.length != 17) {
                                $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                                $('#emp_national_id').css("border-color","red");
                                $('#btnSubmit').attr("disabled","disabled");
                            }else if ( nidNo.length == 17 ){
                                $("#errMsgNID").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgNID', 'NID', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }
                                
                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");                                      
                                //             $('#errMsgNID').html('Please enter unique NID');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             $('#btnSubmit').removeAttr("disabled");
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }
                        } else if ( nidNo.length == 13 ){
                            $("#errMsgNID").html('');
                            $('#emp_national_id').css('border-color','#e4eaec');

                            // // // Duplicate Check
                            var query = $(this).val();
                            var forWhich = $(this).attr("name");
                            var tableName = btoa('hr_employees');

                            var columnName = $(this).attr("name")+'&&is_delete';
                            var columnValue = $(this).val()+'&&0';
                            var url_text = "{{url('/ajaxCheckDuplicate')}}";
                            var fieldID = $(this).attr("id");
                            var updateID = null;

                            fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                            'errMsgNID', 'NID', updateID);

                            if($('#'+ fieldID).val() !== ''){
                                $('#btnSubmit').removeAttr("disabled");
                                $(this).css('border-color','#e4eaec');
                            }
                            
                            // $.ajax({
                            //     type: "get",
                            //     url: "{{route('ajaxCheckDuplicate')}}",
                            //     data: {query: query, tableName: tableName, forWhich: forWhich},
                            //     dataType: "json",
                            //     success: function (data) {
                            //         if (data.exists) {
                            //             $('#errMsgNID').html('Please enter unique NID');
                            //             $('#emp_national_id').css("border-color","red");
                            //         }
                            //         else {
                            //             $('#btnSubmit').removeAttr("disabled");
                            //             $(this).css('border-color','#e4eaec');  
                            //         }
                            //     },
                            // });
                        }
                    }
                    else {
                        $("#errMsgNID").html('');
                        $('#emp_national_id').css("border-color","#e4eaec");
                        $('#btnSubmit').removeAttr('disabled');
                    }
                });
            }
        }

        $(".identification").click(function() {
            $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").html('');
            $('#emp_national_id').val('');
            var selIdTxt = $(this).val();

            $( '.identificationInput' ).each(function() {

                if(selIdTxt === 'nid'){
                    $(this).attr("placeholder", "Enter NID No");
                    $("#errMsg,#errMsgSC,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgNID');
                    $(".identificationInput").on("input", function(event){
                        var nidNo = $(this).val();
                        if (nidNo.length > 0) {
                            if (nidNo.length != 13) {
                                if (nidNo.length != 17) {
                                    $("#errMsgNID").html("Invalid NID! NID must be of 13 or 17 Digits").show();
                                    $('#emp_national_id').css("border-color","red");
                                    $('#btnSubmit').attr("disabled","disabled");
                                }else if ( nidNo.length == 17 ){
                                    $("#errMsgNID").html('');
                                    $('#emp_national_id').css('border-color','#e4eaec');

                                    // // // Duplicate Check
                                    var query = $(this).val();
                                    var forWhich = $(this).attr("name");
                                    var tableName = btoa('hr_employees');

                                    var columnName = $(this).attr("name")+'&&is_delete';
                                    var columnValue = $(this).val()+'&&0';
                                    var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                    var fieldID = $(this).attr("id");
                                    var updateID = null;

                                    fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                    'errMsgNID', 'NID', updateID);

                                    if($('#'+ fieldID).val() !== ''){
                                        $('#btnSubmit').removeAttr("disabled");
                                        $(this).css('border-color','#e4eaec');
                                    }

                                    // $.ajax({
                                    //     type: "get",
                                    //     url: "{{route('ajaxCheckDuplicate')}}",
                                    //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                    //     dataType: "json",
                                    //     success: function (data) {
                                    //         if (data.exists) {
                                    //             $('#btnSubmit').attr("disabled","disabled");                                      
                                    //             $('#errMsgNID').html('Please enter unique NID');
                                    //             $('#emp_national_id').css("border-color","red");
                                    //         }
                                    //         else {
                                    //             $('#btnSubmit').removeAttr("disabled");
                                    //             $(this).css('border-color','#e4eaec');
                                    //         }
                                    //     },
                                    // });
                                }
                            } else if ( nidNo.length == 13 ){
                                $("#errMsgNID").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgNID', 'NID', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }

                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#errMsgNID').html('Please enter unique NID');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             var numberOfEmptyFields = $('form').find('input[required],select[required]')
                                //                                         .filter(function () {
                                //                                             return $(this).val() === "";
                                //                                         }).length;
                                //             if (numberOfEmptyFields == 0) {
                                //                 $('#btnSubmit').removeAttr("disabled");
                                //             }

                                //             $(this).css('border-color','#e4eaec');  
                                //         }
                                //     },
                                // });
                            }
                        }
                        else {
                            $("#errMsgNID").html('');
                            $('#emp_national_id').css("border-color","#e4eaec");
                            $('#btnSubmit').removeAttr('disabled');
                        }
                    });
                }
                else if(selIdTxt === 'smartCard'){
                    $(this).attr("placeholder", "Enter Smart Card No");
                    $("#errMsg,#errMsgNID,#errMsgPP,#errMsgDL,#errMsgBR").prop('id', 'errMsgSC');
                    $(".identificationInput").on("input", function(event){
                        var cardNo = $(this).val();
                        if (cardNo.length > 0) {
                            if(cardNo.length != 10) {
                                $("#errMsgSC").html("Not a valid 10-digit Smart Card Number").show();
                                $(this).css('border-color','red');
                                $('#btnSubmit').attr("disabled","disabled");
                            }
                            else if ( cardNo.length == 10 ){
                                $("#errMsgSC").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgSC', 'smart card no.', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }

                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");                                      
                                //             $('#errMsgSC').html('Please enter unique Smart Card ID');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             $('#btnSubmit').removeAttr("disabled");
                                            
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }
                        }
                        else {
                            $("#errMsgSC").html('');
                            $('#emp_national_id').css("border-color","#e4eaec");
                            $('#btnSubmit').removeAttr('disabled');
                        }
                        
                    });
                }
                else if(selIdTxt === 'passport'){
                    $(this).attr("placeholder", "Enter passport No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgDL,#errMsgBR").prop('id', 'errMsgPP');
                    $(".identificationInput").on("input", function(event){
                        var passportNo = $(this).val();
                        if(passportNo.length > 0) {
                            if (passportNo.length != 9) {
                                $("#errMsgPP").html("Not a valid 9-digit Passport Number").show();
                                $(this).css('border-color','red');
                                $('#btnSubmit').attr("disabled","disabled");
                            } 
                            else if ( passportNo.length == 9 ){
                                $("#errMsgPP").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgPP', 'passport no.', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }

                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");                                      
                                //             $('#errMsgPP').html('Please enter unique Passport No');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             $('#btnSubmit').removeAttr("disabled");
                                            
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }   
                        }
                        else {
                            $("#errMsgPP").html('');
                            $('#emp_national_id').css("border-color","#e4eaec");
                            $('#btnSubmit').removeAttr('disabled');
                        }
                    });
                }
                else if(selIdTxt === 'drivingLicense'){
                    $(this).attr("placeholder", "Enter Driving License No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgBR").prop('id', 'errMsgDL');
                    $(this).removeClass('textNumber');
                    $(".identificationInput").on("input", function(event){
                        var licenceNo = $(this).val();
                        if(licenceNo.length > 0) {
                            if (licenceNo.length != 15) {
                                $("#errMsgDL").html("Not a valid 15-digit Driving Licence Number").show();
                                $(this).css('border-color','red');
                                $('#btnSubmit').attr("disabled","disabled");
                            }
                            else if ( licenceNo.length == 15 ){
                                $("#errMsgDL").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgDL', 'driving license.', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }

                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");                                      
                                //             $('#errMsgDL').html('Please enter unique Driving Licence No');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             $('#btnSubmit').removeAttr("disabled");
                                            
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }   
                        }
                        else {
                            $("#errMsgDL").html('');
                            $('#emp_national_id').css("border-color","#e4eaec");
                            $('#btnSubmit').removeAttr('disabled');
                        }
                    });
                }
                else if(selIdTxt === 'birthCertificate'){
                    $(this).attr("placeholder", "Enter Birth Registration No");
                    $("#errMsg,#errMsgNID,#errMsgSC,#errMsgPP,#errMsgDL").prop('id', 'errMsgBR');
                    $(".identificationInput").on("input", function(event){
                        var brNo = $(this).val();
                        if(brNo.length > 0) {
                            if (brNo.length != 17) {
                                $("#errMsgBR").html("Not a valid 17-digit Birth Registration Number").show();
                                $(this).css('border-color','red');
                                $('#btnSubmit').attr("disabled","disabled");
                            } 
                            else if ( brNo.length == 17 ){
                                $("#errMsgBR").html('');
                                $('#emp_national_id').css('border-color','#e4eaec');

                                // // // Duplicate Check
                                var query = $(this).val();
                                var forWhich = $(this).attr("name");
                                var tableName = btoa('hr_employees');

                                var columnName = $(this).attr("name")+'&&is_delete';
                                var columnValue = $(this).val()+'&&0';
                                var url_text = "{{url('/ajaxCheckDuplicate')}}";
                                var fieldID = $(this).attr("id");
                                var updateID = null;

                                fnCheckDuplicate(tableName, columnName, columnValue, url_text, fieldID,
                                'errMsgBR', 'birth registration no.', updateID);

                                if($('#'+ fieldID).val() !== ''){
                                    $('#btnSubmit').removeAttr("disabled");
                                    $(this).css('border-color','#e4eaec');
                                }

                                // $.ajax({
                                //     type: "get",
                                //     url: "{{route('ajaxCheckDuplicate')}}",
                                //     data: {query: query, tableName: tableName, forWhich: forWhich},
                                //     dataType: "json",
                                //     success: function (data) {
                                //         if (data.exists) {
                                //             $('#btnSubmit').attr("disabled","disabled");                                      
                                //             $('#errMsgBR').html('Please enter unique Birth Registration No');
                                //             $('#emp_national_id').css("border-color","red");
                                //         }
                                //         else {
                                //             $('#btnSubmit').removeAttr("disabled");
                                            
                                //             $(this).css('border-color','#e4eaec');
                                //         }
                                //     },
                                // });
                            }   
                        }
                        else {
                            $("#errMsgBR").html('');
                            $('#emp_national_id').css("border-color","#e4eaec");
                            $('#btnSubmit').removeAttr('disabled');
                        }
                    });
                }  
            });
        });

    });



function btnAddNewRowEducation() {
    
    
    
            var TotalRowCount = $('#education_table_row').val();
            TotalRowCount++;
            $('#education_table_row').val(TotalRowCount);
    
            var html = '<tr>';

            html += '<td><input type="text" id="edu_exam_title_'+ TotalRowCount +'" name="edu_exam_title[]" class="form-control round" placeholder="Title" value="" required ></td>>';
            html += ' <td><input type="text" id="edu_department_'+ TotalRowCount +'" name="edu_department[]" class="form-control round" placeholder="Department" value="" required ></td>';
            html += ' <td><input type="text" id="edu_institute_'+ TotalRowCount +'" name="edu_institute[]" class="form-control round" placeholder="Institute" value="" required ></td>';
            html += '<td><input type="text" id="edu_board_'+ TotalRowCount +'" name="edu_board[]" class="form-control round" placeholder="Board/University" value="" required ></td>';
            html += ' <td><input type="text" id="edu_result_type_'+ TotalRowCount +'" name="edu_result_type[]" class="form-control round" placeholder="Result Type" value="" required ></td>';
             html += ' <td><input type="text" id="edu_result_'+ TotalRowCount +'" name="edu_result[]" class="form-control round" placeholder="Result" value="" required ></td>'; 
             html += ' <td><input type="text" id="edu_outOf_'+ TotalRowCount +'" name="edu_outOf[]" class="form-control round" placeholder="Out Of" value="" required ></td>'; 
             html += ' <td><input type="text" id="edu_year_'+ TotalRowCount +'" name="edu_year[]" class="form-control round" placeholder="Year" value="" required ></td>';
             html += '<td width="4%">' +
                '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRowEducation(this);">' +
                ' <i class="icon fa fa-times align-items-center"></i>' +
                '</a>' +
                '</td>';
            html += '</tr>';

            $('.educationTable tbody').after(html);
}

function btnRemoveRowEducation(RemoveID) {

    $(RemoveID).closest('tr').remove();
    fnTotalQuantity();
    fnTotalAmount();
}

function btnAddNewRowReference() {
    
    
    
    var TotalRowCount = $('#reference_row_num').val();
    TotalRowCount++;
    $('#reference_row_num').val(TotalRowCount);

    var html = '<div id="reference_'+TotalRowCount+'">';
        
        html += '<div class="input-title form-group mt-4 border-bottom"></div>';
     

        html += '<div class="row">';
        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Name</label><div class="input-group">';
        html += ' <input type="text" class="form-control round" id="ref_name" name="ref_name[]" placeholder="Enter Reference Name" required data-error="Please enter Reference Name.">';
        html += ' </div><div class="help-block with-errors is-invalid"></div></div>';


        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Designation</label><div class="input-group">';
        html += '<input type="text" class="form-control round" id="ref_designation" name="ref_designation[]" placeholder="Enter Designation Name" required data-error="Please enter Designation Name.">';

        html += '</div><div class="help-block with-errors is-invalid"></div>  </div>';

        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Relation</label>  <div class="input-group">';

        html += ' <input type="text" class="form-control round" id="ref_relation" name="ref_relation[]" placeholder="Enter Relation " required data-error="Please enter Relation .">';

        html += '</div>  <div class="help-block with-errors is-invalid"></div> </div>';

        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">NID</label><div class="input-group">';

        html += ' <input type="text" class="form-control round" id="ref_nid" name="ref_nid[]" placeholder="Enter NID" required data-error="Please enter NID." >';

        html += '</div><div class="help-block with-errors is-invalid"></div></div>';

        html += ' </div> <div class="row">';
        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Mobile</label> <div class="input-group">';
        html += '<input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="ref_mobile[]" id="ref_mobile" placeholder="Mobile Number (01*********)" required data-error="Please enter mobile number (01*********)" ';
                                               
                                               
        html += ' minlength="11" maxlength="11" onblur="fnCheckDuplicate("{{base64_encode("hr_employees")}}",  this.name+"&&is_delete", this.value+"&&0","{{url("/ajaxCheckDuplicate")}}",this.id, "txtCodeErrorM",  "mobile number");">';
        html += '</div><div class="help-block with-errors is-invalid"></div></div>';

        html += ' <div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Phone</label> <div class="input-group">';
        html += '<input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber" name="ref_phone[]" id="ref_phone" placeholder="Mobile Number (01*********)" required data-error="Please enter mobile number (01*********)" ';
                                       
        html += ' minlength="11" maxlength="11" onblur="fnCheckDuplicate("{{base64_encode("hr_employees")}}",  this.name+"&&is_delete", this.value+"&&0","{{url("/ajaxCheckDuplicate")}}",this.id, "txtCodeErrorM",  "mobile number");">';
                                              
        html += '</div><div class="help-block with-errors is-invalid"></div> </div>';
        html += '<div class="col-lg-3 form-group"> <label class="col-lg-4 input-title RequiredStar">Email</label><div class="input-group">';
        html += ' <input type="email" class="form-control round" id="ref_email" name="ref_email[]" placeholder="Enter Email" required data-error="Please enter Email.">';
        html += '</div> <div class="help-block with-errors is-invalid"></div></div>';
        html += '<div class="col-lg-3 form-group"><label class="col-lg-4 input-title RequiredStar">Occupation</label><div class="input-group">';
        html += '<input type="text" class="form-control round" id="ref_occupation" name="ref_occupation[]" placeholder="Enter Occupation " required  data-error="Please enter Occupation .">';
        html += ' </div> <div class="help-block with-errors is-invalid"></div></div>';



        html += ' </div> <div class="row">';



        html += ' <div class="col-lg-3 form-group">   <label class="col-lg-4 input-title RequiredStar">Working Address</label> <div class="input-group">';
        html += ' <textarea type="text" class="form-control round" id="ref_working_add" name="ref_working_add[]" placeholder="Enter Nominee Name" required data-error="Please enter Nominee Name." rows="2" ></textarea>';
        html += '  </div> <div class="help-block with-errors is-invalid"></div> </div>';

    
        
                        

        html += ' </div> ';
        html +='<span style="float: right" > <a href="javascript:void(0);" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"';
        html += 'onclick="btnRemoveRowReference('+TotalRowCount+');"><i class="icon fa fa-times align-items-center"></i></a> </span></div><br><br>';

  
                                      
                                  
    
          

    $('#divReference').append(html);
}

function btnRemoveRowReference(RemoveID) {

    $('#reference_'+RemoveID).remove();
}

function btnAddNewRowTraning() {
    
    var TotalRowCount = $('#Traning_row_num').val();
    TotalRowCount++;
    $('#Traning_row_num').val(TotalRowCount);

    var html = '<div id="Traning_'+TotalRowCount+'">';



        html += ' <div>'
                        +'<div class="input-title form-group mt-4 border-bottom"></div>'
                        +'<div class="row">'
                            +'<div class="col-lg-3 form-group">'
                            +'<label class="col-lg-4 input-title RequiredStar">Traning Title</label>'
                                   +'<div class="input-group">'+
                                   '<input type="text" class="form-control round" id="traning_title" name="traning_title[]"'+
                                        ' placeholder="Enter Traning Title" required'+
                                        'data-error="Please enter Traning Title."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+
                                '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">Organizar</label>'+
                                    '<div class="input-group">'+
                                        '<input type="text" class="form-control round" id="traning_organizar" name="traning_organizar[]"'+
                                        'placeholder="Enter Organizar" required'+
                                        'data-error="Please enter Organizar."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+

                                '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">County</label>'+
                                    '<div class="input-group">'+
                                        '<input type="text" class="form-control round" id="traning_county" name="traning_county[]"'+
                                        'placeholder="Enter Traning County" required'+
                                        'data-error="Please enter Traning County."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+

                            
                               '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">Address</label>'+
                                    '<div class="input-group">'+
                                        '<textarea type="text" class="form-control round" id="nominee_address" name="nominee_address[]"'+
                                        'placeholder="Enter Nominee Name" required'+
                                        'data-error="Please enter Nominee Name."'+
                                        'rows="2"'+
                                        '>'+
                                        '</textarea>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+


                            '</div>'+

                            '<div class="row">'+

                                '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">Topic</label>'+
                                    '<div class="input-group">'+
                                        '<input type="text" class="form-control round" id="traning_topic" name="traning_topic[]"'+
                                        'placeholder="Enter Traning Topic" required'+
                                       ' data-error="Please enter Traning Topic."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                               ' </div>'+

                                '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">Traning Year</label>'+
                                    '<div class="input-group">'+
                                        '<input type="text" class="form-control round" id="traning_year" name="traning_year[]"'+
                                        'placeholder="Enter Traning Year" required'+
                                        'data-error="Please enter Traning Year."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+

                                '<div class="col-lg-3 form-group">'+
                                    '<label class="col-lg-4 input-title RequiredStar">Duration</label>'+
                                    '<div class="input-group">'+
                                        '<input type="text" class="form-control round" id="traning_duration" name="traning_duration[]"'+
                                        'placeholder="Enter Traning Duration" required'+
                                        'data-error="Please enter Traning Duration."'+
                                        '>'+
                                    '</div>'+
                                    '<div class="help-block with-errors is-invalid"></div>'+
                                '</div>'+
                                
                            '</div>';

        html +='<span style="float: right" > <a href="javascript:void(0);" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"';
        html += 'onclick="btnRemoveRowTraning('+TotalRowCount+');"><i class="icon fa fa-times align-items-center"></i></a> </span></div><br><br>';




                        
        
                             
    
          

    $('#divTraning').append(html);
}

function btnRemoveRowTraning(RemoveID) {

$('#Traning_'+RemoveID).remove();
}

function btnAddNewRowExperience() {


    
    
    var TotalRowCount = $('#Experience_row_num').val();
    TotalRowCount++;
    $('#Experience_row_num').val(TotalRowCount);

    var html = '<div id="Experience_'+TotalRowCount+'">';



        html += '<div id="Experience_0">';
        html += `<div class="input-title form-group mt-4 border-bottom"></div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Organization Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_name" name="exp_org_name[]"
                                placeholder="Enter Organization Name" required
                                data-error="Please enter Organization Name."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Organization Type</label>
                            <div class="input-group">
                                <select type="text" class="form-control round" id="exp_org_type" name="exp_org_type[]"
                                placeholder="Enter Organization Type" required
                                data-error="Please enter Organization Type."
                                >
                                <option>Select</option>
                                <option value="Domestic">Domestic</option>
                                <option value="International">International</option>
                                </select>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Location</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_location" name="exp_org_location[]"
                                placeholder="Enter Location" required
                                data-error="Please enter Location."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Designation</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_designation" name="exp_org_designation[]"
                                placeholder="Enter Designation" required
                                data-error="Please enter Designation."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>



                    </div>

                    <div class="row">


                        

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Department/Project Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_department" name="exp_org_department[]"
                                placeholder="Enter Department" 
                                data-error="Please enter Department."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Job Responsibility</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_responsibility" name="exp_org_responsibility[]"
                                placeholder="Enter Responsibility" 
                                data-error="Please enter Responsibility."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar"> Area of Experience</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_AreaOfEx" name="exp_org_AreaOfEx[]"
                                placeholder="Enter Area of Experience" 
                                data-error="Please enter Area of Experience."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Address</label>
                            <div class="input-group">
                                <textarea type="text" class="form-control round" id="exp_org_address" name="exp_org_address[]"
                                placeholder="Enter Address" required
                                data-error="Please enter Address.">
                                </textarea>
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Start Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_date_start"
                                            name="exp_org_date_start[]" placeholder="DD-MM-YYYY"
                                            value="{{  \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                            >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">End Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_date_end"
                                            name="exp_org_date_end[]" placeholder="DD-MM-YYYY"
                                            value="{{  \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                            >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label class="input-title RequiredStar">Duration</label>
                            <div class="input-group">
                                <input type="text" class="form-control round" id="exp_org_duration" name="exp_org_duration[]"
                                placeholder="Enter Duration" required
                                data-error="Please enter Duration."
                                >
                            </div>
                            <div class="help-block with-errors is-invalid"></div>
                        </div>


                        
                    </div>`;

              

        
        
        
        html +='<span style="float: right" > <a href="javascript:void(0);" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center"';
        html += 'onclick="btnRemoveRowExperience('+TotalRowCount+');"><i class="icon fa fa-times align-items-center"></i></a> </span></div><br><br>';





                        
        // console.log('sss');
                             
    
          

    $('#divExperience').append(html);
}

function btnRemoveRowExperience(RemoveID) {

    $('#Experience_'+RemoveID).remove();
}
</script>
@endsection
