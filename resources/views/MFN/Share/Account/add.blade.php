@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

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
                             placeholder="DD-MM-YYYY" value="{{$sysDate }}" readonly>
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
                        <input type="text" class="form-control textNumber" name="numberOfShare" id="numberOfShare" required onkeypress="calculateTotal();">
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
    $(document).ready(function () {

         $.ajax({
                type: "POST",
                url: "./getData",
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

        function selectizeMember(options) {
            console.log(options);

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
                options: options,
                create: false,
                render: {
                    option: function (member, escape) {
                        return '<div>' +
                            '<span class="lebel">' + member.name + ' - ' + member.memberCode +
                            '</span> <br>' +
                            '<span>Branch: ' + member.branch + '</span> <br>' +
                            '<span>Samity: ' + member.samity + '</span> <br>' +
                            '<span>Working Area: ' + member.workingArea + '</span> ' +
                            '</div>';

                    }
                }
            });
        }

        selectizeMember(@php echo json_encode($members) @endphp);

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
