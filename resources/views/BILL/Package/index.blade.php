@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\RoleService as Role;
?>

<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
            <thead>
                <tr>
                    <th style="width:5%;">SL</th>
                    <th>Package Name</th>
                    <th>Package Products</th>
                    <th>Package Price </th>
                    <th style="width:15%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
              <?php
              $i = 0;
              ?>
              @foreach ($dataSet as $Row)
              <tr>
                  <td scope="row" class="text-center"> {{++$i}}</td>
                  <td> {{$Row['package_name']}}</td>
                  <td> {{$Row['product']}}</td>
                  <td class="text-right"> {{$Row['package_price']}}</td>
                  <td class="text-center">
                      <!-- Action Calling Role Wise -->
                      {!! Role::roleWisePermission($GlobalRole, $Row['id']) !!}
                  </td>
              </tr>
              @endforeach
            </tbody>
        </table>
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
        "{{url('bill/package/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        // "{{base64_encode('prod_sub_cat_id')}}",
        // "{{base64_encode('is_delete,0')}}",
        // "{{base64_encode('pos_p_brands')}}"
    );
}
</script>
@endsection
