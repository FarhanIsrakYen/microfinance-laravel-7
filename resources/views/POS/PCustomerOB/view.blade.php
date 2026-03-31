@extends('Layouts.erp_master_full_width')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<div class="panel">
	<div class="panel-body">

		<div class="row">
			<div class="col-lg-12">
				{!! HTML::forCompanyFeild($POBDueSaleDataM->company_id,'disabled') !!}
			</div>
		</div>

		<table class="table table-striped table-bordered">
			<thead>
				<th colspan="4">
					Opening Balance Customer Due Sale Information
				</th>
			</thead>
			<tbody style="color: #000;">
				<tr>
					<th width="20%">Branch</th>
					<td width="20%">{{$POBDueSaleDataM->branch['branch_name']}}</td>

					<th width="20%">Opening Date </th>
					<td width="20%">{{date('d-m-Y', strtotime($POBDueSaleDataM->opening_date))}}</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-hover table-striped table-bordered w-full text-center table-responsive">
			<thead>
				<th width="1%">#</th>
				<th width="10%">Customer Name</th>
				<th width="5%">Customer Code</th>
				<th width="20%">Product</th>
				<th width="8%">Sales Amount</th>
				<th width="8%">Colletion Amount</th>
				<th width="8%">Due Amount</th>
				<th width="8%">Inst. Amount</th>
				<th width="8%">Inst. Month</th>
				<th width="8%">Inst. Type</th>
				<th width="8%">Sale Date</th>
				<th width="8%">Last Collection Date</th>
			</thead>
			<?php $i = 0; ?>
			@foreach($POBDueSaleDataD as $OBMData)
				<tbody>
					<td>
						<strong>{{ ++$i }}</strong>
						<input type="hidden" name="customer_id_arr[]" value="{{ $OBMData->customer_id }}">
					</td>
					<td>
						{{-- <input type="text" name="customer_name_arr[]" id="customer_name_{{ $i }}" value="{{ $OBMData->customer_name }}" class="form-control round" readonly> --}}
						{{ $OBMData->customer_name }}
					</td>
					<td>
						{{-- <input type="text" name="customer_no_arr[]" id="customer_no_{{ $i }}" value="{{ $OBMData->customer_no }}" class="form-control round" readonly> --}}
						{{ $OBMData->customer_no }}
					</td>
					<td>
						<?php
							$product = explode(',', $OBMData->sales_products);
						?>
						{{-- <select name="product_arr[][]" id="product_{{ $i }}" class="form-control round cls-select2-mul" multiple="multiple" disabled="true">
							@foreach($ProductData as $PData)
								<option value="{{ $PData->id }}"
									@if(in_array($PData->id, $product)) {{ 'selected' }} @endif>{{ $PData->product_name }}</option>
							@endforeach
						</select> --}}
						@foreach($ProductData as $PData)
							@if(in_array($PData->id, $product))
								{{ $PData->product_name.', ' }}
							@endif
						@endforeach
					</td>
					<td>
						<input type="hidden" class="ttl-sales-amt-cls" value="{{ $OBMData->sales_amount }}">
						{{ $OBMData->sales_amount }}
					</td>
					<td>
						{{-- <input type="text" name="collection_amt_arr[]" id="collection_amt_{{ $i }}" value="" class="form-control round ttl-clln-amt-cls" onkeyup="fnTotalCllnAmount();" readonly> --}}
						<input type="hidden" class="ttl-clln-amt-cls" value="{{ $OBMData->collection_amount }}">
						{{ $OBMData->collection_amount }}
					</td>
					<td>
						{{-- <input type="text" name="due_amt_arr[]" id="due_amt_{{ $i }}" value="{{ $OBMData->due_amount }}" class="form-control round ttl-due-amt-cls" onkeyup="fnTotalDueAmount();" readonly> --}}
						<input type="hidden" class="ttl-due-amt-cls" value="{{ $OBMData->due_amount }}">
						{{ $OBMData->due_amount }}
					</td>
					<td>
						{{-- <input type="text" name="inst_amt_arr[]" id="inst_amt_{{ $i }}" value="{{ $OBMData->installment_amount }}" class="form-control round" readonly> --}}
						{{ $OBMData->installment_amount }}
					</td>
					<td>
						{{-- <input type="text" name="inst_month_arr[]" id="inst_month_{{ $i }}" value="{{ $OBMData->installment_month }}" class="form-control round" readonly> --}}
						{{ $OBMData->installment_month }}

					</td>
					<td>
						{{-- <select name="inst_type_arr[]" id="inst_type_amt_{{ $i }}" class="form-control round clsSelect2" disabled="true">
							<option value="1" @if($OBMData->installment_type == 1) {{ 'selected' }} @endif>Month</option>
							<option value="2" @if($OBMData->installment_type == 2) {{ 'selected' }} @endif>Week</option>
						</select> --}}
						@if($OBMData->installment_type == 1)
							{{ 'Month' }}
						@else
							{{ 'Week' }}
						@endif
					</td>
					<td>
						<?php
							$sale_date = new DateTime($OBMData->sales_date);
							$sale_date = $sale_date->format('d-m-Y')
						?>
						{{-- <input type="text" name="sale_date_arr[]" id="sale_date_{{ $i }}" value="{{ $sale_date }}" class="form-control round datepicker" autocomplete="off" placeholder="DD-MM-YYYY" readonly> --}}
						{{ $sale_date }}
					</td>
					<td>
						<?php
							$last_collection_date = new DateTime($OBMData->last_collection_date);
							$last_collection_date = $last_collection_date->format('d-m-Y')
						?>
							{{-- <input type="text" name="last_clln_date_arr[]" id="last_clln_date_{{ $i }}"
							value="{{ $last_collection_date }}" class="form-control round datepicker" autocomplete="off" placeholder="DD-MM-YYYY" readonly> --}}
							{{ $last_collection_date }}
					</td>
				</tbody>
			@endforeach
			<tfoot>
				<td>
					<input type="hidden" name="total_customer" value="{{ $i }}">
				</td>
				<td colspan="3"><strong>Total</strong></td>
				<td>
					<strong id="total_sales_amt">0</strong>
					<input type="hidden" name="total_sales_amount" id="total_sales_amount" value="0">
				</td>
				<td>
					<strong id="total_clln_amt">0</strong>
					<input type="hidden" name="total_collection" id="total_collection" value="0">
				</td>
				<td>
					<strong id="total_due_amt">0</strong>
					<input type="hidden" name="total_due_amount" id="total_due_amount" value="0">
				</td>
			</tfoot>
		</table>

		<div class="row">
			<div class="col-lg-12">
				<div class="form-group d-flex justify-content-center">
					<div class="example example-buttons">
						<a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
						<a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">

