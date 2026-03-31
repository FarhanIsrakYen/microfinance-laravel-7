@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Requisition Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Requisition No.</td>
                <td width="25%">{{ $requisitionM->requisition_no }}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td>{{ date('d-m-Y', strtotime($requisitionM->requisition_date)) }}</td>
            </tr>
            <tr>
                <td>Requisition To</td>
                <td>{{ $requisitionM->branch_id }}</td>
            </tr>
            <tr>
                <td>Requisition From</td>
                <td>{{ $requisitionM->emp_from }}</td>
            </tr>
            <tr>
                <td>Supplier Name</td>
                <td>{{ $requisitionM->sup_name }}</td>
            </tr>
            <tr>
                <td>Requisition For</td>
                <td>{{ $requisitionM->requisition_for == 1 ? 'Personal' : 'Employee' }}</td>
            </tr>
            @if($requisitionM->requisition_for == 2)
                <tr>
                    <td>Department</td>
                    <td>{{ $requisitionM->dept_name }}</td>
                </tr>
                <tr>
                    <td>Room</td>
                    <td>{{ $requisitionM->room_name }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <table class="table table-striped table-bordered"
        id="purchaseTable">
        <thead>
            <tr>
                <th width="40%">Product Name</th>
                <th width="15%">Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php $ttl_qtn = 0; ?>
            @foreach($requisitionD as $reqD)
                <tr>
                    <td>
                        {{ $reqD->product_code ? $reqD->product_name . ' (' . $reqD->product_code . ')' : $reqD->product_name }}
                        {{-- @if(Common::getBranchId() == 1)
                            @if($reqD->is_ordered == 1)
                                <span class="border border-danger text-danger" style="margin-left: 20px;">{{ 'ORDERED' }}</span>
                            @elseif($reqD->order_qtn > 0)
                                <span class="border border-primary text-primary" style="margin-left: 20px;">{{ 'ORDERED='.$reqD->order_qtn }}</span>
                            @endif
                        @endif --}}
                    </td>

                    <td class="text-center">
                        {{ $reqD->product_quantity }}
                        <?php $ttl_qtn += $reqD->product_quantity; ?>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="text-right"><h5>Total Quantity</h5></td>
                <td class="text-center"><h5>{{ $ttl_qtn }}</h5></td>
            </tr>

        </tbody>
    </table>
</div>
<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
              <a href="javascript:void(0)" onclick="goBack();"
                  class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>

@endsection
