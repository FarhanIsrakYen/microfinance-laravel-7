@extends('Layouts.erp_master_full_width')
@section('content')
<div class="panel">
    <div class="panel-body">
        <form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
            novalidate="true" id="filterFormId" autocomplete="off">
            @csrf
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Auto Process</h3>
                </div>
            </div>
            <input type="hidden" class="form-control" name="samityId"
                value="{{ $samityId }}" readonly>
            <input type="hidden" class="form-control" name="autoProcessDate"
                value="{{ date('Y-m-d', strtotime($autoProcessDate)) }}" readonly>
            <div class="table-responsive" id="data">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="5">Member Information</th>
                            <th class="text-center" colspan="8">Loan Information</th>
                            <th class="text-center" colspan="7">Savings Information</th>
                        </tr>
                        <tr>

                            {{-- member info  --}}
                            <th style="width: 3%;" class="text-center">SL</th>
                            <th class="text-center">Member ID</th>
                            <th class="text-center">Member Name</th>
                            <th class="text-center">Spouse Name</th>
                            <th class="text-center"> Is Present </th>
                            {{-- loan info --}}
                            <th class="text-center">Loan ID</th>
                            <th class="text-center">Installment Amount</th>
                            <th class="text-center">Due</th>
                            <th class="text-center">Advance</th>
                            <th class="text-center">Full</th>
                            <th class="text-center">Partial</th>
                            <th class="text-center">Zero</th>
                            <th class="text-center">Amount</th>
                            {{-- savings info --}}
                            <th class="text-center">Savings ID</th>
                            <th class="text-center">Auto Process Amount</th>
                            <th class="text-center">Full</th>
                            <th class="text-center">Partial</th>
                            <th class="text-center">Zero</th>
                            <th class="text-center">Amount</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            {{-- member info  --}}
                            <th style="width: 3%;" class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"><input type="checkbox" id="mem_present_all" checked /></th>
                            {{-- loan info --}}
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"><input type="checkbox" id="loan_full_all" class="loan_group"
                                    checked /></th>
                            <th class="text-center"><input type="checkbox" id="loan_partial_all" class="loan_group" />
                            </th>
                            <th class="text-center"><input type="checkbox" id="loan_zero_all" class="loan_group" /></th>
                            <th class="text-center"></th>
                            {{-- savings info --}}
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th class="text-center"><input type="checkbox" id="sav_full_all" class="sav_group"
                                    checked /></th>
                            <th class="text-center"><input type="checkbox" id="sav_partial_all" class="sav_group" />
                            </th>
                            <th class="text-center"><input type="checkbox" id="sav_zero_all" class="sav_group" /></th>
                            <th class="text-center"></th>

                        </tr>

                        @php
                        $slNo = 1;
                        @endphp
                        @foreach ($members as $member)
                        @php
                        $memLoans = $loans->where('memberId', $member->id)->values();
                        $memSavAccs = $savAccounts->where('memberId', $member->id)->values();
                        if (count($memLoans) + count($memSavAccs) == 0) {
                        continue;
                        }
                        $rowSpan = max(count($memLoans), count($memSavAccs));
                        @endphp
                        @for ($i = 0; $i < $rowSpan; $i++) <tr>
                            {{-- Member --}}
                            @if ($i == 0)
                            <td rowspan="{{ $rowSpan }}">{{ $slNo++ }}</td>
                            <td rowspan="{{ $rowSpan }}">{{ $member->memberCode }}</td>
                            <td rowspan="{{ $rowSpan }}">{{ $member->name }}</td>
                            <td rowspan="{{ $rowSpan }}">{{ $member->spouseName }}</td>
                            <td rowspan="{{ $rowSpan }}" class="text-center">
                                <input type="hidden" name="memIds[]"  value="{{ $member->id }}" />
                                <input type="hidden" name="memPresents[]"  value="1" />
                                <input type="checkbox" class="memPresent" checked />
                            </td>
                            @endif

                            {{-- Loan --}}
                            @if (isset($memLoans[$i]))
                            <td>{{ $memLoans[$i]->loanCode }}</td>
                            <td>{{ $memLoans[$i]->installmentAmount }}</td>
                            <td>{{ $memLoans[$i]->dueAmount }}</td>
                            <td>{{ $memLoans[$i]->advanceAmount }}</td>                            
                            <td class="text-center">
                                <input type="checkbox" class="loan_full loan_group_child" checked />
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="loan_partial loan_group_child" />
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="loan_zero loan_group_child" />
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="loanAccIds[]" value="{{ $memLoans[$i]->id }}">
                                <input type="text" name="loanCollectionAmounts[]" installmentAmount="{{ (int) $memLoans[$i]->installmentAmount }}"
                                    value="{{ (int) $memLoans[$i]->installmentAmount }}" class="textNumber" readonly />
                            </td>
                            @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @endif

                            {{-- Savings --}}
                            @if (isset($memSavAccs[$i]))
                            <td>{{ @$memSavAccs[$i]->accountCode }}</td>
                            <td>{{ @$memSavAccs[$i]->autoProcessAmount	}}</td>
                            <td class="text-center">
                                <input type="checkbox" class="sav_full sav_group_child" checked />
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="sav_partial sav_group_child" />
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="sav_zero sav_group_child" />
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="savAccIds[]" value="{{ $memSavAccs[$i]->id }}">
                                <input type="text" name="savDepositAmounts[]" autoProcessAmount="{{ (int) $memSavAccs[$i]->autoProcessAmount }}"
                                    value="{{ (int) $memSavAccs[$i]->autoProcessAmount }}" class="textNumber" readonly />
                            </td>
                            @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @endif
                            
                            </tr>
                            @endfor
                            @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button class="btn btn-primary btn-round" id="preview">Preview And Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal savings -->
