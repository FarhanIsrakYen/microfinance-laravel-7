@extends('Layouts.erp_master')
@section('content')
<style type="text/css">
    .customDivWidth {
        width: 10%;
    }

    .input-group-text {
        padding: 8px;
    }

</style>
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-12">

            <input type="hidden" name="productId" value="{{ encrypt($product->id) }}">

            <div class="form-row form-group align-items-center">
                <div class="col-lg-3"></div>
                <label class="col-lg-2 input-title RequiredStar">Product</label>
                <div class="col-lg-4">
                    <div class="input-group">
                        <input type="text" class="form-control round"
                            value="{{ $product->productCode.' - '.$product->name }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            @if ($product->productTypeId == 1) {{-- if product type is regular --}}
            <div class="form-row form-group align-items-center">
                <div class="col-lg-3"></div>
                <label class="col-lg-2 input-title RequiredStar">Interest Rate</label>
                <div class="col-lg-4">
                    <div class="input-group">
                        <input type="text" name="interestRate" class="form-control round textAmount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <div class="col-lg-3"></div>
                <label class="col-lg-2 input-title RequiredStar">Effective Date</label>
                <div class="col-lg-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" name="effectiveDate" class="form-control round datepicker-custom">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            @elseif($product->productTypeId == 2) {{-- if product type is fixed deposit --}}
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">
                    FDR Interest
                    <a href="javascript:void(0);" class="float-right addFDRInterest blue-grey-700">
                        <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                        <span style="font-size:13px;">Add</span>
                    </a>
                </div>
                <div class="panel-body">
                    @php
                    $matureInterests = $activeInterestRates->where('parentId', 0);
                    @endphp

                    @foreach ($matureInterests as $matureInterest)
                    <div class="card" style="border: 1px solid rgba(0,0,0,.125)">
                        <div class="card-body maturePeriodDiv">
                            <div class="row">
                                <div class="checkbox-custom checkbox-primary mt-4">
                                    <input type="checkbox" name="" class="matureCheck">
                                    <label></label>
                                </div>
                                <div class="customDivWidth form-group ml-4">
                                    <label class="input-title">Mature Period</label>
                                    <div class="input-group">
                                        <input type="text" class="maturePeriod form-control round textNumber"
                                            name="maturePeriod[]" placeholder="Enter Month"
                                            value="{{ $matureInterest->durationMonth }}" objectId="{{ $matureInterest->id }}" readonly>
                                    </div>
                                    <input type="hidden" name="" value="{{ $matureInterest->durationMonth }}"
                                        class="matPer">
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Interest Rate(%)</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control round textAmount interestRateFDR edit_mat"
                                            name="interestRateFDR[]" placeholder="Enter Percentage"
                                            value="{{ $matureInterest->interestRate }}" readonly>
                                    </div>
                                    <input type="hidden" name="" value="{{ $matureInterest->interestRate }}"
                                        class="matInt">
                                </div>
                                <div class="customDivWidth form-group">
                                    <label class="input-title">FDR Amount</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control round textNumber matureFdrAmount"
                                            name="matureFdrAmount[]" placeholder="FDR Amount" readonly value="100000">
                                    </div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Total Repay Amount</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control round textAmount matureRepayAmount edit_mat"
                                            name="matureRepayAmount[]" placeholder="Enter Total Repay" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Effective Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round datepicker-custom"
                                            name="effectiveDateMature" placeholder="DD-MM-YYYY" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title"></label>
                                    <div class="input-group">
                                        <a href="javascript:void(0);"
                                            class="float-right addFDRSubInterest blue-grey-700">
                                            <i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>
                                            <span style="font-size:13px;">Add Partial Period</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="input-title"></label>
                                    <div class="input-group">
                                        <a href="javascript:void(0);" class="float-right disableMP blue-grey-700">
                                            <i class="fa fa-ban fa-lg" aria-hidden="true"></i>
                                            <span class="disableMat" style="font-size:13px;">Disable</span>
                                        </a>
                                    </div>
                                </div>

                            </div>
                            @php
                            $partials = $activeInterestRates->where('parentId', $matureInterest->id);
                            @endphp

                            {{-- partials --}}
                            @foreach ($partials as $partial)
                            <div class="row partialPeriodDiv">
                                <div class="checkbox-custom checkbox-primary mt-4">
                                    <input type="checkbox" name="" class="partialCheck">
                                    <label></label>
                                </div>
                                <div class="customDivWidth form-group" style="margin-left: 140px">
                                    <label class="input-title">Partial Period</label>
                                    <div class="input-group">
                                        <input type="text" class="partialPeriod form-control round textNumber edit_part"
                                            name="partialPeriod[]" placeholder="Enter Month"
                                            value="{{ $partial->durationMonth }}" objectId="{{ $partial->id }}" readonly>
                                    </div>
                                    <input type="hidden" name="" value="{{ $partial->durationMonth }}" class="partPer">
                                </div>
                                <div class="col-lg-2 form-group"><label class="input-title">Interest
                                        Percentage(%)</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control round textAmount partialRateFDR edit_part"
                                            name="partialRateFDR[]" placeholder="Enter Percentage"
                                            value="{{ $partial->interestRate }}" readonly>
                                    </div>
                                    <input type="hidden" name="" value="{{ $partial->interestRate }}" class="partInt">
                                </div>
                                <div class="col-lg-2 form-group">
                                    <label class="input-title">Effective Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round datepicker-custom"
                                            name="effectiveDatePartial[]" placeholder="DD-MM-YYYY" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="input-title"></label>
                                    <div class="input-group">
                                        <a href="javascript:void(0);"
                                            class="float-right disablePartialRow blue-grey-700">
                                            <i class="fa fa-ban fa-lg" aria-hidden="true"></i>
                                            <span style="font-size:13px;">&nbsp;<span class="disablePart">Disable</span>
                                                Partial Period</span>
                                        </a>
                                    </div>
                                </div>

                            </div>
                            @endforeach

                            {{-- end partials --}}
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
            @endif


            <div class="form-row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();

            var fdrArray = makeFdrArray();
            var maturePeriods = fdrArray['maturePeriodsArray'];
            var removedMaturePeriods = fdrArray['removedMatureArray'];
            var removedPartialPeriods = fdrArray['removedPartialArray'];

            maturePeriods = JSON.stringify(maturePeriods);
            removedMaturePeriods = JSON.stringify(removedMaturePeriods);
            removedPartialPeriods = JSON.stringify(removedPartialPeriods);

            console.log(fdrArray);
            // return false;
            

            // $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "./../add",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize() + "&maturePeriods=" + maturePeriods + "&removedMaturePeriods=" + removedMaturePeriods + "&removedPartialPeriods=" + removedPartialPeriods,
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
                            window.location.href = "./../../view/" + "{{ $product->id }}";
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

        function makeFdrArray() {

            var maturePeriodsArray = new Array();
            $.each($("[name^=maturePeriod]:not(.removed)"), function (index, el) {

                var maturePeriod = new Object();
                maturePeriod.month = $(el).val();

                maturePeriod.interestRate = $(el).parents().eq(2).find('.interestRateFDR').val();
                if ($(el).hasClass('modified')) {
                    maturePeriod.isModified = 1;
                    maturePeriod.effectiveDate = $(el).parents().eq(2).find('.datepicker-custom').val();
                } else {
                    maturePeriod.isModified = 0;
                }

                var partialArray = new Array();
                var partialPeriods = $(el).closest('.maturePeriodDiv').find(
                    ".partialPeriod:not(.removed)");
                $.each(partialPeriods, function (pIndex, pel) {
                    var partial = new Object();
                    partial.month = $(pel).val();
                    partial.interestRate = $(pel).parents().eq(2).find('.partialRateFDR').val();

                    if ($(pel).hasClass('modified')) {
                        partial.isModified = 1;
                        partial.effectiveDate = $(pel).parents().eq(2).find(
                            '.datepicker-custom').val();
                    } else {
                        partial.isModified = 0;
                    }

                    partialArray.push(partial);
                });

                maturePeriod.partials = partialArray;
                maturePeriodsArray.push(maturePeriod);
            });

            // get the periods which have been removed.

            var removedMatureArray = new Array();
            var removedPartialArray = new Array();

            $.each($(".removed[name^=maturePeriod]"), function (index, el) {

                var maturePeriod = new Object();
                maturePeriod.id = $(el).attr('objectId');
                maturePeriod.effectiveDate = $(el).parents().eq(2).find('.datepicker-custom').val();

                removedMatureArray.push(maturePeriod);
            });

            // get the partials which parents are not removed
            $.each($(".removed[name^=partialPeriod]"), function (index, el) {

                var isParentRemoved = $(el).closest('.maturePeriodDiv').find('.maturePeriod').hasClass('removed');

                if(isParentRemoved){
                    return;
                }

                var partialPeriod = new Object();
                partialPeriod.id = $(el).attr('objectId');
                partialPeriod.effectiveDate = $(el).parents().eq(2).find('.datepicker-custom').val();

                removedPartialArray.push(partialPeriod);
            });

            var data = new Array();
            data['maturePeriodsArray'] = maturePeriodsArray;
            data['removedMatureArray'] = removedMatureArray;
            data['removedPartialArray'] = removedPartialArray;

            return data;

        }

        // Mature Period and Interest Percentage editable if corresponding checkbox is checked
        $(".matureCheck").click(function () {
            if ($(this).is(':checked')) {
                $(this).closest('.maturePeriodDiv').find('.edit_mat').prop('readonly', false);

                $(this).closest('.maturePeriodDiv').find('.maturePeriod').addClass('modified');

                $(this).closest('.maturePeriodDiv').find('.datepicker-custom').prop('disabled', false);


            } else if ($(this).is(':not(:checked)')) {
                $(this).closest('.maturePeriodDiv').find('.edit_mat').prop('readonly', true);

                $(this).closest('.maturePeriodDiv').find('.datepicker-custom').val('');
                $(this).closest('.maturePeriodDiv').find('.datepicker-custom').prop('disabled', true);

                $(this).closest('.maturePeriodDiv').find('.maturePeriod').removeClass('modified');

                var maturePeriodValue = $(this).closest('.maturePeriodDiv').find('.matPer').val();
                $(this).closest('.maturePeriodDiv').find('.maturePeriod').val(maturePeriodValue);
                var matureInterestValue = $(this).closest('.maturePeriodDiv').find('.matInt').val();
                $(this).closest('.maturePeriodDiv').find('.interestRateFDR').val(matureInterestValue);
                $('.interestRateFDR,.partialRateFDR').trigger('keyup');
            }
        });

        // Mature Period and Interest Percentage editable if corresponding checkbox is checked
        $(".partialCheck").click(function () {
            if ($(this).is(':checked')) {
                $(this).closest('.partialPeriodDiv').find('.edit_part').prop('readonly', false);

                $(this).closest('.partialPeriodDiv').find('.partialPeriod').addClass('modified');

                $(this).closest('.partialPeriodDiv').find('.datepicker-custom').prop('disabled', false);

            } else if ($(this).is(':not(:checked)')) {
                $(this).closest('.partialPeriodDiv').find('.edit_part').prop('readonly', true);

                $(this).closest('.partialPeriodDiv').find('.partialPeriod').removeClass('modified');
                var partialPeriodValue = $(this).closest('.partialPeriodDiv').find('.partPer').val();
                $(this).closest('.partialPeriodDiv').find('.partialPeriod').val(partialPeriodValue);
                var partialInterestValue = $(this).closest('.partialPeriodDiv').find('.partInt').val();
                $(this).closest('.partialPeriodDiv').find('.partialRateFDR').val(partialInterestValue);
                $('.partialRateFDR').trigger('keyup');

                $(this).closest('.partialPeriodDiv').find('.datepicker-custom').val('');
                $(this).closest('.partialPeriodDiv').find('.datepicker-custom').prop('disabled', true);
            }
        });

        // Disable Partial Period Click Event
        function pa(el) {
            $(el).closest('.partialPeriodDiv').find('.disablePart').text('Enable');
            $(el).closest('.partialPeriodDiv').find('.fa').removeClass('fa-ban').addClass('fa-check');
            $(el).closest('.partialPeriodDiv').find('.partialPeriod').removeClass('modified').addClass(
                'removed');
            $(el).closest('.partialPeriodDiv').find('.edit_part').prop('readonly', true);
            $(el).closest('.partialPeriodDiv').find('.partialCheck').attr("disabled", true);
            $(el).closest('.partialPeriodDiv').css({
                "background": "#e4eaec"
            });
            if ($('.partialCheck').is(':checked')) {
                $(el).closest('.partialPeriodDiv').find('.partialCheck').prop('checked', false);
            }

            $(el).closest('.partialPeriodDiv').find('.datepicker-custom').prop('disabled', false);
        }

        function pb(el) {
            $(el).closest('.partialPeriodDiv').find('.disablePart').text('Disable');
            $(el).closest('.partialPeriodDiv').find('.fa').removeClass('fa-check').addClass('fa-ban');
            $(el).closest('.partialPeriodDiv').find('.partialCheck').attr("disabled", false);
            $(el).closest('.partialPeriodDiv').css({
                "background": "transparent"
            });

            $(el).closest('.partialPeriodDiv').find('.datepicker-custom').val('');
            $(el).closest('.partialPeriodDiv').find('.datepicker-custom').prop('disabled', true);
        }

        $(".disablePartialRow").click(function () {
            var el = this;
            var find = $(el).closest('.maturePeriodDiv').find('.disableMat').text();
            if (find == 'Enable') {
                $(this).prop('disabled', true);
            } else
                return (el.tog ^= 1) ? pa(el) : pb(el);
        });

        // Disable Mature Period Click Event
        function ma(el) {
            $(el).closest('.maturePeriodDiv').find('.disableMat').text('Enable');
            $(el).closest('.maturePeriodDiv').find(".fa").each(function () {
                $(this).removeClass('fa-ban').addClass('fa-check');
            });
            $(el).closest('.maturePeriodDiv').find('.maturePeriod,.partialPeriod')
                .removeClass('modified').addClass('removed');
            $(el).closest('.maturePeriodDiv').find('.edit_mat,.edit_part').prop('readonly', true);
            $(el).closest('.maturePeriodDiv').find('.partialCheck,.matureCheck').attr("disabled", true);
            $(el).closest('.maturePeriodDiv').find('.addFDRSubInterest,.removePartialRow').prop("disabled",
                true);            
            $(el).closest('.card').css({
                "background": "#e4eaec"
            });
            if ($('.matureCheck').is(':checked') || $('.partialCheck').is(':checked')) {
                $(el).closest('.maturePeriodDiv').find('.partialCheck,.matureCheck').prop('checked', false);
            }

            $(el).closest('.maturePeriodDiv').find(".partialPeriodDiv").each(function () {
                $(this).find('.disablePart').text('Enable');
                $(this).css({
                    "background": "transparent"
                });
            });

            $(el).closest('.maturePeriodDiv').find('.datepicker-custom').prop('disabled', false);
            $(el).closest('.maturePeriodDiv').find('.partialPeriodDiv').find('.datepicker-custom').val('');
            $(el).closest('.maturePeriodDiv').find('.partialPeriodDiv').find('.datepicker-custom').prop('disabled', true);

        }

        function mb(el) {
            $(el).closest('.maturePeriodDiv').find('.disableMat').text('Disable');
            $(el).closest('.maturePeriodDiv').find(".fa").each(function () {
                $(this).removeClass('fa-check').addClass('fa-ban');
            });
            $(el).closest('.maturePeriodDiv')
                .find('.edit_mat,.edit_part')
                .removeClass('removed');

            $(el).closest('.maturePeriodDiv').find('.partialCheck,.matureCheck').attr("disabled", false);
            $(el).closest('.maturePeriodDiv').find('.addFDRSubInterest,.removePartialRow').prop("disabled",
                false);
            $(el).closest('.card').css({
                "background": "transparent"
            });
            $(el).closest('.maturePeriodDiv').find(".partialPeriodDiv").each(function () {
                $(this).find('.disablePart').text('Disable');
            });
            $(el).closest('.maturePeriodDiv').find('.datepicker-custom').val('');
            $(el).closest('.maturePeriodDiv').find('.datepicker-custom').prop('disabled', true);
        }

        $(".disableMP").click(function () {
            var el = this;
            return (el.tog ^= 1) ? ma(el) : mb(el);
        });

        // Add Mature Period Click Event 
        $(document).on('click', '.addFDRInterest', function () {

            var html = '<div class="card" style="border: 1px solid rgba(0,0,0,.125)">';
            html += '<div class="card-body maturePeriodDiv">';
            html += '<div class="row">';
            html += '<div class="">';
            html += '</div>';
            html += '<div class="customDivWidth form-group" style="margin-left:35px">';
            html += '<label class="input-title">Mature Period</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="maturePeriod form-control round textNumber edit_mat modified" name="maturePeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Interest Percentage(%)</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round interestRateFDR edit_mat" name="interestRateFDR[]" placeholder="Enter Percentage">';
            html += '</div>';
            html += '</div>';

            html += '<div class="customDivWidth form-group">';
            html += '<label class="input-title">FRD Amount</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textNumber matureFdrAmount" name="matureFdrAmount[]" placeholder="FDR Amount" readonly value="100000">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Total Repay Amount</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textAmount matureRepayAmount" name="matureRepayAmount[]" placeholder="Enter Total Repay">';
            html += '</div>';
            html += '</div>';


            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Effective Date</label>';
            html += '<div class="input-group">';
            html += '<div class="input-group-prepend">';
            html += '<span class="input-group-text">';
            html += '<i class="icon wb-calendar round" aria-hidden="true"></i>';
            html += '</span>';
            html += '</div>';
            html +=
                '<input type="text" class="form-control round datepicker-custom" name="effectiveDateMature[]" placeholder="DD-MM-YYYY">';
            html += '</div>';
            html += '</div>';


            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html +=
                '<a href="javascript:void(0);" class="float-right addFDRSubInterest blue-grey-700">';
            html += '<i class="fa fa-plus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Add Partial Period</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';
            html += '<div class="form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html += '<a href="javascript:void(0);" class="float-right removeMatureRow blue-grey-700">';
            html += '<i class="fa fa-minus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Remove</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // $(".card:last").after(html);
            $(this).parents().eq(1).append(html);

            $('.datepicker-custom').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
            });

        });

        // Add Partial Period Click Event 
        $(document).on('click', '.addFDRSubInterest', function () {

            var html = '<div class="row partialPeriodDiv">';
            // html += '<div class="col-lg-1">';
            // html += '</div>';
            html += '<div class="customDivWidth  form-group" style="margin-left:160px">';
            html += '<label class="input-title">Partial Period</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="partialPeriod form-control round textNumber edit_part modified" name="partialPeriod[]" placeholder="Enter Month">';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Interest Percentage(%)</label>';
            html += '<div class="input-group">';
            html +=
                '<input type="text" class="form-control round textAmount partialRateFDR edit_part" name="partialRateFDR[]" placeholder="Enter Percentage">';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-lg-2 form-group">';
            html += '<label class="input-title">Effective Date</label>';
            html += '<div class="input-group">';
            html += '<div class="input-group-prepend">';
            html += '<span class="input-group-text">';
            html += '<i class="icon wb-calendar round" aria-hidden="true"></i>';
            html += '</span>';
            html += '</div>';
            html +=
                '<input type="text" class="form-control round datepicker-custom" name="effectiveDatePartial[]" placeholder="DD-MM-YYYY">';

            html += '</div>';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label class="input-title"></label>';
            html += '<div class="input-group">';
            html += '<a href="javascript:void(0);" class="float-right removePartialRow blue-grey-700">';
            html += '<i class="fa fa-minus-square fa-lg" aria-hidden="true"></i>';
            html += '<span style="font-size:13px;"> Remove</span>';
            html += '</a>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            $(this).parents().eq(3).append(html);

            $('.datepicker-custom').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
            });

        });

        $(document).on('click', '.removePartialRow', function () {

            $(this).parents().eq(2).remove();
        });

        $(document).on('click', '.removeMatureRow', function () {

            $(this).closest('.card').remove();

        });

        function fnCalcMatureRepay(el) {
            var month = $(el).closest('.maturePeriodDiv').find('.maturePeriod').val();
            if (month == '') {
                $(el).closest('.maturePeriodDiv').find('.matureRepayAmount').val('');
                return false;
            }
            var year = month / 12;

            var baseAmount = 100000;
            var repayAmount = '';
            if (el.val() != '') {
                var repayAmount = Math.round(baseAmount + (el.val() * baseAmount / 100 * year));
            }
            $(el).closest('.maturePeriodDiv').find('.matureRepayAmount').val(repayAmount);
        }

        function fnCalcMatureRate(el) {
            var month = $(el).closest('.maturePeriodDiv').find('.maturePeriod').val();
            if (month == '') {
                $(el).closest('.maturePeriodDiv').find('.interestRateFDR').val('');
                return false;
            }
            var year = month / 12;

            var baseAmount = 100000;
            var interestRate = '';
            if (el.val() != '') {
                var interestRate = (el.val() - baseAmount) * 100 / baseAmount / year;
            }
            $(el).closest('.maturePeriodDiv').find('.interestRateFDR').val(interestRate);
        }

        $(document).on('keyup', '.interestRateFDR', function () {
            fnCalcMatureRepay($(this));
        });

        $(document).on('keyup', '.matureRepayAmount', function () {
            fnCalcMatureRate($(this));
        });
        $(document).on('keyup', '.maturePeriod', function () {
            var element = $(this).closest('.maturePeriodDiv').find('.interestRateFDR');
            fnCalcMatureRepay(element);
        });

        $('.interestRateFDR,.partialRateFDR').trigger('keyup');

    });

</script>

@endsection
