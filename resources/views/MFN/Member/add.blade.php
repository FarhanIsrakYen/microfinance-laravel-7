@extends('Layouts.erp_master')
@section('content')

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

        <ul class="nav nav-tabs nav-tabs-reverse  text-center" role="tablist">
            <li class="nav-item mr-2 flex-fill" role="presentation" id="initialList">
                <a class="nav-link nav-tabs btn btn-bg-color active" id="initialTab" data-toggle="tab" href="#details"
                    role="tab">Member Details
                </a>
            </li>
            @if (count($mandatorySavingProducts) > 0)
            <li class="nav-item mr-2 flex-fill" role="presentation" id="savingsList">
                <a class="nav-link nav-tabs btn btn-bg-color" id="savingsTab" data-toggle="tab" href="#savings"
                    role="tab">Savings
                </a>
            </li>
            @endif
            <li class="nav-item mr-2 flex-fill" role="presentation" id="photoList">
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
                            @if ($isOpening)
                            <div class="panel-heading p-2 mb-4">Opening Member</div>
                            @else
                            <div class="panel-heading p-2 mb-4">Member</div>
                            @endif
                            <div class="panel-body">

                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('samityId')}}">Samity</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="samityId" id="samityId"
                                                {{ isRequired('samityId') }} data-error="Please Select Samity Name">
                                                <option value="">Select</option>
                                                @foreach ($samities as $samity)
                                                <option value="{{$samity->id}}">{{$samity->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('name')}}">Member Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="name"
                                                placeholder="Enter Member Name" {{ isRequired('name') }}
                                                data-error="Please enter Member Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('surName')}}">Member Surname</label>
                                        <input type="text" class="col-lg-12 form-control round" name="surName" {{ isRequired('surName') }}
                                            id="surName" placeholder="Enter Surname">
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">Member Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="memberCode"
                                                id="memberCode" required data-error="Member Code is required" readonly
                                                placeholder="Member Code">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('gender')}}">Gender</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="gender" id="gender" {{ isRequired('gender') }}
                                                data-error="Please Select Gender">
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>


                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('maritalStatusId')}}">Marital Status</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="maritalStatusId"
                                                id="maritalStatusId" {{ isRequired('maritalStatusId') }} data-error="Please Select Marital Status">
                                                <option value="">Select</option>
                                                @foreach ($maritalStatus as $ms)
                                                <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('educationLevelId')}}">Education Level</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="educationLevelId" {{ isRequired('educationLevelId') }}
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
                                        <label class="input-title {{isRequiredClass('dateOfBirth')}}">Date of Birth</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="dateOfBirth"
                                                name="dateOfBirth" placeholder="DD-MM-YYYY" autocomplete="off" {{ isRequired('dateOfBirth') }}
                                                data-error="Please Select Date">
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
                                        <label class="input-title {{isRequiredClass('fatherName')}}">{{ "Father's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="fatherName" {{ isRequired('fatherName') }}
                                                data-error="Enter Father Name" placeholder="Enter Father Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('motherName')}}">{{ "Mother's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="motherName" {{ isRequired('motherName') }}
                                                data-error="Enter Mother Name" placeholder="Enter Mother Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title">{{ "Son's Name (if any)" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="sonName"
                                                placeholder="Enter Son Name">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 form-group" style="display: none;" id="spouseNameDiv">
                                        <label class="input-title {{isRequiredClass('spouseName')}}">{{ "Spouse's Name" }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="spouseName"
                                                data-error="Enter Spouse Name" placeholder="Enter Spouse Name">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>


                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('nationalityId')}}">Nationality</label>
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
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('mobileNo')}}">Mobile No</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnMemberConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round mobileNo"
                                                name="mobileNo" id="mobileNo" placeholder="Enter Mobile No" {{ isRequired('mobileNo') }}>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('email')}}">Email</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="email" id="email" {{ isRequired('email') }}
                                                placeholder="Enter Email">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('formApplicationNo')}}">Form Application No</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round textNumber"
                                                name="formApplicationNo" id="formApplicationNo"
                                                placeholder="Enter Application No" {{ isRequired('formApplicationNo') }}>
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
                                        <label class="input-title {{isRequiredClass('firstEvidenceTypeId')}}">Evidence</label>
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
                                        <label class="input-title {{isRequiredClass('firstEvidence')}}">Evidence Value/Code/Id</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control round" name="firstEvidence" {{ isRequired('firstEvidence') }}
                                                placeholder="Enter Value/Code/Id">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('firstEvidenceIssuerCountryId')}}">Issuer Country</label>
                                        <div class="input-group">
                                            <select class="form-control clsSelect2" name="firstEvidenceIssuerCountryId"
                                            {{ isRequired('firstEvidenceIssuerCountryId') }} data-error="Please Select Issuer Country">
                                                <option value="">Select</option>
                                                @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
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
                                    <label class="input-title {{isRequiredClass('secondEvidenceTypeId')}}">Other Evidence</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="secondEvidenceTypeId" {{ isRequired('secondEvidenceTypeId') }}>
                                            <option value="">Select</option>
                                            @foreach ($SecondStageEvidences as $evidence)
                                            <option value="{{$evidence->id}}">{{$evidence->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('secondEvidence')}}">Evidence Value/Code/Id</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="secondEvidence"
                                            placeholder="Enter Value/Code/Id" {{ isRequired('secondEvidence') }}>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('secondEvidenceIssuerCountryId')}}">Issuer Country</label>
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
                                        <input type="text" name="secondEvidenceValidTill"
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
                                    <label class="input-title @if ($isOpening) RequiredStar @endif ">Admission
                                        Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        @if ($isOpening)
                                        <input type="text" name="admissionDate" id="admissionDate"
                                            class="form-control round" style="cursor: pointer;" required>
                                        @else
                                        <input type="text" name="admissionDate" id="admissionDate"
                                            class="form-control round"
                                            value="{{ \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                            readonly="true">
                                        @endif

                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('admissionFee')}}">Admission Fee</label>
                                    <input type="text" class="form-control round" name="admissionFee" id="admissionFee" {{ isRequired('admissionFee') }}
                                        data-error="Enter Admission Fee" readonly
                                        value="{{ $mfnMemberConfig->admissionFee }}" placeholder="Enter Admission Fee">
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('primaryProductId')}}">Primary Product</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="primaryProductId" {{ isRequired('primaryProductId') }}
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
                                        <input type="text" class="form-control round textNumber" name="admissionNo" {{ isRequired('admissionNo') }}
                                            id="admissionNo" placeholder="Enter Admission No">
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
                                            <select name="preDistrictId" id="preDistrictId" class="form-control clsSelect2 district"
                                            {{ isRequired('preDistrictId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preUpazilaId')}}">Thana/Upazila</label>
                                        <div class="input-group">
                                            <select name="preUpazilaId" class="form-control clsSelect2 upazila"
                                            {{ isRequired('preUpazilaId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preUnionId')}}">Ward/Union</label>
                                        <div class="input-group">
                                            <select name="preUnionId" class="form-control clsSelect2 union" {{ isRequired('preUnionId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preVillageId')}}">Village/Ward</label>
                                        <div class="input-group">
                                            <select name="preVillageId" class="form-control clsSelect2 village"
                                            {{ isRequired('preVillageId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('preStreetHolding')}}">Street & Holding No</label>
                                        <div class="input-group">
                                            <textarea class="form-control round streetHolding" name="preStreetHolding"
                                                rows="2" placeholder="Enter Address" rows="3" {{ isRequired('preStreetHolding') }}
                                                data-error="Please Enter Address"></textarea>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                    <div class="col-lg-3 form-group familyContactDiv">
                                        <label class="input-title {{isRequiredClass('familyContactNumber')}}">Family/Home Contact No.</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    {{ $mfnMemberConfig->countryCode }}
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round mobileNo" {{ isRequired('familyContactNumber') }}
                                                name="familyContactNumber" placeholder="Enter Family/Home Contact No.">
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

                            {{-- <div id="sameAsPreesentDiv" style="pointer-events: none;">
                            </div> --}}

                            <div class="permanentAddressDiv">
                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perDivisionId')}}">Division</label>
                                        <div class="input-group">
                                            <select name="perDivisionId" class="form-control clsSelect2 division" {{ isRequired('perDivisionId') }}>
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
                                            <select name="perDistrictId" id="perDistrictId" class="form-control clsSelect2 district" {{ isRequired('perDistrictId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perUpazilaId')}}">Thana/Upazila</label>
                                        <div class="input-group">
                                            <select name="perUpazilaId" class="form-control clsSelect2 upazila" {{ isRequired('perUpazilaId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perUnionId')}}">Ward/Union</label>
                                        <div class="input-group">
                                            <select name="perUnionId" class="form-control clsSelect2 union" {{ isRequired('perUnionId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perVillageId')}}">Village/Ward</label>
                                        <div class="input-group">
                                            <select name="perVillageId" class="form-control clsSelect2 village" {{ isRequired('perVillageId') }}>
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>

                                    <div class="col-lg-3 form-group">
                                        <label class="input-title {{isRequiredClass('perStreetHolding')}}">Street & Holding No</label>
                                        <div class="input-group">
                                            <textarea class="form-control round streetHolding" name="perStreetHolding" {{ isRequired('perStreetHolding') }}
                                                rows="2" placeholder="Enter Address" rows="3"
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
                            <div class="row nomineeRow">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeNames[]')}}">Nominee Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="nomineeNames[]"
                                            placeholder="Enter Nominee Name" {{ isRequired('nomineeNames[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeMobileNos[]')}}">Mobile No</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                {{ $mfnMemberConfig->countryCode }}
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round mobileNo"
                                            name="nomineeMobileNos[]" placeholder="Enter Mobile No." {{ isRequired('nomineeMobileNos[]') }}>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeRelationships[]')}}">Relation</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2 nomineeRelationship"
                                            name="nomineeRelationships[]" {{ isRequired('nomineeRelationships[]') }}
                                            data-error="Please Select Relation with Nominee" style="width: 100%;">
                                            <option value="">Select</option>
                                            @foreach ($relationships as $relationship)
                                            <option value="{{ $relationship->id }}">{{ $relationship->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('nomineeShares[]')}}">Share (%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textAmount" name="nomineeShares[]"
                                        {{ isRequired('nomineeShares[]') }} placeholder="Enter Share (%)">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-1 form-group nomineeTrashDiv" style="display: none;">
                                    <label class="input-title">Remove</label>
                                    <div class="input-group">
                                        <a href="javascript:void(0)">
                                            <i class="icon fa fa-trash blue-grey-600 nomineeTrash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

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
                            <div class="row referenceRow">
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('referenceNames[]')}}">Reference Name</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceNames[]" {{ isRequired('referenceNames[]') }}
                                            placeholder="Enter Name">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('referenceRelationships[]')}}">Relation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceRelationships[]"
                                        {{ isRequired('referenceRelationships[]') }} placeholder="Enter Relation">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('referenceOrganizations[]')}}">Organization</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceOrganizations[]"
                                        {{ isRequired('referenceOrganizations[]') }} placeholder="Organization name">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title {{isRequiredClass('referenceDesignations[]')}}">Designation</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="referenceDesignations[]"
                                        {{ isRequired('referenceDesignations[]') }} placeholder="Enter Reference Designation">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="input-title {{isRequiredClass('referenceMobileNos[]')}}">Mobile No</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                {{ $mfnMemberConfig->countryCode }}
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round mobileNo"
                                            name="referenceMobileNos[]" {{ isRequired('referenceMobileNos[]') }} placeholder="Enter Mobile No.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                <div class="col-lg-1 form-group referenceTrashDiv" style="display: none;">
                                    <label class="input-title">Remove</label>
                                    <div class="input-group">
                                        <a href="javascript:void(0)" class="referenceTrash"><i
                                                class="icon fa fa-trash blue-grey-600"></i></a>
                                    </div>
                                </div>
                            </div>
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
                                    <label class="input-title {{isRequiredClass('fixedAssetDescription')}}">Fixed Asset Description</label>
                                    <div class="input-group">
                                        <textarea class="form-control round" name="fixedAssetDescription" rows="2" {{ isRequired('fixedAssetDescription') }}
                                            placeholder="Enter Address" rows="3"
                                            data-error="Please Enter Description"></textarea>
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
        <!--  tab pane 2 (Savings) -->

        @if (count($mandatorySavingProducts) > 0)
        <div class="tab-pane show" id="savings" role="tabpanel">

            <div class="row">
                <div class="col-lg-12">

                    <!-- Savings -->
                    <div class="panel panel-default">
                        <div class="panel-heading p-2 mb-4">Savings</div>
                        <div class="panel-body">
                            @foreach ($mandatorySavingProducts as $savingsProduct)

                            {{-- if product is regular/one time commom field --}}
                            <div class="row" style="margin-bottom: 40px; border: 1px solid #D3D3D3">
                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Product</label>
                                    <div class="input-group">
                                        <input type="hidden" name="savingProducts[]" value="{{ $savingsProduct->id }}">
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control round"
                                            value="{{ $savingsProduct->name }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Saving Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="savingsCodes[]" readonly
                                            productId="{{ $savingsProduct->id }}" placeholder="Saving Code">
                                    </div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Opening Date</label>
                                    <div class="input-group">
                                        <input type="text"
                                            value="{{ \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}"
                                            class="form-control round" name="openingDate" id="openingDate" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Product Type</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round"
                                            value="{{ $savingsProduct->productType }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title">Collection Frequency</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="collectionFrequency"
                                            value="{{ $savingsProduct->collectionFrequency }}" readonly
                                            placeholder="Collection Frequency">
                                    </div>
                                </div>

                                {{-- regular product --}}
                                @if ($savingsProduct->productTypeId == 1)
                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Interest Rate (%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="interestRates[]"
                                            value="{{ isset($savingsProduct->interestRate) ? ($savingsProduct->interestRate) : '' }}"
                                            readonly placeholder="Interest Rate (%)">
                                    </div>
                                </div>

                                <div class="col-lg-2 form-group">
                                    <label class="input-title RequiredStar">Auto Process Amount</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textAmount" name="savingsAmounts[]"
                                            required placeholder="Enter Auto Process Amount">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                                @endif

                                {{-- end regular product --}}

                                {{-- one time product --}}

                                @if ($savingsProduct->productTypeId == 2)
                                <div class="col-lg-2 form-group">
                                    <label class="input-title RequiredStar">Mature Period (Month)</label>
                                    <div class="input-group">
                                        <select name="maturePeriods[]" class="form-control clsSelect2 maturePeriods"
                                            required>
                                            <option value="">Select</option>
                                            @foreach ($savingsProduct->interestRates as $rate => $period)
                                            <option value="{{$period}}" interestRate="{{ $rate }}">
                                                {{ $period . ' Months' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Interest Rate (%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round interestRate"
                                            name="interestRates[]" readonly placeholder="Interest Rate (%)">
                                    </div>
                                </div>

                                <div class="col-lg-2 form-group">
                                    <label class="input-title RequiredStar">Fixed Deposit Amount</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textAmount fixedDepositAmount"
                                            name="savingsAmounts[]" placeholder="Enter Fixed Deposit Amount" required>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-2 form-group">
                                    <label class="input-title RequiredStar">Payable Amount</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round payableAmount" name="payableAmount"
                                            readonly placeholder="Payable Amount">
                                    </div>
                                </div>

                                <div class="col-lg-3 form-group">
                                    <label class="input-title RequiredStar">Deposit By</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2 transactionTypeId"
                                            name="transactionTypeIds[]" required data-error="Please Select Deposit By">
                                            <option value="1">Cash</option>
                                            <option value="2">Bank</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group bankDiv" style="display: none;">
                                    <label class="input-title RequiredStar">Bank Account</label>
                                    <div class="input-group">
                                        <select class="form-control clsSelect2 clsLedgerID" name="ledgerIds[]"
                                            data-error="Please Select Bank Account">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>

                                <div class="col-lg-3 form-group bankDiv" style="display: none;">
                                    <label class="input-title RequiredStar">Cheque No</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round" name="chequeNos[]"
                                            placeholder="Cheque No">
                                    </div>
                                </div>

                                @endif

                                {{-- end one time product --}}


                            </div>

                            @endforeach

                            <div class="form-row form-group d-flex justify-content-center">
                                <div class="example example-buttons">
                                    <a href="javascript:void(0)" id="previousButtonSavings"
                                        class="btn btn-default btn-round">Previous</a>

                                    <a href="javascript:void(0)" id="nextButtonSavings"
                                        class="btn btn-primary btn-round">Next</a>
                                </div>
                            </div>

                        </div>
                    </div>


                    <!-- End Savings -->

                </div>
            </div>
        </div>
        @endif
        <!--  tab pane 3 (Photo) -->
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
                                                <img id="profileImagePreview"
                                                    src="{{ asset('assets/images/dummy.png') }}"
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
                                                    <img id="signatureImagePreview"
                                                        src="{{ asset('assets/images/dummy.png') }}"
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
                                            <button type="submit" class="btn btn-primary btn-round">Save</button>
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
    // set the default values
    if ("{{ isRequired('firstEvidenceIssuerCountryId') }}" == 'required') {
        $('select[name="firstEvidenceIssuerCountryId"]').val("{{ $mfnGnlConfig->defaultCountryId }}");
    }   
    if ("{{ isRequired('nationalityId') }}" == 'required') {
        $('#nationalityId').val("{{ $mfnGnlConfig->defaultCountryId }}");
    }   

    // take clone of the following divs to append later.
    var nomineeRow = $('.nomineeRow').clone();
    var referenceRow = $('.referenceRow').clone();

    $(document).ready(function () {


        var mandatorySavingProducts = '<?php echo count($mandatorySavingProducts)?>';

        // fix select box width
        $('.clsSelect2').width("100%");

        /* deposit type */
        $(".transactionTypeId").change(function (e) {
            if ($(this).val() == 2) {

                var accTypeID = 5;
                var selected = null;
                $.ajax({
                    type: "POST",
                    url: "../getBankLedgerId",
                    data: {
                        accTypeID: accTypeID,
                        selected: selected,
                    },
                    dataType: "text",
                    success: function (data) {
                        $(".clsLedgerID").html(data);

                        //   console.log(data);


                    },
                    error: function () {
                        alert('error!');
                    }
                });


                $(this).parents().eq(2).find(".bankDiv").show('slow');
            } else {
                // $(".bankDiv").hide('slow');
                $(this).parents().eq(2).find(".bankDiv").hide('slow');
            }
        });
        /* end deposit type */

        $('form').submit(function (event) {
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    contentType: false,
                    data: new FormData(this),
                    processData: false,
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
                            window.location.href = "./";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                });
        });


        $("#samityId").change(function (event) {
            $("#memberCode").val('');
            $("#gender option:gt(0)").remove();

            if ($(this).val() != '') {
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

                        $.each(data.genders, function (index, value) {
                            $("#gender").append("<option value=" + index + ">" + value +
                                "</option>");
                        });

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
            $(this).parents().eq(3).find('.interestRate').val($(".maturePeriods option:selected").attr(
                'interestRate'));
        });

        $('#savingsTab').click(function (e) {
            e.preventDefault();

            var numberOfEmptyFields = $('#details').find('input[required], select[required]').filter(
                function () {
                    if ($(this).val() === "") {
                        console.log($(this).attr("name"));
                    }                    
                    return $(this).val() === "";
                }).length;

                console.log(numberOfEmptyFields);

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
            if (mandatorySavingProducts > 0)
                $("#savingsTab").trigger('click');
            else
                $("#photoTab").trigger('click');
        });

        // Next button in Savings Tab
        $("#nextButtonSavings").click(function (e) {
            e.preventDefault();
            $("#photoTab").trigger('click');
        });

        // Previous button in Savings Tab
        $("#previousButtonSavings").click(function (e) {
            e.preventDefault();
            $("#initialTab").trigger('click');
        });

        // When Photo div's Previous button is clicked,go to savings if not empty, otherwise Initial
        $("#previousButton").click(function (e) {
            e.preventDefault();
            if (mandatorySavingProducts > 0)
                $("#savingsTab").trigger('click');
            else
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

            if ($(this).is(":checked")) {
                $(".permanentAddressDiv").css('pointer-events', 'none');

                cloneSelect('preDivisionId', 'perDivisionId');
                cloneSelect('preDistrictId', 'perDistrictId');
                cloneSelect('preUpazilaId', 'perUpazilaId');
                cloneSelect('preUnionId', 'perUnionId');
                cloneSelect('preVillageId', 'perVillageId');
                $('textarea[name="perStreetHolding"]').val($('textarea[name="preStreetHolding"]').val());

            } else {
                $(".permanentAddressDiv").css('pointer-events', '');
            }
        });
        /* end when click same as present address */

        /* clone select options */
        function cloneSelect(sourceName, targetName) {
            var source = $("select[name='"+sourceName+"']");
            var target = $("select[name='"+targetName+"']");

            target.empty();

            $("select[name='"+sourceName+"'] > option").each(function()
                {
                    target.append("<option value="+this.value+">"+this.text+"</option>");
                });
                target.val(source.val());
        }
        /* end cloning select options */

        /* calculate payable amount */
        $('.fixedDepositAmount').on('input', function () {
            var onetimeDepositAmount = 0;
            var interestRate = 0;
            var month = 0;

            var month = $(this).parents().eq(2).find('.maturePeriods').val();
            // alert(period);
            // return false;

            if ($(this).val() == '' || month == '') {
                $(this).parents().eq(2).find('.payableAmount').val('');
                return false;
            }

            if ($(this).val() != '') {
                onetimeDepositAmount = parseFloat($(this).val());
            }
            if (month != '') {
                interestRate = parseFloat($(this).parents().eq(2).find('.interestRate').val());
            }

            month = month / 12;

            var payableAmount = onetimeDepositAmount + Math.round(month * (interestRate / 100) *
                onetimeDepositAmount);

            $(this).parents().eq(2).find('.payableAmount').val(payableAmount)

        });
        /* end calculate payable amount */

        $('.maturePeriods').change(function (e) {
            $(this).parents().eq(2).find('.fixedDepositAmount').trigger('input');
        });

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
</script>


@endsection