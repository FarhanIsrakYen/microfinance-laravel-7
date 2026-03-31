@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <input type="hidden" name="id" value="{{ encrypt($ShareAcc->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $member }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Date</label>
                <div class="col-lg-5">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepicker-custom" id="purchaseDate" name="purchaseDate"
                             placeholder="DD-MM-YYYY" value="{{$ShareAcc->purchaseDate}}" readonly>
                    </div>
                </div>
            </div>
           
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Share Current Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="unitPrice" id="unitPrice"
                            readonly value="{{$ShareAcc->unitPrice}}">
                    </div>
                </div>
            </div>
           
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Number Of Shares</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control textNumber" name="numberOfShare" id="numberOfShare" required value="{{$ShareAcc->numberOfShare}}">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Total Price</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="totalPrice" id="totalPrice"value="{{$ShareAcc->totalPrice}}"
                            readonly>
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
    </div>

    </div>
    </div>
</form>


<script type="text/javascript">

    $(document).ready(function () {

         $.ajax({
                type: "POST",
                url: "../getData",
                data: {
                    context: 'sharePrice'
                    
                },
                dataType: "json",
                success: function (response) {
                    $("#unitPrice").val(response['Shareprice']);
                },
                error: function () {
                    alert('error!');
                }
            });

        // Disable Multiple Click
        $('form').submit(function (event) {
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


     

       

        $('#numberOfShare').on('input', function () {
            var value = 0;
            if ($(this).val() != '') {
                value = (parseFloat($(this).val()) * parseFloat($('#unitPrice').val()));;
            }

             $('#totalPrice').val(value.toFixed(2)); 
            // console.log(value);
        });
  
    
      

    });

</script>


@endsection
