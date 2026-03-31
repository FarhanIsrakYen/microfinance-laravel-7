@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<?php 
use App\Services\RoleService as Role;
?>

<div class="row smallDivWidth">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                <thead>
                    <tr>
                        <th style="width:3%;">SL</th>
                        <th class="text-left">Company</th>
                        <th class="text-right">Amount</th>
                        <th style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i= 0; ?>
                    @foreach($proFeeData as $pData)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $pData->company['comp_name'] }}</td>
                        <td class="text-right">{{ $pData->amount }}</td>
                        <td class="text-center"> {!! Role::roleWisePermission($GlobalRole, $pData->id) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
function fnDelete(RowID) {
    /**
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */

    fnDeleteCheck(
        "{{url('pos/proFee/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('inst_package_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('pos_sales_m')}}"
    );
}
</script>

@endsection