$(document).ready(function(){

	fnTotalSalesAmount();
	fnTotalCllnAmount();
	fnTotalDueAmount();
});


function fnTotalSalesAmount() {

    var totalSalesAmt = 0;
    $('.ttl-sales-amt-cls').each(function() {
        totalSalesAmt = Number(totalSalesAmt) + Number($(this).val());
    });
    $('#total_sales_amt').html(totalSalesAmt);
    //-------------------------- Total sales Amount
    $('#total_sales_amount').val(totalSalesAmt);
}

function fnTotalCllnAmount() {

    var totalCllnAmt = 0;
    $('.ttl-clln-amt-cls').each(function() {
        totalCllnAmt = Number(totalCllnAmt) + Number($(this).val());
    });
    $('#total_clln_amt').html(totalCllnAmt);
    //-------------------------- Total Collection Amount
    $('#total_collection').val(totalCllnAmt);
}

function fnTotalDueAmount() {

    var totalDueAmt = 0;
    $('.ttl-due-amt-cls').each(function() {
        totalDueAmt = Number(totalDueAmt) + Number($(this).val());
    });
    $('#total_due_amt').html(totalDueAmt);
    //-------------------------- Total Due Amount
    $('#total_due_amount').val(totalDueAmt);
}

</script>

@endsection
