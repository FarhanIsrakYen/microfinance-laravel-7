@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\RoleService as Role;
?>

<style>
.table>tbody td p {
    margin-bottom: 0px;
}
/* .clsdataTable > thead > tr > th[class*="nosort"]::after{display: none} */
</style>
<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsdataTable" data-plugin="dataTable">
                <thead>
                    <tr>
                        <th class="nosort" style="width:3%;">SL</th>
                        <th>Color</th>
                        <th>Group</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <!-- <th>Brand</th> -->
                        <!-- <th>Model</th> -->
                        <!-- <th>Size</th> -->
                        <th style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                       $i= 0;
                       ?>
                    @foreach ($ProdColorData as $Row)
                    <tr>
                        <td scope="row"> {{++$i}}</td>
                        <td> {{$Row->color_name}}</td>
                        <td> {{$Row->pgroup['group_name'] }}</td>
                        <td> {{$Row->pcategory['cat_name'] }}</td>
                        <td> {{$Row->psubCategoty['sub_cat_name'] }}</td>
                        <!-- <td> {{-- $Row->pbrand['brand_name'] --}}</td> -->
                        <!-- <td> {{-- $Row->pmodel['model_name'] --}}</td> -->
                        <!-- <td> {{-- $Row->psize['size_name'] --}}</td> -->

                        <td class="text-center">
                            <!-- Action Calling Role Wise -->
                            {!! Role::roleWisePermission($GlobalRole, $Row->id) !!}
                        </td>
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
        "{{url('pos/color/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('prod_size_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('pos_p_colors')}}"
    );
}


// $(document).ready(function(){
//     $('.clsdataTable').DataTable({
//         stateSave: true,
//         stateDuration: 1800,
//         // order: [[ 2, 'asc' ]],
//         // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
//         columnDefs: [
//             { orderable: false, targets: [0,1,8] }
//         ]
//     });
// });
</script>
@endsection