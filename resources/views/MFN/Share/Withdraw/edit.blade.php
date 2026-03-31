@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
autocomplete="off">

    @csrf

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            <input type="hidden" name="id" value="{{ encrypt($withdraw->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $member }}" readonly>
                    </div>
                </div>
            </div>   
        <input type="hidden" class="form-control" name="accountId"  id="accountId" readonly value="{{$withdraw->accountId}}">
           
            
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Withdraw Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="date" value="{{ \Carbon\Carbon::parse($withdraw->withdrawDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Share Current Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="unitPrice" id="unitPrice" value="{{$withdraw->unitPrice}}"
                            readonly>
                    </div>
                </div>
            </div>
           
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Number Of Shares</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="numberOfShare" id="numberOfShare" required value="{{$withdraw->numberOfShare}}" >
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Total Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="totalPrice" id="totalPrice" value="{{$withdraw->totalPrice}}" 
                            readonly>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Transaction Type</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="transactionTypeId" id="transactionTypeId" required
                            data-error="Please Select Transaction Type">
                            <option value="1">Cash</option>
                            <option value="2">Bank</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div id="bankDiv" style="display: none;">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Bank Account</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control" name="ledgerId" id="ledgerId"
                                data-error="Please Select Bank Account">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
    
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Cheque No</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control" name="chequeNo" value="{{ $withdraw->chequeNo }}">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>            

            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                    class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-3 align-items-left">
            <table class="table table-bordered table-striped">
                <tr>
                    <td>Member Info</td>
                  <td class="memberinfo_cls">{{$member}}</td>
                </tr>
                <tr>
                    <td>Number of share</td>
                    <td class="share_have_cls">{{$account->numberOfShare}}</td>
                </tr>
                <tr>
                    <td>Unit Price</td>
                    <td class="unit_price_cls">{{$account->unitPrice}}</td>
                </tr>
                <tr>
                    <td>Total Value</td>
                    <td class="total_value_cls">{{$account->totalPrice}}</td>
                </tr>
                
            </table>
        </div>
    </div>

    </div>
    </div>
</form>


<script type="text/javascript">
    
    

    $(document).ready(function () {        

        // set default values
        $('#transactionTypeId').val({{ $withdraw->transactionTypeId }});
        // $("#transactionTypeId").trigger('change');
        $('#ledgerId').val({{ $withdraw->ledgerId }});

        // Disable Multiple Click
        $('form').submit(function (event) {
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');

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


        /* transaction type */
        $("#transactionTypeId").change(function (e) { 
            if ($(this).val() == 2) {
                var accTypeID = 5; 
                var selected = {{ $withdraw->ledgerId }}; 
                    $.ajax({
                        type: "POST",
                        url: "../../getBankLedgerId",
                        data: { 
                            accTypeID : accTypeID,
                            selected:selected,
                        },
                        dataType: "text",
                        success: function (data) {
                            $("#ledgerId").html(data);
                            
                        //   console.log(data);


                        },
                        error: function(){
                            alert('error!');
                        }
                    });
                $("#bankDiv").show('slow');
            }
            else{
                $("#bankDiv").hide('slow');
            }
        });
        $("#transactionTypeId").trigger('change');
        /* end transaction type */

    

        $('#numberOfShare').keyup(function() {
            var value = 0;
            if ($(this).val() != '') {

                // console.log($('.share_have_cls').html());
                if(Number($(this).val()) > Number($('.share_have_cls').html())){
                   
                    swal({
                            icon: 'warning',
                            title: 'Sorry',
                            text: 'You have total '+$('.share_have_cls').html()+' shares .',
                        });
                        $(this).val( $('.share_have_cls').html());  
                        value = (parseFloat($(this).val()) * parseFloat($('#unitPrice').val()));
                }else{
                    value = (parseFloat($(this).val()) * parseFloat($('#unitPrice').val()));
                }
                
              

            }

             $('#totalPrice').val(value.toFixed(2)); 
            // console.log(value);
        });


    });

</script>


@endsection
