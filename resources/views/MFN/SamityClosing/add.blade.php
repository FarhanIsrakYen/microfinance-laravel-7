@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            @if (count($branches) > 1)
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Branch</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch" id="branch" required
                            data-error="Please Select Branch">
                            <option value="">Select</option>
                            @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Samity</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity" required
                            data-error="Please Select Samity">
                            <option value="">Select</option>
                            @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Closing Date</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round" id="closingDate" name="closingDate"
                                value="{{$closingDate}}" placeholder="DD-MM-YYYY" autocomplete="off" required
                                data-error="Please Select Date" readonly="readonly">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Submit</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
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
    $( document ).ready(function() {

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
            .done(function(response) {
                
                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: response['message'],
                    });
                    $('form').find(':submit').prop('disabled', false);
                }
                else{
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    }).then(function () {
                            window.location.href = "{{ url('mfn/samityclosing') }}";
                        });;

                    // setTimeout(function(){ window.location = "{{ url('mfn/samityclosing') }}"}, 3000);
                }                
            
            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
            });
            
        });

        // get the samities on selecting branch
        $('#branch').change(function(event) {
            $('#closingDate').val('');
            $('[name=currentFieldOfficer]').val('');
            $('#newFieldOfficer option:gt(0)').remove();
            $('#samity option:gt(0)').remove();

            if ($(this).val() == '') { 
                return false;
            }            

            $.ajax({
                url: './../getSamities',
                type: 'POST',
                dataType: 'json',
                data: {branchId: $('#branch').val()},
            })
            .done(function(samities) {
                $.each(samities, function(index, samity) {
                    $('#samity').append("<option value="+samity.id+">"+samity.name+"</option>");                     
                });
            })
            .fail(function() {
                console.log("error");
            });

            $.ajax({
                url: './../getBranchDate',
                type: 'POST',
                dataType: 'json',
                data: {branchId: $('#branch').val()},
            })
            .done(function(branchDate) {
                $('#closingDate').val(branchDate);
            })
            .fail(function() {
                console.log("error");
            });

        });

        // GET FIELD OFFICER WHEN SAMITY CHANGE
        // $('#samity').change(function() {
        //     $('[name=currentFieldOfficer]').val('');
        //     $('#newFieldOfficer option:gt(0)').remove();

        //     if ($(this).val() == '') { 
        //         return false;
        //     }

        //     $.ajax({
        //         url: './getSamityInfo',
        //         type: 'POST',
        //         dataType: 'json',
        //         data: {samityId: $('#samity').val()},
        //     })
        //     .done(function(data) {
        //         $('[name=currentFieldOfficer]').val(data.currentFieldOfficer);
        //         $.each(data.filedOfficers, function(index, val) {
        //             $('#newFieldOfficer').append("<option value="+index+">"+val+"</option>");                     
        //         });
        //     })
        //     .fail(function() {
        //         console.log("error");
        //     });
            
        // });


    });
</script>


@endsection