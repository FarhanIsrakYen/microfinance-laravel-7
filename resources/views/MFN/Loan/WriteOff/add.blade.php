@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <input type="hidden" name="branchId" value="{{ $branchId }}">
    <input type="hidden" name="samityId" value="{{ $samityId }}">
    <input type="hidden" name="memberId" value="{{ $memberId }}">
    <input type="hidden" name="loanId" value="{{ $loanId }}">

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $memberName }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>      

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Loan Id</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $loanCode }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Write Off Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="writeOffDate" value="{{ Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Principal Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="principalAmount" id="principalAmount" value="{{ $principalAmount }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Interest Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="interestAmount" id="interestAmount" value="{{ $interestAmount }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="amount" id="amount" 
                        value="{{ $amount }}" readonly>
                    </div>
                </div>
            </div>

            {{-- <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Write Off Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="interestAmount" id="interestAmount" value="{{ $principalAmount }}" required data-error="Please give Amount" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Rebate Amount</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="rebateAmount" id="rebateAmount" value="{{ $interestAmount }}" required data-error="Please give Amount">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> 
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Is Death?</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="branchId" id="branchId" required
                            data-error="Please Select Branch">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> --}}

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Notes</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <textarea class="form-control" name="note" id="note" rows="2"></textarea>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 align-items-left">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>LOAN</th>
                        <th>TOTAL</th>
                        <th>PRICIPAL</th>
                        <th>INTEREST</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Payable</td>
                        <td class="savInfo text-right" id="total_payable">{{ $total_payable }}</td>
                        <td class="savInfo text-right" id="pricipal_payable">{{ $pricipal_payable }}</td>
                        <td class="savInfo text-right" id="interest_payable">{{ $interest_payable }}</td>
                    </tr>
                    <tr>
                        <td>Teansaction</td>
                        <td class="savInfo text-right" id="total_trans">{{ $total_trans }}</td>
                        <td class="savInfo text-right" id="pricipal_trans">{{ $pricipal_trans }}</td>
                        <td class="savInfo text-right" id="interest_trans">{{ $interest_trans }}</td>
                    </tr>
                    <tr>
                        <td>Outstanding</td>
                        <td class="savInfo text-right" id="total_outstanding">{{ $total_outstanding }}</td>
                        <td class="savInfo text-right" id="pricipal_outstanding">{{ $pricipal_outstanding }}</td>
                        <td class="savInfo text-right" id="interest_outstanding">{{ $interest_outstanding }}</td>
                    </tr>
                    <tr>
                        <td>Installment</td>
                        <td class="savInfo text-right" id="total_installment">{{ $total_installment }}</td>
                        <td class="savInfo text-right" id="pricipal_installment">{{ $pricipal_installment }}</td>
                        <td class="savInfo text-right" id="interest_installment">{{ $interest_installment }}</td>
                    </tr>
                    <tr>
                        <td>Advance</td>
                        <td class="savInfo text-right" id="total_advance">{{ $total_advance }}</td>
                        <td class="savInfo text-right" id="pricipal_advance">{{ $pricipal_advance }}</td>
                        <td class="savInfo text-right" id="interest_advance">{{ $interest_advance }}</td>
                    </tr>
                    <tr>
                        <td>Due</td>
                        <td class="savInfo text-right" id="total_due">{{ $total_due }}</td>
                        <td class="savInfo text-right" id="pricipal_due">{{ $pricipal_due }}</td>
                        <td class="savInfo text-right" id="interest_due">{{ $interest_due }}</td>
                    </tr>
                </tbody>               
            </table>
        </div>

        <div class="col-lg-1"></div>
    </div>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>

    // Disable Multiple Click with submit form
    $('form').submit(function (event) {
        event.preventDefault();

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
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    });

                    setTimeout(function () {
                        window.location = './../'
                    }, 3000);
                }

            })
            .fail(function () {
                console.log("error");
            })
            .always(function () {
                console.log("complete");
            });

    });
    

</script>

@endsection
