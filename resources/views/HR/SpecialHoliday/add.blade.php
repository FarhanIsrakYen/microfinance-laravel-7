@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<!-- Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Applicable For</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="ApplicationFor" checked="checked" name="sh_app_for" value="org">
                            <label for="checkGroup">Organization &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary">
                            <input type="radio" class="ApplicationFor" name="sh_app_for" value="branch">
                            <label for="checkBranch">Branch &nbsp &nbsp </label>
                        </div>
                        <div class="radio-custom radio-primary" style="display: none">
                            <input type="radio" class="ApplicationFor" name="sh_app_for" value="somity">
                            <label for="checkSomity">Somity &nbsp &nbsp </label>
                        </div>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center desc" id="org" style="display: none;">
                <label class="col-lg-3 input-title RequiredStar">Organization</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="company_id" id="company_id" required
                            data-error="Select Organization">
                            <option value="" selected="selected">Select Organization</option>
                            @foreach ($CompanyData as $Comp)
                            <option value="{{$Comp->id}}" @if(Common::getCompanyId()==$Comp->id) selected @endif>
                                {{ printf("%04d", $Comp->comp_code)."-".$Comp->comp_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center desc" id="branch" style="display: none;">
                <label class="col-lg-3 input-title RequiredStar">Branch</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id"
                            data-error="Select Branch" style="width: 100%">
                            <option value="" selected="selected">Select Branch</option>
                            @foreach ($BranchData as $Branch)
                            @if($Branch->is_approve == 1)
                            <option value="{{$Branch->id}}" @if(Common::getBranchId()==$Branch->id) selected @endif>
                                {{ printf("%04d", $Branch->branch_code)."-".$Branch->branch_name }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center desc" id="somity" style="display: none;">
                <label class="col-lg-3 input-title RequiredStar">Somity</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="somity_id" id="somity_id"
                            data-error="Select Somity">
                            <option value="" selected="selected">Select Somity</option>

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Holiday Title</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Holiday Title" name="sh_title"
                            id="sh_title" required data-error="Please enter Holiday Title.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center ">
                <label class="col-lg-3 input-title RequiredStar">Holiday Date From</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepickerNotRange" id="sh_date_from"
                            name="sh_date_from" placeholder="DD-MM-YYYY" required data-error="Select Date"
                            onchange="fnCheckDayEnd();">
                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="form-row align-items-center ">
                <label class="col-lg-3 input-title RequiredStar">Holiday Date To</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepickerNotRange" id="sh_date_to"
                            name="sh_date_to" placeholder="DD-MM-YYYY" required data-error="Select Date"
                            onchange="fnCheckDayEnd();">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Description</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <textarea class="form-control round" id="sh_description" name="sh_description" rows="2"
                            placeholder="Enter Description"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
<!-- End Page -->

<script type="text/javascript">
    // Change views of dropdown box(Org,branch,Samity)
    $(document).ready(function () {

        if ($('.ApplicationFor').is(':checked')) {
            var idTxt = $('.ApplicationFor:checked').val();
            $('#' + idTxt).show();
        }

        $(".ApplicationFor").click(function () {
            var selIdTxt = $(this).val();

            $('.ApplicationFor').each(function () {

                if (selIdTxt === $(this).val()) {
                    $("#" + $(this).val()).show();
                } else {
                    $("#" + $(this).val()).hide();
                }
            });
        });
        $('form').submit(function (event) {
            $(this).find(':submit').attr('disabled', 'disabled');
        });
    });
    //check Day End value on database in real time

    function fnCheckDayEnd() {
        var startDateFrom = $('#sh_date_from').val();
        var startDateTo = $('#sh_date_to').val();

        if (startDateFrom != '' && startDateTo != '') {
            $.ajax({
                type: "get",
                url: "{{url('hr/specialholiday/CheckDayEnd')}}",
                data: {
                    startDateFrom: startDateFrom,
                    startDateTo: startDateTo
                },
                dataType: "json",
                success: function (data) {

                    if (data.Table == "DayEnd") {
                        swal({
                            title: "Day end exist, please select another day !!",
                            icon: "error",
                        });

                        $('#sh_date_from').val('');
                        $('#sh_date_to').val('');

                    } else if (data.Table == "Holiday") {
                        swal({
                            title: "Holiday exist, please select another day !!",
                            icon: "error",
                        });

                        $('#sh_date_from').val('');
                        $('#sh_date_to').val('');
                    }
                }
            });
        }
    }

</script>

@endsection
