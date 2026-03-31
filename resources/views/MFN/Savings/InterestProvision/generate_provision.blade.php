<!-- Modal -->
<div class="modal fade" id="interestProvisionModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="exampleModalLongTitle">Generate!!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" data-toggle="validator" novalidate="true" id="intereatProvisionFromId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-row form-group align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Generate For:</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="generateFor"
                                                id="generateForAll" value="All">
                                            <label class="form-check-label" for="generateForAll">All</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="generateFor"
                                                id="generateForPartAcc" value="Particular Account" checked>
                                            <label class="form-check-label" for="generateForPartAcc">Individual
                                                Account</label>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="branchAccDiv">
                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Branch:</label>
                                    <div class="col-lg-8">
                                        <div class="input-group ">
                                            <select class="form-control cls-select-2" name="branchId" id="branchId"
                                                data-error="Please Select Branch" style="width: 100%" required>
                                                <option value="">Select Branch</option>
                                                @foreach($branchList as $row)
                                                <option value="{{ $row->id }}">{{ $row->branch }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="form-row form-group align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Samity:</label>
                                    <div class="col-lg-8">
                                        <div class="input-group ">
                                            <select class="form-control cls-select-2" name="samityId" id="samityId"
                                                data-error="Please Select Branch" style="width: 100%" required>
                                                <option value="">Select Branch</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                                <div class="form-row form-group align-items-center" id="accountDivId">
                                    <label class="col-lg-4 input-title RequiredStar">Account:</label>
                                    <div class="col-lg-8">
                                        <div class="input-group ">
                                            <select class="form-control cls-select-2" name="accountId" id="accountId"
                                                data-error="Please Select Account" style="width: 100%">
                                                <option value="">Select Account</option>
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row form-group  align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Date To:</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control datepicker-custom"
                                            style="z-index:99999!important;" id="dateTo" name="dateTo"
                                            autocomplete="off" placeholder="DD-MM-YYYY" data-error="Please Select Date"
                                            required>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-round">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){

        $("#accountDivId").hide();

        $('.cls-select-2').select2({
            dropdownParent: $('#interestProvisionModal'),
            placeholder: "Select Please",
        });
    });

    $('#branchId').change(function(){

        var branchId = $(this).val();

        if(branchId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}/getData",
            data: {context : 'samity', branchId : branchId},
            dataType: "json",
            success: function (response) {
                $('#samityId')
                        .find('option')
                        .remove()
                        .end()
                        .append(response.samities);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    $('#samityId').change(function(){

        var samityId = $(this).val();

        if (samityId != 'all') {
            $('#accountDivId').show('slow');
        } else {
            $('#accountDivId').hide('slow');
        }

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}/getData",
            data: {context : 'account', samityId : samityId},
            dataType: "json",
            success: function (response) {
                $('#accountId')
                        .find('option')
                        .remove()
                        .end()
                        .append(response.accounts);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    $('#generateForPartAcc').click(function () {

        if ($('#generateForPartAcc').is(':checked')) {
            $('#branchAccDiv').show('slow');
            $('#branchId').prop('required', true);
            $('#accountId').prop('required', true);
        } else {
            $('#branchAccDiv').hide('slow');
            $('#branchId').prop('required', false);
            $('#accountId').prop('required', false);
        }
    })

    $('#generateForAll').click(function () {

        if ($('#generateForAll').is(':checked')) {
            $('#branchAccDiv').hide('slow');
            $('#branchId').prop('required', false);
            $('#accountId').prop('required', false);
        } else {
            $('#branchAccDiv').show('slow');
            $('#branchId').prop('required', true);
            $('#accountId').prop('required', true);
        }
    })

    // Disable Multiple Click with submit form
    $('form').submit(function (event) {
        event.preventDefault();

        $.ajax({
                url: "{{ url()->current() . '/add' }}",
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
                    $('#branchId').val('');
                    $('#branchId').trigger('change');
                    $('#samityId')
                        .find('option')
                        .remove();
                    $('#accountId')
                        .find('option')
                        .remove();

                    $('#interestProvisionModal').modal('hide');

                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    });

                    ajaxDataLoad();
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