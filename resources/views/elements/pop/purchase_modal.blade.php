<?php 
use App\Services\CommonService as Common;
?>

<div class="modal fade" id="modalSupplierForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h4 class="modal-title font-weight-bold text-center">Supplier Entry</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body mx-3">
                        <form enctype="multipart/form-data" method="post" action="" data-toggle="validator"
                            novalidate="true" id="supModalFormId">
                            @csrf
                            <input type="hidden" id="csrf" name="_token" value="{{csrf_token()}}">



                            <div class="row">
                                <div class="col-lg-12">
                                    <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">
                                  
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER NAME</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" placeholder="Enter Supplier Name" name="sup_name" id="sup_name" required data-error="Please enter supplier name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">SUPPLIER TYPE</label>
                                        <div class="col-lg-8 form-group">
                                            <div class="input-group">
                                                <select class="form-control"  
                                                    name="supplier_type" id="supplier_type" required
                                                    data-error="Select Supplier Type.">
                                                    <option value="">Select Type</option>
                                                    <option value="1">PURCHASE</option>
                                                    <option value="2">COMISSION</option>

                                                </select>
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>
                            
                                    <div id="comissionIDinput" style="display:none;">
                                        <div class="form-row align-items-center" >
                                            <label class="col-lg-4 input-title RequiredStar">COMISSION</label>
                                            <div class="col-lg-8">
                                                <div class="form-group">
                                                    <div class="input-group ">
                                                        <input type="number" class="form-control round" placeholder="Enter Comission Percentage."
                                                        name="comission_percent" id="comission_percent">
                                                    </div>
                                                    <div class="help-block with-errors is-invalid"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Supplier's Company</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" placeholder="Enter Company Name" name="sup_comp_name" id="sup_comp_name" required data-error="Please enter supplier's company name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Email</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div>
                                                    <input type="email" class="form-control round" id="sup_email" name="sup_email" placeholder="Enter Email" required data-error="Please enter email.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title RequiredStar">Mobile</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="number" class="form-control round" id="sup_phone" name="sup_phone" placeholder="Enter Phone Number" required data-error="Please enter mobile.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Address</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <textarea class="form-control round" id="sup_addr" name="sup_addr" rows="2" placeholder="Enter Address"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Website</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" name="sup_web_add" id="sup_web_add" placeholder="https://example.com" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Description</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <textarea class="form-control round fix-size" id="sup_desc" name="sup_desc" rows="2" placeholder="Enter Description"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Reference No</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" id="sup_ref_no" name="sup_ref_no" placeholder="Enter Reference No">
                                                </div>
                                                {{-- <div class="help-block with-errors is-invalid"></div> --}}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <label class="col-lg-4 input-title">Attentions</label>
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <textarea class="form-control round" rows="2" id="sup_attentionA" name="sup_attentionA" placeholder="Enter Attentions."></textarea>
                                                </div>
                                                {{-- <div class="help-block with-errors is-invalid"></div> --}}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                        
                        </form>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <div class="row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" class="btn btn-default btn-round"
                                            data-dismiss="modal">Back</a>
                                        <a href="javascript:void(0)" class="btn btn-primary btn-round"
                                            id="submitButtonSupPOP">Submit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>