<div class="modal" id="modal">
    <div class="modal-dialog" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="savingsLongTitle">Confirm And Save</h5>

            </div>
            <div class="modal-body" id="modal_body" style="pointer-events: none;">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit">Save changes</button>
            </div>
        </div>
    </div>
</div>

<style>
    .textNumber{
        text-align: right;
    }
</style>

<script>
    $(document).ready(function () {

        $('#preview').on('click', function (e) {

            e.preventDefault();
            $('#modal_body').html($('#data').clone());
            $("#modal").modal();

        });
        $('#submit').on('click', function () {
            $('#filterFormId').submit();
        });

        var checkAllParentChild = [];
        checkAllParentChild['mem_present_all'] = 'memPresent';
        checkAllParentChild['loan_full_all'] = 'loan_full';
        checkAllParentChild['loan_partial_all'] = 'loan_partial';
        checkAllParentChild['loan_zero_all'] = 'loan_zero';
        checkAllParentChild['sav_full_all'] = 'sav_full';
        checkAllParentChild['sav_partial_all'] = 'sav_partial';
        checkAllParentChild['sav_zero_all'] = 'sav_zero';

        for (var index in checkAllParentChild) {
            checkUncheckAll(index, checkAllParentChild[index]);
        }

        function checkUncheckAll(parentId, childClassName) {

            // parent change event
            $('#' + parentId).change(function () {
                if ($(this).hasClass("loan_group")) {
                    if ($(this).is(':checked')) {
                        $('.loan_group').not($(this)).prop('checked', false).trigger('change');
                    } else {
                        if ($('.loan_group:checked').length == 0) {
                            $(this).prop('checked', true);
                            $('.loan_group').not($(this)).prop('checked', false).trigger('change');
                        }
                    }
                }
                if ($(this).hasClass("sav_group")) {
                    if ($(this).is(':checked')) {
                        $('.sav_group').not($(this)).prop('checked', false).trigger('change');
                    } else {
                        if ($('.sav_group:checked').length == 0) {
                            $(this).prop('checked', true);
                            // $('.sav_group').not($(this)).prop('checked', false).trigger('change');
                        }
                    }
                }

                if ($('#' + parentId).is(':checked')) {
                    $('.' + childClassName).prop('checked', true);
                } else {
                    $('.' + childClassName).prop('checked', false);
                }

                // triger child
                $('.' + childClassName).trigger('change');
            });

            // child change event
            $('.' + childClassName).change(function () {

                // for member
                if ($(this).hasClass("memPresent")) {
                    if ($(this).is(':checked')) {
                        $(this).closest('td').find('input[name="memPresents[]"]').val(1);
                    }
                    else{
                        $(this).closest('td').find('input[name="memPresents[]"]').val(0);
                    }
                }

                // for loan
                if ($(this).hasClass("loan_group_child")) {
                    if ($(this).is(':checked')) {
                        $(this).closest('tr').find('.loan_group_child').not($(this)).prop('checked',
                            false).trigger('change');

                        // set collectionAmount value
                        var collectionAmount = $(this).closest('tr').find('input[name="loanCollectionAmounts[]"]');
                        if ($(this).hasClass('loan_full')) {
                            collectionAmount.val(collectionAmount.attr('installmentAmount')).prop('readonly', true);
                        }
                        else if($(this).hasClass('loan_zero')){
                            collectionAmount.val(0).prop('readonly', true);
                        }
                        else{
                            collectionAmount.val('').prop('readonly', false);
                        }
                    } else {
                        if ($(this).closest('tr').find('.loan_group_child:checked').length == 0) {
                            $(this).prop('checked', true);
                        }
                    }
                }

                // for savings
                if ($(this).hasClass("sav_group_child")) {
                    if ($(this).is(':checked')) {
                        $(this).closest('tr').find('.sav_group_child').not($(this)).prop('checked',
                            false).trigger('change');

                        // set savDepositAmount value
                        var savDepositAmount = $(this).closest('tr').find('input[name="savDepositAmounts[]"]');
                        if ($(this).hasClass('sav_full')) {
                            savDepositAmount.val(savDepositAmount.attr('autoProcessAmount')).prop('readonly', true);
                        }
                        else if($(this).hasClass('sav_zero')){
                            savDepositAmount.val(0).prop('readonly', true);
                        }
                        else{
                            savDepositAmount.val('').prop('readonly', false);
                        }
                    } else {
                        if ($(this).closest('tr').find('.sav_group_child:checked').length == 0) {
                            $(this).prop('checked', true);
                        }
                    }
                }

                // update parent
                if ($(this).is(':checked')) {
                    if ($('.' + childClassName + ':checked').length == $('.' + childClassName)
                        .length) {
                        $('#' + parentId).prop('checked', true);
                    }
                } else {
                    $('#' + parentId).prop('checked', false);
                }
            });
        }

        /* form submit */
        $('form').submit(function (event) {
            event.preventDefault();
            // $('#submit').attr('disabled', 'disabled');
        
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
                $('#submit').prop('disabled', false);
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
        /* end form submit */

    }); /* end ready */

</script>

@endsection