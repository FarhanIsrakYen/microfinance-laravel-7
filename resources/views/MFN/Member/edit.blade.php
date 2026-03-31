@extends('Layouts.erp_master')
@section('content')
<style>
    .tooltip-icon{
        padding: 5px 10px;
        border-radius: 50%;
        background: #ccc;
        color: black;
        cursor: pointer;
    }
    .tooltip-text{
        background: rgba(0,0,0,0.3);
        width: 200px;
        color: white;
        padding: 10px;
        border-radius: 10px;
        position: absolute;
        z-index: 1;
        display: none;
    }
</style>
@php
    $GLOBALS['requiredFields'] = $requiredFields;
    
    function isRequired($fieldName)
    {
        if (isset($GLOBALS['requiredFields'][$fieldName])) {
            if ($GLOBALS['requiredFields'][$fieldName] == 'required') {
                echo 'required';
            }
        }
    }
    function isRequiredClass($fieldName)
    {
        if (isset($GLOBALS['requiredFields'][$fieldName])) {
            if ($GLOBALS['requiredFields'][$fieldName] == 'required') {
                echo 'RequiredStar';
            }
        }
    }
@endphp

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off" id="memberForm">
    @csrf
    <div class="nav-tabs-horizontal f-inline-flex" id="tabs">
        <div class="row">
            <div class='m-4 ml-auto'>
                <label> Edit Basic Info Only &nbsp; &nbsp;</label>
                <input type="checkbox" class='edit-basic' data-plugin="switchery" id="basic_info_button"  name="basicInfoEdit" onchange="basicInfoButtonToggle(this)">
                <span class="tooltip-icon"> ? </span>
                <div class="tooltip-text"> Only Name, Surname, Father's Name, Mother's Name, Son's Name and Spouse Name can be edited</div>
                
                {{-- <input type="checkbox"  data-plugin="switchery" checked /> --}}
            </div>
            
        </div>
        <ul class="nav nav-tabs nav-tabs-reverse  text-center" role="tablist">
            <li class="nav-item mr-2 flex-fill" role="presentation" id="initialList">
                <a class="nav-link nav-tabs btn btn-bg-color active" id="initialTab" data-toggle="tab" href="#details"
                    role="tab">Member Details
                </a>
            </li>
            <li class="nav-item flex-fill" role="presentation" id="photoList">
                <a class="nav-link nav-tabs btn btn-bg-color" id="photoTab" data-toggle="tab" href="#photo"
                    role="tab">Photo
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content pt-20">
            <!--  tab pane 1 (Details) -->
            <div class="tab-pane active show" id="details" role="tabpanel">

                <div class="row">
                    <div class="col-lg-12">

                        {{-- Member's Basic Information --}}
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Member</div>
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Samity</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" readonly
                                                value="{{ $samity->samityCode . ' - ' .$samity->name }}"
                                                placeholder="Samity" >
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('name')}}">Member Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="name" 
                                                placeholder="Enter Member Name" {{ isRequired('name') }}
                                                value="{{ $member->name }}" data-error="Please enter Member Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('surName')}}">Member Surname</label>
                                        <input type="text" class="col-lg-12 form-control round edit-basic" name="surName" 
                                            {{ isRequired('surName') }} value="{{ $memberDetails->surName }}"
                                            id="surName" placeholder="Enter Surname">
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Member Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="memberCode" 
                                                id="memberCode" required data-error="Member Code is required" readonly
                                                value="{{ $member->memberCode }}" placeholder="Member Code">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('gender')}}">Gender</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="gender" id="gender"
                                                {{ isRequired('gender') }} data-error="Please Select Gender">
                                                <option value="">Select</option>
                                                @foreach ($genders as $key => $gender)
                                                <option value="{{ $key }}">{{ $gender }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>


                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('maritalStatusId')}}">Marital
                                            Status</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="maritalStatusId"
                                                id="maritalStatusId" {{ isRequired('maritalStatusId') }}
                                                data-error="Please Select Marital Status">
                                                <option value="">Select</option>
                                                @foreach ($maritalStatus as $ms)
                                                <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('educationLevelId')}}">Education
                                            Level</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="educationLevelId"
                                                {{ isRequired('educationLevelId') }}
                                                data-error="Please Select Education">
                                                <option value="">Select</option>
                                                @foreach ($educationalLevels as $eduLevel)
                                                <option value="{{ $eduLevel->id }}">{{ $eduLevel->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-2 form-group">
                                        <label class="input-title {{isRequiredClass('dateOfBirth')}}">Date of
                                            Birth</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="dateOfBirth"
                                                name="dateOfBirth" placeholder="DD-MM-YYYY" autocomplete="off"
                                                {{ isRequired('dateOfBirth') }} data-error="Please Select Date">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-1 form-group">
                                        <label class="input-title">Age</label>
                                        <input type="text" class="form-control round" name="member_age" id="member_age"
                                            readonly placeholder="age">
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('fatherName')}}">{{ "Father's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="fatherName" 
                                                {{ isRequired('fatherName') }} value="{{ $memberDetails->fatherName }}"
                                                data-error="Enter Father Name" placeholder="Enter Father Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('motherName')}}">{{ "Mother's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="motherName" 
                                                {{ isRequired('motherName') }} value="{{ $memberDetails->motherName }}"
                                                data-error="Enter Mother Name" placeholder="Enter Mother Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">{{ "Son's Name (if any)" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="sonName" 
                                                value="{{ $memberDetails->sonName }}" placeholder="Enter Son Name">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 form-group" style="display: none;" id="spouseNameDiv">
                                        <label
                                            class="input-title {{isRequiredClass('spouseName')}}">{{ "Spouse's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round edit-basic" name="spouseName" 
                                                value="{{ $memberDetails->spouseName }}" data-error="Enter Spouse Name"
                                                placeholder="Enter Spouse Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('nationalityId')}}">Nationality</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="nationalityId"
                                                id="nationalityId" {{ isRequired('nationalityId') }}>
                                                <option value="">Select</option>
                                                @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->id }}">{{ $nationality->nationality }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('mobileNo')}}">Mobile No</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnMemberConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round mobileNo" name="mobileNo"
                                                id="mobileNo" placeholder="Enter Mobile No"
                                                value="{{ $memberDetails->mobileNo }}" {{ isRequired('mobileNo') }}>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('email')}}">Email</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="email" id="email"
                                                {{ isRequired('email') }} placeholder="Enter Email">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('formApplicationNo')}}">Form
                                            Application No</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber"
                                                name="formApplicationNo" id="formApplicationNo"
                                                value="{{ $memberDetails->formApplicationNo }}"
                                                placeholder="Enter Application No"
                                                {{ isRequired('formApplicationNo') }}>
                                        </div>
                                    </div>


                                </div>

                            </div>
                        </div>

                        {{-- evidences --}}
                        <div class="panel panel-default">
                            <div class="panel-heading p-2 mb-4">Evidences</div>
                            <div class="panel-body">

                                {{-- national id or smart card --}}
                                @php
                                $firtStageEvidences = $evidenceTypes->where('id', '<=', 2); @endphp <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('firstEvidenceTypeId')}}">Evidence</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="firstEvidenceTypeId"
                                                data-error="Select Evidence" {{ isRequired('firstEvidenceTypeId') }}>
                                                <option value="">Select</option>
                                                @foreach ($firtStageEvidences as $evidence)
                                                <option value="{{$evidence->id}}">{{$evidence->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('firstEvidence')}}">Evidence
                                            Value/Code/Id</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="firstEvidence"
                                                value="{{ $memberDetails->firstEvidence }}"
                                                {{ isRequired('firstEvidence') }} placeholder="Enter Value/Code/Id">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('firstEvidenceIssuerCountryId')}}">Issuer
                                            Country</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="firstEvidenceIssuerCountryId"
                                                {{ isRequired('firstEvidenceIssuerCountryId') }}
                                                data-error="Please Select Issuer Country">
                                                <option value="">Select</option>
                                                @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                            </div>
                            {{-- end national id or smart card --}}

                            {{-- Second Stage Evidences --}}
                            @php
                            $SecondStageEvidences = $evidenceTypes->where('id', '>', 2);
                            @endphp
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('secondEvidenceTypeId')}}">Other
                                        Evidence</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="secondEvidenceTypeId"
                                            {{ isRequired('secondEvidenceTypeId') }}>
                                            <option value="">Select</option>
                                            @foreach ($SecondStageEvidences as $evidence)
                                            <option value="{{$evidence->id}}">{{$evidence->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('secondEvidence')}}">Evidence
                                        Value/Code/Id</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="secondEvidence"
                                            value="{{ $memberDetails->secondEvidence }}"
                                            placeholder="Enter Value/Code/Id" {{ isRequired('secondEvidence') }}>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label
                                        class="input-title {{isRequiredClass('secondEvidenceIssuerCountryId')}}">Issuer
                                        Country</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="secondEvidenceIssuerCountryId"
                                            data-error="Please Select Issuer Country"
                                            {{ isRequired('secondEvidenceIssuerCountryId') }}>
                                            <option value="">Select</option>
                                            @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Valid Till</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" name="secondEvidenceValidTill" @if($memberDetails->secondEvidenceValidTill != null &&
                                        $memberDetails->secondEvidenceValidTill != '0000-00-00')
                                        value="{{ \Carbon\Carbon::parse($memberDetails->secondEvidenceValidTill)->format('d-m-Y') }}"
                                        @endif
                                        class="form-control round datepicker-custom">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                            </div>
                            {{-- end Second Stage Evidences --}}

                        </div>
                    </div>
                    {{-- end evidences --}}

                    {{-- Member's Admission Information --}}
                    <div class="panel panel-default">
                        <div class="panel-heading p-2 mb-4">Admission Information</div>
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Admission Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        @if ($isOpening)
                                        <input type="text" name="admissionDate" id="admissionDate" 
                                            class="form-control round edit-basic" style="cursor: pointer;"
                                            value="{{ \Carbon\Carbon::parse($member->admissionDate)->format('d-m-Y') }}"
                                            required>
                                        @else
                                        <input type="text" class="form-control round edit-basic" name="admissionDate" id="admissionDate" 
                                            value="{{ \Carbon\Carbon::parse($member->admissionDate)->format('d-m-Y') }}"
                                            readonly="true">
                                        @endif
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('admissionFee')}}">Admission Fee</label>
                                    <input type="text" class="form-control round edit-basic" name="admissionFee" id="admissionFee" 
                                        {{ isRequired('admissionFee') }} data-error="Enter Admission Fee" readonly
                                        value="{{ $memberDetails->admissionFee }}" placeholder="Enter Admission Fee">
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('primaryProductId')}}">Primary
                                        Product</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="primaryProductId"
                                            {{ isRequired('primaryProductId') }}
                                            data-error="Please Select Primary Product">
                                            <option value="">Select</option>
                                            @foreach ($primaryProducts as $primaryProduct)
                                            <option value="{{$primaryProduct->id}}">{{$primaryProduct->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('admissionNo')}}">Admission No</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textNumber" name="admissionNo"
                                            value="{{ $memberDetails->admissionNo }}" id="admissionNo"
                                            placeholder="Enter Admission No" {{ isRequired('admissionNo') }}>
                                    </div>
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
                                        <label class="input-title {{isRequiredClass('preDivisionId')}}">Division</label>
                                        <div class="input-group">
                                            <select name="preDivisionId" class="form-control clsSelect2 division"
                                                {{ isRequired('preDivisionId') }}>
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
                                        <label class="input-title {{isRequiredClass('preDistrictId')}}">District</label>
                                        <div class="input-group">
                                            <select name="preDistrictId" class="form-control clsSelect2 district"
                                                {{ isRequired('preDistrictId') }}>
                                                <option value="">Select</option>
                                                @foreach ($preAddress['districts'] as $district)
                                                <option value="{{ $district->id }}">{{ $district->district_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('preUpazilaId')}}">Thana/Upazila</label>
                                        <div class="input-group">
                                            <select name="preUpazilaId" class="form-control clsSelect2 upazila"
                                                {{ isRequired('preUpazilaId') }}>
                                                <option value="">Select</option>
                                                @foreach ($preAddress['upazilas'] as $upazila)
                                                <option value="{{ $upazila->id }}">{{ $upazila->upazila_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preUnionId')}}">Ward/Union</label>
                                        <div class="input-group">
                                            <select name="preUnionId" class="form-control clsSelect2 union"
                                                {{ isRequired('preUnionId') }}>
                                                <option value="">Select</option>
                                                @foreach ($preAddress['unions'] as $union)
                                                <option value="{{ $union->id }}">{{ $union->union_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('preVillageId')}}">Village/Ward</label>
                                        <div class="input-group">
                                            <select name="preVillageId" class="form-control clsSelect2 village"
                                                {{ isRequired('preVillageId') }}>
                                                <option value="">Select</option>
                                                @foreach ($preAddress['villages'] as $village)
                                                <option value="{{ $village->id }}">{{ $village->village_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preStreetHolding')}}">Street &
                                            Holding No</label>
                                        <div class="input-group">
                                            <textarea class="form-control round streetHolding" name="preStreetHolding"
                                                rows="2" placeholder="Enter Address" rows="3"
                                                {{ isRequired('preStreetHolding') }}
                                                data-error="Please Enter Address"></textarea>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group familyContactDiv">
                                        <label
                                            class="input-title {{isRequiredClass('familyContactNumber')}}">Family/Home
                                            Contact No.</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnMemberConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round mobileNo"
                                                {{ isRequired('familyContactNumber') }} name="familyContactNumber"
                                                placeholder="Enter Family/Home Contact No.">
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
                                        <label class="input-title {{isRequiredClass('perDivisionId')}}">Division</label>
                                        <div class="input-group">
                                            <select name="perDivisionId" class="form-control clsSelect2 division"
                                                {{ isRequired('perDivisionId') }}>
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
                                        <label class="input-title {{isRequiredClass('perDistrictId')}}">District</label>
                                        <div class="input-group">
                                            <select name="perDistrictId" class="form-control clsSelect2 district"
                                                {{ isRequired('perDistrictId') }}>
                                                <option value="">Select</option>
                                                @foreach ($perAddress['districts'] as $district)
                                                <option value="{{ $district->id }}">{{ $district->district_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('perUpazilaId')}}">Thana/Upazila</label>
                                        <div class="input-group">
                                            <select name="perUpazilaId" class="form-control clsSelect2 upazila"
                                                {{ isRequired('perUpazilaId') }}>
                                                <option value="">Select</option>
                                                @foreach ($perAddress['upazilas'] as $upazila)
                                                <option value="{{ $upazila->id }}">{{ $upazila->upazila_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perUnionId')}}">Ward/Union</label>
                                        <div class="input-group">
                                            <select name="perUnionId" class="form-control clsSelect2 union"
                                                {{ isRequired('perUnionId') }}>
                                                <option value="">Select</option>
                                                @foreach ($perAddress['unions'] as $union)
                                                <option value="{{ $union->id }}">{{ $union->union_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label
                                            class="input-title {{isRequiredClass('perVillageId')}}">Village/Ward</label>
                                        <div class="input-group">
                                            <select name="perVillageId" class="form-control clsSelect2 village"
                                                {{ isRequired('perVillageId') }}>
                                                <option value="">Select</option>
                                                @foreach ($perAddress['villages'] as $village)
                                                <option value="{{ $village->id }}">{{ $village->village_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perStreetHolding')}}">Street &
                                            Holding No</label>
                                        <div class="input-group">
                                            <textarea class="form-control round streetHolding" name="perStreetHolding"
                                                {{ isRequired('perStreetHolding') }} rows="2"
                                                placeholder="Enter Address" rows="3"
                                                data-error="Please Enter Address"></textarea>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Nominee Details -->
                    <div class="panel panel-default">
                        <div class="panel-heading p-2 mb-4">Nominee Details
                            <a href="javascript:void(0)" class="addNominee float-right">
                                <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                                <span style="font-size:13px;">Add</span>
                            </a>
                        </div>
                        <div class="panel-body">
                            @foreach ($nominees as $key => $nominee)
                            <div class="row nomineeRow">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeNames[]')}}">Nominee
                                        Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="nomineeNames[]"
                                            value="{{ $nominee->name }}" placeholder="Enter Nominee Name"
                                            {{ isRequired('nomineeNames[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeMobileNos[]')}}">Mobile
                                        No</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                {{ $mfnMemberConfig->countryCode }}
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round mobileNo" name="nomineeMobileNos[]"
                                            value="{{ $nominee->mobileNo }}" placeholder="Enter Mobile No."
                                            {{ isRequired('nomineeMobileNos[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label
                                        class="input-title {{isRequiredClass('nomineeRelationships[]')}}">Relation</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2 nomineeRelationship"
                                            name="nomineeRelationships[]" {{ isRequired('nomineeRelationships[]') }}
                                            data-error="Please Select Relation with Nominee" style="width: 100%;">
                                            <option value="">Select</option>
                                            @foreach ($relationships as $relationship)
                                            @php
                                            $selectedAttr = $relationship->id == $nominee->relationshipId ?
                                            'selected="selected"' : '';
                                            @endphp
                                            <option value="{{ $relationship->id }}" {{ $selectedAttr }}>
                                                {{ $relationship->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeShares[]')}}">Share (%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textAmount" name="nomineeShares[]"
                                            value="{{ $nominee->share }}" placeholder="Enter Share (%)"
                                            {{ isRequired('nomineeShares[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-1 form-group nomineeTrashDiv" @if ($key==0) style="display: none;"
                                    @endif>
                                    <label class="input-title">Remove</label>
                                    <div class="input-group">
                                        <a href="javascript:void(0)">
                                            <i class="icon fa fa-trash blue-grey-600 nomineeTrash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>

                    <!-- Reference Details -->
                    <div class="panel panel-default">
                        <div class="panel-heading p-2 mb-4">Reference Details
                            <a href="javascript:void(0)" class="addReference float-right">
                                <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                                <span style="font-size:13px;">Add</span>
                            </a>
                        </div>

                        <div class="panel-body">
                            @foreach ($references as $key => $reference)
                            <div class="row referenceRow">
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('referenceNames[]')}}">Reference
                                        Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceNames[]"
                                            value="{{ $reference->name }}" placeholder="Enter Name"
                                            {{ isRequired('referenceNames[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label
                                        class="input-title {{isRequiredClass('referenceRelationships[]')}}">Relation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceRelationships[]"
                                            value="{{ $reference->relationship }}"
                                            {{ isRequired('referenceRelationships[]') }} placeholder="Enter Relation">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label
                                        class="input-title {{isRequiredClass('referenceOrganizations[]')}}">Organization</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceOrganizations[]"
                                            value="{{ $reference->organization }}"
                                            {{ isRequired('referenceOrganizations[]') }}
                                            placeholder="Organization name">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label
                                        class="input-title {{isRequiredClass('referenceDesignations[]')}}">Designation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceDesignations[]"
                                            value="{{ $reference->designation }}"
                                            placeholder="Enter Reference Designation"
                                            {{ isRequired('referenceDesignations[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('referenceMobileNos[]')}}">Mobile
                                        No</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                {{ $mfnMemberConfig->countryCode }}
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round mobileNo"
                                            name="referenceMobileNos[]" value="{{ $reference->mobileNo }}"
                                            placeholder="Enter Mobile No." {{ isRequired('referenceMobileNos[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-1 form-group referenceTrashDiv" @if ($key==0) style="display: none;"
                                    @endif>
                                    <label class="input-title">Remove</label>
                                    <div class="input-group">
                                        <a href="javascript:void(0)" class="referenceTrash"><i
                                                class="icon fa fa-trash blue-grey-600"></i></a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Other Information -->
                    <div class="panel panel-default">
                        <div class="panel-heading p-2 mb-4">Other Information</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('professionId')}}">Profession</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="professionId" id="professionId"
                                            {{ isRequired('professionId') }} data-error="Please Select Profession">
                                            <option value="">Select</option>
                                            @foreach ($professions as $profession)
                                            <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('religionId')}}">Religion</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="religionId" id="religionId"
                                            {{ isRequired('religionId') }} data-error="Please Select Profession">
                                            <option value="">Select</option>
                                            @foreach ($religions as $religion)
                                            <option value="{{ $religion->id }}">{{ $religion->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title">No of Family Member</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textNumber"
                                            name="numberOfFamilyMember" id="numberOfFamilyMember"
                                            placeholder="Enter No of Family Member">
                                    </div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Yearly Income</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textAmount" name="yearlyIncome"
                                            id="yearlyIncome" placeholder="Enter Yearly Income">
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Land Area</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="landArea" id="landArea"
                                            placeholder="Enter Land Area">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Note</label>
                                    <div class="input-group">
                                        <textarea class="form-control round" id="note" name="note" rows="2"
                                            placeholder="Enter Address" rows="3"
                                            data-error="Please Enter Note"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('fixedAssetDescription')}}">Fixed Asset
                                        Description</label>
                                    <div class="input-group">
                                        <textarea class="form-control round" name="fixedAssetDescription" rows="2"
                                            {{ isRequired('fixedAssetDescription') }} placeholder="Enter Address"
                                            rows="3" data-error="Please Enter Description"></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="form-row form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" id="nextButton" class="btn btn-primary btn-round">Next</a>
                </div>
            </div>

        </div>

        <!--  tab pane 2 (Photo) -->
        <div class="tab-pane show" id="photo" role="tabpanel">

            <div class="row">
                <div class="col-lg-12">

                    <!-- Upload Photo -->
                    <div class="panel panel-default">
                        <div class="panel-heading p-2">Upload Photo</div>
                        <div class="panel-body">

                            <!-- Profile Picture -->
                            <div class="well border-bottom">
                                <h4 class="panel-title text-center">Member Profile Picture</h4>
                                <div class="row">
                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                    <div class="col-md-6">
                                        <div style="width: 290px; height: 190px;">
                                            <div></div>
                                            <video id="profileVideo" autoplay="autoplay"
                                                style="width: 290px; height: 190px;"></video>
                                        </div>
                                        <br>
                                        <input type="button" id="profileSnap" class="btn btn-success btn-round"
                                            value="Take Picture">
                                    </div>
                                    @endif

                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                    @php
                                    $profileImageSizes = explode(':', $mfnMemberConfig->profileImageSize);
                                    $width = $profileImageSizes[0];
                                    $height = $profileImageSizes[1];
                                    @endphp
                                    <div class="col-md-6">
                                        @else
                                        <div class="col-md-12">
                                            @endif
                                            <div class="">
                                                <span>Your given image will appear here... </span><br>
                                                @if ($mfnGnlConfig->useWebCam == 'yes')
                                                <canvas id="profileCanvas" width="{{ $width }}" height="{{ $height }}"
                                                    hidden></canvas>
                                                @endif
                                                <input type="text" name="profileImageText" id="profileImageText" hidden>
                                                <img id="profileImagePreview" @if ($memberDetails->profileImage != '')
                                                src="{{asset('images/members/profile') . '/'. $memberDetails->profileImage}}"
                                                @endif
                                                class="img-responsive img-rounded full-width mt-2"
                                                style="height: 150PX; width: 120PX;">
                                            </div>
                                            <input class="profileImage mt-2" name="profileImage" id="profileImage"
                                                type="file">
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature -->
                                <div class="well">
                                    <h4 class="panel-title text-center">Member National ID Signature</h4>
                                    <div class="row">
                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                        <div class="col-md-6">
                                            <div style="width: 290px; height: 190px;">
                                                <div></div>
                                                <video id="signatureVideo" autoplay="autoplay"
                                                    style="width: 290px; height: 190px;"></video>
                                            </div>
                                            <br>
                                            <input id="signatureSnap" type="button" class="btn btn-success btn-round"
                                                value="Take Picture">
                                        </div>
                                        @endif

                                        @if ($mfnGnlConfig->useWebCam == 'yes')
                                        @php
                                        $signatureImageSizes = explode(':',$mfnMemberConfig->signatureImageSize);
                                        $width = $signatureImageSizes[0];
                                        $height = $signatureImageSizes[1];
                                        @endphp
                                        <div class="col-md-6">
                                            @else
                                            <div class="col-md-12">
                                                @endif
                                                <div class="">
                                                    <span>Your given image will appear here... </span><br>
                                                    @if ($mfnGnlConfig->useWebCam == 'yes')
                                                    <canvas id="signatureCanvas" width="{{ $width }}"
                                                        height="{{ $height }}" hidden></canvas>
                                                    @endif
                                                    <input type="text" name="signatureImageText" id="signatureImageText"
                                                        hidden>
                                                    <img id="signatureImagePreview" @if ($memberDetails->signatureImage
                                                    != '')
                                                    src="{{asset('images/members/signature') . '/'. $memberDetails->signatureImage}}"
                                                    @endif
                                                    class="img-responsive img-rounded full-width mt-2"
                                                    style="height: 100PX; width: 200PX;">
                                                </div>
                                                <input class="signatureImage mt-2" name="signatureImage"
                                                    id="signatureImage" type="file">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row form-group d-flex justify-content-center">
                                        <div class="example example-buttons">
                                            <a href="javascript:void(0)" id="previousButton"
                                                class="btn btn-default btn-round">Previous</a>
                                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</form>

<script type="text/javascript">
    // set default values
    $("#gender").val("{{ $member->gender }}");
    $("#nationalityId").val("{{ $memberDetails->nationalityId }}");
    $("select[name='maritalStatusId']").val("{{ $memberDetails->maritalStatusId }}");
    $("select[name='educationLevelId']").val("{{ $memberDetails->educationLevelId }}");
    $("input[name='familyContactNumber']").val("{{ $memberDetails->familyContactNumber }}");
    $("select[name='firstEvidenceTypeId']").val("{{ $memberDetails->firstEvidenceTypeId }}");
    $("select[name='firstEvidenceIssuerCountryId']").val("{{ $memberDetails->firstEvidenceIssuerCountryId }}");
    $("select[name='secondEvidenceTypeId']").val("{{ $memberDetails->secondEvidenceTypeId }}");
    $("select[name='secondEvidenceIssuerCountryId']").val("{{ $memberDetails->secondEvidenceIssuerCountryId }}");
    $("select[name='primaryProductId']").val("{{ $member->primaryProductId }}");
    $("select[name='professionId']").val("{{ $memberDetails->professionId }}");
    $("select[name='religionId']").val("{{ $memberDetails->religionId }}");
    $("input[name='numberOfFamilyMember']").val("{{ $memberDetails->numberOfFamilyMember }}");
    $("input[name='yearlyIncome']").val("{{ $memberDetails->yearlyIncome }}");
    $("input[name='landArea']").val("{{ $memberDetails->landArea }}");
    $("textarea[name='note']").val("{{ $memberDetails->note }}");
    $("textarea[name='fixedAssetDescription']").val("{{ $memberDetails->fixedAssetDescription }}");

    // address
    $("select[name='preDivisionId']").val("{{ $memberDetails->preDivisionId }}");
    $("select[name='preDistrictId']").val("{{ $memberDetails->preDistrictId }}");
    $("select[name='preUpazilaId']").val("{{ $memberDetails->preUpazilaId }}");
    $("select[name='preUnionId']").val("{{ $memberDetails->preUnionId }}");
    $("select[name='preVillageId']").val("{{ $memberDetails->preVillageId }}");
    $("textarea[name='preStreetHolding']").val("{{ $memberDetails->preStreetHolding }}");

    $("select[name='perDivisionId']").val("{{ $memberDetails->perDivisionId }}");
    $("select[name='perDistrictId']").val("{{ $memberDetails->perDistrictId }}");
    $("select[name='perUpazilaId']").val("{{ $memberDetails->perUpazilaId }}");
    $("select[name='perUnionId']").val("{{ $memberDetails->perUnionId }}");
    $("select[name='perVillageId']").val("{{ $memberDetails->perVillageId }}");
    $("textarea[name='perStreetHolding']").val("{{ $memberDetails->perStreetHolding }}");

    var sameAsPreesentAddress = "{{ $sameAsPreesentAddress }}";

    if (sameAsPreesentAddress) {
        $('#sameAsPreesent').prop('checked', true);
    }

    // take clone of the following divs to append later.
    var nomineeRow = $('.nomineeRow').clone();
    var referenceRow = $('.referenceRow').clone();

    $(document).ready(function () {

        var mandatorySavingProducts = '<?php echo count($mandatorySavingProducts)?>';

        // fix select box width
        $('.clsSelect2').width("100%");

        /* deposit type */
        $("#transactionTypeId").change(function (e) {
            if ($(this).val() == 2) {
                $(".bankDiv").show('slow');
            } else {
                $(".bankDiv").hide('slow');
            }
        });
        /* end deposit type */

        $('form').submit(function (event) {
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');
            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    contentType: false,
                    data: new FormData(this),
                    processData: false,
                    // basicInfoEdit : $('#basic_info_button').is(':checked'),
                    
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
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = "./../";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                });
        });


        $("#samityId").change(function (event) {
            $("#memberCode").val('');

            if ($(this).val() != '') {
                // get member code
                $.ajax({
                        url: './getData',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            context: 'samity',
                            samityId: $("#samityId").val()
                        },
                    })
                    .done(function (data) {
                        $("#memberCode").val(data.memberCode);

                        $.each(data.savingsCodes, function (productId, savingsCode) {
                            $.each($("input[name^='savingsCodes']"), function (index, el) {
                                if ($(el).attr('productId') == productId) {
                                    $(el).val(savingsCode);
                                }
                            });
                        });
                    })
                    .fail(function (response) {
                        console.log("error");
                    });
            }

        });

        /*calculate the age and Validate the age */
        $("#dateOfBirth").on('change', function () {

            $("#member_age").val('');

            var systemDate = new Date("{{ $sysDate }}");
            var dateOfBirth = toDate(this.value);

            var diff = systemDate.getTime() - dateOfBirth.getTime();
            var age = Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));

            var minAge = "{{ $mfnMemberConfig->minAge }}";
            if (minAge != '') {
                if (age < minAge) {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: "Age should be minimum " + minAge,
                    });
                    this.value = '';
                    return false;
                }
            }

            var maxAge = "{{ $mfnMemberConfig->maxAge }}";
            if (maxAge != '') {
                if (age > maxAge) {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: "Age should be maximum " + maxAge,
                    });
                    this.value = '';
                    return false;
                }
            }

            $("#member_age").val(age);
        });
        $("#dateOfBirth").val("{{ \Carbon\Carbon::parse($memberDetails->dateOfBirth)->format('d-m-Y') }}")
            .trigger('change');
        /*end calculating the age and Validation of the age */

        /* mobile number length */
        $('.mobileNo').unbind();
        $(document).on('input', '.mobileNo', function () {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
            if ($(this).val().length != "{{ $mfnMemberConfig->mobileNoLength }}") {
                $(this).parents().eq(1).find('.help-block').html("Length should be " +
                    "{{ $mfnMemberConfig->mobileNoLength }}").show();
                $(this).focus();
            } else {
                $(this).parents().eq(1).find('.help-block').fadeOut('slow');
            }
        });
        /* end mobile number length */

        /* add nominee row */
        $('.addNominee').on('click', function () {
            var nomineeRowClone = nomineeRow.clone();
            nomineeRowClone.find('.nomineeTrashDiv').show();
            nomineeRowClone.find('.clsSelect2').select2();
            $(this).parents().eq(1).find('.panel-body').append(nomineeRowClone);
        });
        /* end add nominee row */

        /* remove nominee row */
        $(document).on('click', '.nomineeTrash', function () {
            $(this).closest('.nomineeRow').remove();
        });
        /* end remove nominee row */

        /* add reference row */
        $('.addReference').on('click', function () {
            var referenceRowClone = referenceRow.clone();
            referenceRowClone.find('.referenceTrashDiv').show();
            $(this).parents().eq(1).find('.panel-body').append(referenceRowClone);
        });
        /* end add reference row */

        /* remove reference row */
        $(document).on('click', '.referenceTrash', function () {
            $(this).closest('.referenceRow').remove();
        });
        /* end remove reference row */

        $(".maturePeriods").on('change', function () {
            $(this).parents().eq(3).find('.interestRate').val($(this).val());
        });

        $('#savingsTab').click(function (e) {
            e.preventDefault();

            var numberOfEmptyFields = $('#details').find('input[required], select[required]').filter(
                function () {
                    return $(this).val() === "";
                }).length;

            if (numberOfEmptyFields > 0) {
                $('#details').find(".form-control:invalid").first().focus(); // show all data error
                $('#details').find(".form-control:invalid").focusout(); // Focus on first error

                alert('Please fill all required fields first.');
                return false;
            }

        });
        $('#photoTab').click(function (e) {
            e.preventDefault();

            var numberOfEmptyFields = $('#details').find('input[required], select[required]').filter(
                function () {
                    return $(this).val() === "";
                }).length;

            if (numberOfEmptyFields > 0) {
                $('#details').find(".form-control:invalid").first().focus(); // show all data error
                $('#details').find(".form-control:invalid").focusout(); // Focus on first error

                alert('Please fill all required fields first.');
                return false;
            }

            var numberOfEmptyFields = $('#savings').find('input[required], select[required]').filter(
                function () {
                    return $(this).val() === "";
                }).length;

            if (numberOfEmptyFields > 0) {
                $('#savings').find(".form-control:invalid").first().focus(); // show all data error
                $('#savings').find(".form-control:invalid").focusout(); // Focus on first error

                alert('Please fill all required fields first.');
                return false;
            }

        });

        // When Initial next button is clicked,go to savings if not empty, otherwise photos
        $("#nextButton").click(function (e) {
            e.preventDefault();
            $("#photoTab").trigger('click');
        });

        // When Photo div's Previous button is clicked,go to savings if not empty, otherwise Initial
        $("#previousButton").click(function (e) {
            e.preventDefault();
            $("#initialTab").trigger('click');
        });

        if ("{{$mfnGnlConfig->useWebCam}}" == 'yes') {
            /* web cam */
            var profileVideo = document.getElementById('profileVideo');
            var signatureVideo = document.getElementById('signatureVideo');

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: true
                }).then(function (stream) {
                    profileVideo.srcObject = stream;
                    profileVideo.play();

                    signatureVideo.srcObject = stream;
                    signatureVideo.play();
                });
            }

            // take profile image
            var profileCanvas = document.getElementById('profileCanvas');
            var profileContext = profileCanvas.getContext('2d');

            document.getElementById('profileSnap').addEventListener('click', function () {
                profileContext.drawImage(profileVideo, 0, 0, 340, 280);
                var image = profileCanvas.toDataURL("image/png");
                document.getElementById("profileImagePreview").src = image;
                document.getElementById("profileImageText").value = image;
            });

            // take Signature image
            var signatureCanvas = document.getElementById('signatureCanvas');
            var signatureContext = signatureCanvas.getContext('2d');

            document.getElementById('signatureSnap').addEventListener('click', function () {
                signatureContext.drawImage(signatureVideo, 0, 0, 340, 280);
                var image = signatureCanvas.toDataURL("image/png");
                document.getElementById("signatureImagePreview").src = image;
                document.getElementById("signatureImageText").value = image;
            });

            /* end web cam */
        }

        /* set image when choose file */
        $('#profileImage').change(function (event) {
            $('#profileImagePreview').val('');
            $('#profileImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        $('#signatureImage').change(function (event) {
            $('#signatureImageText').val('');
            $('#signatureImagePreview').attr('src', URL.createObjectURL(event.target.files[0]));
        });
        /* end set image when choose file */

        /* show/hide spouse name base on marital Status */
        $('#maritalStatusId').change(function (e) {
            if ($(this).val() == 1 || $(this).val() == 4) {
                $('#spouseNameDiv').show('slow');
            } else {
                $('#spouseNameDiv').hide('slow');
            }
        });
        /* end show/hide spouse name base on marital Status */
        $('#maritalStatusId').trigger('change');

        /* address */
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
                url: "./../../" + url,
                data: data,
                async: false,
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

            if ($(this).is(":checked")) {
                $(".permanentAddressDiv").css('pointer-events', 'none');

                cloneSelect('preDivisionId', 'perDivisionId');
                cloneSelect('preDistrictId', 'perDistrictId');
                cloneSelect('preUpazilaId', 'perUpazilaId');
                cloneSelect('preUnionId', 'perUnionId');
                cloneSelect('preVillageId', 'perVillageId');
                $('textarea[name="perStreetHolding"]').val($('textarea[name="preStreetHolding"]')
            .val());

            } else {
                $(".permanentAddressDiv").css('pointer-events', '');
            }
        });
        /* end when click same as present address */

        /* clone select options */
        function cloneSelect(sourceName, targetName) {
            var source = $("select[name='" + sourceName + "']");
            var target = $("select[name='" + targetName + "']");

            target.empty();

            $("select[name='" + sourceName + "'] > option").each(function () {
                target.append("<option value=" + this.value + ">" + this.text + "</option>");
            });
            target.val(source.val());
        }
        /* end cloning select options */

        $('.datepicker-custom').click(function (e) {
            $(this).val('');
        });

        var isOpening = "{{ $isOpening }}";

        if (isOpening) {
            var systemDate = new Date("{{ $sysDate }}");
            $('#admissionDate').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
                maxDate: systemDate
            }).keydown(false);
        }

        $('#dateOfBirth').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            maxDate: systemDate
        }).keydown(false);

    });

    function basicInfoButtonToggle(ele){
        
        if($(ele).is(':checked')){
            $('input:not(.edit-basic)').attr('readonly', true);
            $('textarea').attr('readonly', true);
            $('select').attr('disabled',true);
        }
        else{
            $('input:not(.edit-basic)').attr('readonly', false);
            $('textarea').attr('readonly', false);
            $('select').attr('disabled', false);
        }
    }

    $('.tooltip-icon').on('mouseover', function(e){
        $('.tooltip-text').css('display','block');
    });
    $('.tooltip-icon').on('mouseout', function(e){
        $('.tooltip-text').css('display','none');
    });

</script>


@endsection
