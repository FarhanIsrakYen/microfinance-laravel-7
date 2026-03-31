<?php 
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\InvService as INVS;

?>

@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($UseData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Product Use Information
                </th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td>Branch</td>
                <td>
                    @if($UseData->branch != null)
                    {{ (!empty($UseData->branch['branch_name'])) ? $UseData->branch['branch_name']. " (". $UseData->branch['branch_code'].")" : "" }}
                    @endif
                </td>

                <?php
                    $SalesDate = new DateTime($UseData->uses_date);
                    $SalesDate = (!empty($SalesDate)) ? $SalesDate->format('d-m-Y') : date('d-m-Y');
                ?>
                <td>Use Date</td>
                <td>{{ $SalesDate }}</td>
            </tr>

            <tr>
                <td width="25%">Bill No.</td>
                <td width="25%">{{ $UseData->uses_bill_no }}</td>

                <td width="25%">Requisition No.</td>
                <td width="25%">{{ $UseData->requisition_no }}</td>
            </tr>

            <tr>
                @if($UseData->requisition_for == 2)
                <td>Department</td>
                <td>{{ $UseData->department['dept_name'] }}</td>

                <td>Room</td>
                    <td>{{ (!empty($UseData->room['room_name'])) ? $UseData->room['room_name'] ." (". $UseData->room['room_code'].")" : "" }}</td>

                @else
                    <td>Employee</td>
                    <td>{{ (!empty($UseData->employee['emp_name'])) ? $UseData->employee['emp_name'] ." (". $UseData->employee['emp_code'].")" : "" }}</td>
                @endif
            </tr>
            
        </tbody>
    </table>
</div>


<!-- Product table -->
<table class="table table-hover table-striped table-bordered w-full text-center d-print-none" id="salesTable">
    <thead>
        <tr>
            <th width="50%">Product Name</th>
            <th width="20%">Quantity</th>
            <th width="30%">Serial No</th>
            
        </tr>
    </thead>
    <tbody>
        <?php
            $ProductList = Common::ViewTableOrder('inv_products',
                            ['is_delete' => 0],
                            ['id', 'product_name', 'cost_price', 'product_code'],
                            ['product_name', 'ASC']);
        ?>

        @if(count($UseDataD) > 0)
        @foreach($UseDataD as $SDataD)
        <tr>
            <td class="text-left">
                @foreach($ProductList as $ProductInfo)
                @if($ProductInfo->id == $SDataD->product_id)
                {{ $ProductInfo->product_name . ' (' . $ProductInfo->product_code . ')' }}
                @endif
                @endforeach
            </td>

            <td class="text-center">
                {{ $SDataD->product_quantity }}
            </td>

            <td>
                {{ $SDataD->product_serial_no }}
            </td>

        </tr>
        @endforeach
        @endif
        <tr>
            <td colspan="1" class="text-right">
                <h5>Total Quantity</h5>
            </td>
            <td class="text-center">
                <h5>{{ $UseData->total_quantity }}</h5>
            </td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>

<div class="row align-items-center d-print-none">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">

                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    #invoiceTable tbody tr td, #invoiceTable th {
        padding: .2rem;
        padding-right: .5rem;
    }
    .prWarranty ul {
        list-style-type: none;    
    }

    .prWarranty ul li:before {
        content:'*'; /* Change this to unicode as needed*/
        width: 1em !important;
        margin-left: -1em;
        display: inline-block;
    }
</style>

@endsection
