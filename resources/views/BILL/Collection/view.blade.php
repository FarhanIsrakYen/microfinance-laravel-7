
<?php 
    use App\Services\CommonService as Common;
    use App\Services\HtmlService as HTML;
    use App\Services\BillService as BILLS;

    $PaySystemList = Common::ViewTableOrder('gnl_payment_system',
        [['is_delete', 0], ['is_active', 1]],
        ['id', 'payment_system_name'],
        ['id', 'ASC']);

?>
<!-- The Modal -->

@extends('Layouts.erp_master')
@section('content')

<div class="row d-print-none">
    <div class="col-lg-9 offset-3 mb-2">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($collectionData->company_id,'disabled') !!}
    </div>
</div>

<div class="table-responsive d-print-none">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4">
                    Collection Information
                </th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td width="25%">Collection No.</td>
                <td width="25%">{{ $collectionData->collection_no }}</td>

                <td width="25%">Collection Date</td>
                <td width="25%">{{ $collectionData->collection_date }}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>
                    {{ $collectionData->customer['customer_no'] ? $collectionData->customer['customer_name']." (".$collectionData->customer['customer_no'].")" : '' }}
                </td>

                <td>Collection By</td>
                <td>
                    {{ $collectionData->employee['employee_no'] ? $collectionData->employee['emp_name']." (".$collectionData->employee['employee_no'].")" : '' }}
                </td>
            </tr>
            <tr>
                <td>Payment Type</td>
                <td>
                    @foreach($PaySystemList as $payment)
                    @if($payment->id == $collectionData->payment_system_id)
                    {{ $payment->payment_system_name }}
                    @endif
                    @endforeach
                </td>

                <td>Collection Amount</td>
                <td>{{ $collectionData->collection_amount }}</td>
            </tr>

        </tbody>
    </table>
</div>

<div class="row align-items-center d-print-none">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">

                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>

                <a type="button" class="btn btn-default btn-round clsPrint"
                href="{{ url('pos/sales_cash/invoice/'.$collectionData->collection_no) }}">Invoice
                </a>
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
<script type="text/javascript">
    function invoiceModal(){
        $('.modal').show();
    }
</script>

@endsection
