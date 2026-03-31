@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
    <form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">

              

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Table Name</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="table_name" placeholder="Enter Table Name" required data-error="Please enter Table name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

               

                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round" id="btnSubmitCompany">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
<!-- End Page -->

<script type="text/javascript">
   
    // $('btnSubmitCompany').click(function(){

    // });

</script>


@endsection
