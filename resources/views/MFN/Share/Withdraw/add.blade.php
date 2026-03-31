@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
autocomplete="off">

    @csrf
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="memberId" id="memberId" required
                            data-error="Please Select Member">
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>      

            {{-- <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Savings Account</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="accountId" id="accountId" required
                            data-error="Please Select Savings Account">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div> --}}
            <input type="hidden" class="form-control" name="accountId"  id="accountId" readonly>
            {{-- <input type="text" class="form-control" id="share_have" value="0" readonly> --}}
            {{-- <select class="form-control" name="accountId" id="accountId" required
                            data-error="Please Select Savings Account">
                            <option value="">Select</option>
                        </select> --}}
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Withdraw Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="date" value="{{ \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Share Current Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="unitPrice" id="unitPrice"
                            readonly>
                    </div>
                </div>
            </div>
           
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Number Of Shares</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="numberOfShare" id="numberOfShare" required >
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Total Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="totalPrice" id="totalPrice" value="0.00"
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
                            <input type="text" class="form-control" name="chequeNo">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-3 align-items-left">
            <table class="table table-bordered table-striped">
                <tr>
                    <td>Member Info</td>
                    <td class="memberinfo_cls"></td>
                </tr>
                <tr>
                    <td>Number of share</td>
                    <td class="share_have_cls"></td>
                </tr>
                <tr>
                    <td>Unit Price</td>
                    <td class="unit_price_cls"></td>
                </tr>
                <tr>
                    <td>Total Value</td>
                    <td class="total_value_cls"></td>
                </tr>
               
                
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



<link rel="stylesheet" href="{{asset('assets/css/selectize.bootstrap3.min.css')}}">
<script src="{{asset('assets/js/selectize.min.js')}}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }
    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
</style>

<script type="text/javascript">
    // initialize some variables
  

    $(document).ready(function () {        

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
                            window.location = './'
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


        /* member selectize */

        function selectizeMember(options){
            // console.log(options);
            
            $('#memberId').selectize({
                valueField: 'id',
                labelField: 'member',
                searchField: ['name', 'memberCode'],
                sortField: [{
                    field: "memberCode",
                    direction: "asc"
                }],
                // sortDirection: 'asc',
                highlight: true,
                allowEmptyOption: true,
                maxItems: 1,
                //only using options-value in jsfiddle - real world it's using the load-function
                options: options ,
                create: false,
                render: {
                    option: function (member, escape) {
                        return '<div>' +
                            '<span class="lebel">' + member.name + ' - ' + member.memberCode + '</span> <br>' +
                            '<span>Branch: ' + member.branch + '</span> <br>' +
                            '<span>Samity: ' + member.samity + '</span> <br>' +
                            '<span>Working Area: ' + member.workingArea + '</span> ' +
                            '</div>';

                    }
                }
            });
        }

        selectizeMember(@php echo json_encode($members) @endphp);
        /* end member selectize */

        /* get savings accounts */
        $("#memberId").change(function (e) {
            

            var memberId = $(this).val();

            if(memberId == ''){
                return false;
            }
            $('.memberinfo_cls').html($(this).html());
            $.ajax({
                type: "POST",
                url: "./getData",
                data: {context : 'member', memberId : memberId},
                dataType: "json",
                success: function (response) {

                    console.log(response);
                    $('#accountId').val(response.account.id);
                    $('#unitPrice').val(response.account.unitPrice);
                    // $('#share_have').val(response.account.numberOfShare);
                    $('.share_have_cls').html(response.account.numberOfShare);
                    $('.unit_price_cls').html(response.account.unitPrice);
                    $('.total_value_cls').html(response.account.totalPrice);

               
                },
                error: function(){
                    alert('error!');
                }
            });
        });
        /* end get saving accounts */

  

        /* withdraw type */
        $("#transactionTypeId").change(function (e) { 
            if ($(this).val() == 2) {

                var accTypeID = 5; 
                var selected = null; 
                    $.ajax({
                        type: "POST",
                        url: "../getBankLedgerId",
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
        /* end withdraw type */


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
  
        /* end calculate balance information */


    });

</script>


@endsection
