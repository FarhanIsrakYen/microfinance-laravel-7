@extends('Layouts.erp_master')
@section('content')

<?php

	$gnlComp = App\Model\GNL\Company::where([['is_active', 1], ['is_delete', 0]])->first();

?>
<div class="row">
	<div class="col-lg-12" style="color: #000; font-size: 13px;">
		<div class="d-print-none text-right">
			<a href="javascript:void(0)" onClick="window.print();"
                style="background-color:transparent;border:none;" class="btnPrint mr-2">
                <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
            </a>
		</div>

		<p style="font-size: 12px; color: #000; font-weight: 500;">
			Ref No: {{ $gnlComp->comp_name }}/{{ $orderData->sup_name }} Product/Work order/{{ date("Y") }}/{{ $orderData->order_no }}
		</p>
		<p>{{ date('d-M-Y') }}</p>
		<p>
			{{ $orderData->sup_name }}<br>
			{{ $orderData->sup_addr }}<br>
			{{ $orderData->sup_email }}
		</p>
		<p>
			@if($orderData->sup_attentionA != null)
				<span style="font-size: 12px; color: #000; font-weight: 500;">Atten: {{ $orderData->sup_attentionA }}</span><br>
			@endif

			@if($orderData->sup_attentionB != null)
				<span style="font-size: 12px; color: #000; font-weight: 500;">Atten: {{ $orderData->sup_attentionB }}</span><br>
			@endif

			@if($orderData->sup_attentionC != null)
				<span style="font-size: 12px; color: #000; font-weight: 500;">Atten: {{ $orderData->sup_attentionC }}</span>
			@endif
		</p>

		<p>
			Sub: Supply order of {{ $orderData->sup_name }} Product for {{ $gnlComp->comp_name }} Branchs.
		</p><br>

		<p>
			Dear Sir,<br>
			Pursuant to the discussion between ourselves and also taking into cognizance the contents of your offer letter we are pleased to place herewith this work order with the specifications and terms and conditions as appended hereunder:-
		</p>

		<p class="text-center" style="font-size: 19px;">Order Details</p>
		<table class="table table-hover table-striped table-bordered w-full text-center clsDataTable">
			<thead>
				<tr>
					<td width ="5%">SL</td>
					<td>Product Description / Model No.</td>
					<td>Quantity</td>
					<td>Unit Price</td>
					<td>Total Price</td>
					<td>Delivery Place</td>
					<td>Contact Person</td>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 1;
					$ttl_qtn = 0;
				?>
				@foreach($orderD as $row)
					<tr>
						<td>{{ $i++ }}</td>
						<td class="text-left">{{ $row->product_name .' /'. $row->model_name }}</td>
						<td>
							{{ $row->product_quantity }}
							<?php $ttl_qtn += $row->product_quantity ?>
						</td>
						<td class="text-right">

						</td>
						<td class="text-right">
						</td>
						<td class="text-left">{{ $row->branch_addr }}</td>
						<td class="text-left">
							<span>{{ $row->contact_person }}</span><br>
							<span>{{ $row->branch_phone }}</span><br>
							<span>{{ $row->requisition_no }}</span>
							<?php
								$contactPerson = $row->contact_person;
							?>
						</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2" style="color: #000" class="text-right"><b>Total</b></td>
					<td style="color: #000"><b>{{ $ttl_qtn }}</b></td>
					<td class="text-right"></td>
					<td class="text-right"></td>
					<td colspan="2"></td>
				</tr>
			</tfoot>
		</table>

		<?php
			$termsConds = DB::table('gnl_terms_conditions')->where([['is_delete', 0], ['is_active', 1]])->get();
		?>

		<p style="font-size: 10px;">
			<u style="font-weight: 600;">Terms & Conditions:</u><br>
			@foreach($termsConds as $row)
				{{ $row->tc_remarks }}<br>
			@endforeach
		</p>
		<p>Thanking You,</p>
		<br><br>

		<p style="font-size: 12px; color: #000; font-weight: 500;">
			{{ $contactPerson }} <br>
			Managing Director
		</p>
		<span>
			Copy to:
			<ul>
			    <li>Director-ECBD.</li>
			    <li>Program Director-MFP.</li>
			</ul>
		</span>

	</div>
</div>
<div class="row">
		<div class="col-lg-12">
				<div class="form-group text-center">
						<div class="example example-buttons">
								<a href="javascript:void(0)" onclick="goBack();"
													class="btn btn-default btn-round d-print-none">Back</a>

						</div>
				</div>
		</div>
</div>
@endsection
