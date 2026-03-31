<?php 
use App\Services\CommonService as Common;
?>

<style type="text/css">
     .datepicker-custom {
         z-index:9999 !important; 
     }
</style>

<?php $BillList = Common::ViewTableOrder('pos_sales_m',
                            ['is_delete' => 0],
                            ['id', 'sales_bill_no'],
                            ['sales_bill_no', 'ASC']);
           
            ?>
<div class="modal fade" id="modal-cust-due-sales" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"
data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h4 class="modal-title font-weight-bold text-center">Customer Due Sales Entry</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mx-3">
                <form enctype="multipart/form-data" method="post" action="" data-toggle="validator" novalidate="true" id="CDSModalFormId">
                    @csrf
                    <input type="hidden" id="csrf" name="_token" value="{{csrf_token()}}">
                    <div class="row ">
                        <div class="col-lg-12">
                            <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Customer Name</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group" >
                                        <select class="form-control round cls-select-2" name="customer_name_0" id="customer_name_0" required data-error="Select Customer." style="width: 100%">
                                            <option value="">Select One</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Products</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group">
                                        <select class="form-control cls-select-2" id="product_0" multiple="multiple" required data-error="Select Products." style="width: 100%">
                                            @foreach($ProductData as $PData)
		                                    	<option value="{{ $PData->id }}" pname = "{{ $PData->product_name }}">{{ $PData->product_name }}</option>
		                                    @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div>
                                <div class="form-row align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Sales Bill No</label>
                                    <div class="col-lg-8 form-group">
                                        <div class="input-group">

                                            <input type="text" id="sale_bill_no_0" class="form-control round" placeholder="SL-(4 digit branch code)-(serial no)" required data-error="Please Enter Sales Bill No">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>

                            </div>

                            <div>
                                <div class="form-row align-items-center">
                                    <label class="col-lg-4 input-title RequiredStar">Sales Amount</label>
                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <div class="input-group ">
                                                <input type="number" class="form-control round"
                                                    placeholder="Enter Sales Amount"
                                                    id="sale_amt_0" required
                                                    data-error="Please Enter Sales Amount"
                                                    onkeyup="fnCalculateDue();">
                                            </div>
                                            <div class="help-block with-errors is-invalid"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Collection Amount</label>
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="number" class="form-control round"
                                                placeholder="Enter Collection Amount" id="clln_amt_0"
                                                required data-error="Please Enter Collection Amount"
                                                onkeyup="fnCalculateDue();">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Due Amount</label>
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <div>
                                            <input type="number" class="form-control round" id="due_amt_0"
                                                placeholder="Enter Due Amount" required
                                                data-error="Please Enter Due Amount" readonly>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Installment Amount</label>
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="number" class="form-control round" id="inst_amt_0"
                                                placeholder="Enter Installment Amount" required
                                                data-error="Please Enter Installment Amount">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            {{-- Query for get all inst month package --}}
                            <?php $InstPackList = Common::ViewTableOrder('pos_inst_packages',
                                            ['is_delete' => 0],
                                            ['id', 'prod_inst_month'],
                                            ['prod_inst_month', 'DESC']);
                            ?>
                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Installment Month</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group">
                                        <select class="form-control cls-select-2" id="inst_month_0"
                                            required data-error="Select Installment Month" style="width: 100%">
                                            <option value="">Select Type</option>
                                            @foreach($InstPackList as $Package)
                                            	<option value="{{ $Package->id }}" instMonth="{{ $Package->prod_inst_month }}">{{ $Package->prod_inst_month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            {{-- Query for get all inst tupe --}}
                            <?php
                                $instTypes = Common::ViewTableOrder('gnl_installment_type',
                                            ['is_active' => 1],
                                            ['id', 'name'],
                                            ['id', 'ASC']);
                            ?>

                            <div class="form-row align-items-center">
                                <label class="col-lg-4 input-title RequiredStar">Installment Type</label>
                                <div class="col-lg-8 form-group">
                                    <div class="input-group">
                                        <select class="form-control cls-select-2" id="inst_type_0"
                                            required data-error="Select Installment Type." style="width: 100%">
                                            <option value="">Select Type</option>
                                            @foreach($instTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>

                            <div class="form-group">
	                            <div class="form-row align-items-center">
	                                <label class="col-lg-4 input-title RequiredStar" for="sales_date_0">Sales Date</label>
	                                <div class="col-lg-7 form-group">
	                                    <div class="input-group">
	                                        <div class="input-group-prepend">
	                                            <span class="input-group-text">
	                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
	                                            </span>
	                                        </div>
	                                        <input type="text" class="form-control round datepicker-custom" id="sales_date_0" autocomplete="off" placeholder="DD-MM-YYYY" required data-error="Select Date">
	                                    </div>
                                        <div class="help-block with-errors is-invalid"></div>
	                                </div>
	                            </div>
                            </div>

                            <div class="form-group">
	                            <div class="form-row align-items-center">
	                                <label class="col-lg-4 input-title RequiredStar" for="sales_date_0">Last Collection Date</label>
	                                <div class="col-lg-7 form-group">
	                                    <div class="input-group">
	                                        <div class="input-group-prepend">
	                                            <span class="input-group-text">
	                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
	                                            </span>
	                                        </div>
	                                        <input type="text" class="form-control round datepicker-custom" id="lst_clln_date_0" autocomplete="off" placeholder="DD-MM-YYYY" required data-error="Select Date">
	                                    </div>
                                        <div class="help-block with-errors is-invalid"></div>
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
                                    id="subBtnCustDueSalesPOP">Save</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
