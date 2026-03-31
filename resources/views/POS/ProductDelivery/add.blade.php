@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;
?>

<!-- <div class="row align-items-center pb-10">
	<div class="col-lg-2">
	    <label class="input-title">Delivery No:</label>
	    <div class="input-group">
	        <input type="text" class="form-control" id="delivery_no" name="delivery_no">
	    </div>
	</div>

	<div class="col-lg-2">
	    <label class="input-title">Order No.</label>
	    <div class="input-group">
	    	<?php 
	    		$orders = App\Model\POS\OrderMaster::where([['is_delete', 0], ['is_active', 1]])->get();
	    	?>
	        <select class="form-control clsSelect2" name="order_id" id="order_id">
	            <option value="">Select Option</option>
	            @foreach ($orders as $row)
	            	<option value="{{ $row->order_no }}">{{ $row->order_no }}</option>
	            @endforeach
	        </select>
	    </div>
	</div>

	<div class="col-lg-2">
	    <label class="input-title">Requisition No.</label>
	    <?php
	    	$requistions = App\Model\POS\RequisitionMaster::where([['is_delete', 0], ['is_active', 1]])->get();
	    ?>
	    <div class="input-group">
	        <select class="form-control clsSelect2" name="requisition_id" id="requisition_id">
	            <option value="">Select Option</option>
	            @foreach ($requistions as $row)
	            <option value="{{ $row->requisition_no }}">{{ $row->requisition_no }}</option>
	            @endforeach
	        </select>
	    </div>
	</div>

	<div class="col-lg-2">
	    <label class="input-title">Chalan No.</label>
	    <div class="input-group">
	        <input type="text" class="form-control" id="invoice_id" name="invoice_id">
	    </div>
	</div>

	<div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
            id="searchButton">Search</a>
    </div>
</div> -->

<div class="row">
    <div class="col-lg-6">
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Delivery No</label>
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="requisition_no" name="requisition_no" placeholder="Enter Requisition No." required="required"
                        value="{{ POSS::generateBillDelivery(Common::getBranchId()) }}" 
                        readonly>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">

        <input type="hidden" name="branch_to" id="branch_to" value="{{ Common::getBranchId() }}">

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Order No.</label>
            <?php 
	    		$orders = App\Model\POS\OrderMaster::where([['is_delete', 0], ['is_active', 1]])->get();
	    	?>
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="input-group ">
                        <select class="form-control round clsSelect2" name="order_id" id="order_id"
                        onchange="fnGetRequisitonNo()">
            	            <option value="">Select Option</option>
            	            @foreach ($orders as $row)
            	            	<option value="{{ $row->order_no }}">{{ $row->order_no }}</option>
            	            @endforeach
            	        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Requisition No.</label>
            <?php
                $requistions = App\Model\POS\RequisitionMaster::where([['is_delete', 0], ['is_active', 1]])->get();
            ?>
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="input-group ">
                        <select class="form-control round clsSelect2" name="requisition_id" id="requisition_id">
                            <option value="">Select Option</option>
                            @foreach ($requistions as $row)
                            <option value="{{ $row->requisition_no }}">{{ $row->requisition_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Invoice No</label>
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="invoice_id" name="invoice_id" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="form-group text-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();"
                          class="btn btn-default btn-round d-print-none">Back</a>

                    <button type="submit" class="btn btn-primary btn-round"
                    id="order_button_id">Order</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function fnGetRequisitonNo() {

        var order_no = $('#order_id').val();

        if(order_no != '')
        {
            $.ajax({
                method: "GET",
                url: "{{ url('/ajaxGetRequisitionNo') }}",
                dataType: "text",
                data: {
                    order_no: order_no,
                },
                success: function(data) {
                    
                    if (data) {
                        $('#requisition_id')
                        .find('option')
                        .remove()
                        .end()
                        .append(data);
                    }
                }
            });
        }
    }
</script>

@endsection