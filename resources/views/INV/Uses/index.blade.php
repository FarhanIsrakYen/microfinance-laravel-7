@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;


// $SupplierData = Common::ViewTableOrder('inv_suppliers', [['is_delete', 0], ['is_active', 1]], ['id', 'sup_name'], ['sup_name', 'ASC']);
// $GroupData = Common::ViewTableOrder('inv_p_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name'], ['group_name', 'ASC']);
// $CategoryData = Common::ViewTableOrder('inv_p_categories', [['is_delete', 0], ['is_active', 1]], ['id', 'cat_name'], ['cat_name', 'ASC']);
// $SubCatData = Common::ViewTableOrder('inv_p_subcategories', [['is_delete', 0], ['is_active', 1]], ['id', 'sub_cat_name'], ['sub_cat_name', 'ASC']);
// $BrandData = Common::ViewTableOrder('inv_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
// $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();

?>
<!-- Search Option Start -->


<!-- Search Option start -->
<div class="row align-items-center pb-10 d-print-none">
    <!-- Html View Load For Branch Search -->
    {!! HTML::forBranchFeildSearch('all') !!}


    <div class="col-lg-2">
      <label class="input-title">Start Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="start_date"
                name="startDate" placeholder="DD-MM-YYYY" value="{{$StartDate}}">
        </div>
    </div>


    <div class="col-lg-2">
      <label class="input-title">End Date</label>
        <div class="input-group ghdatepicker">
            <div class="input-group-prepend ">
                <span class="input-group-text ">
                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                </span>
            </div>
            <input type="text" class="form-control round datepicker-custom" id="end_date"
                name="endDate" placeholder="DD-MM-YYYY" value="{{$EndDate}}">
        </div>
    </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton"
            class="btn btn-primary btn-round" id="useSearch">Search</a>
    </div>
</div>

<!-- {{-- do not delete commentted code below --}}
        {{-- <div class="row align-items-center d-flex justify-content-center pb-10">

            <label class="input-title">Group</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="group_id" id="group_id">
                    <option value="">Select one</option>
                    @foreach ($GroupData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Category</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="category_id" id="category_id">
                    <option value="">Select one</option>
                    @foreach ($CategoryData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Sub Category</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
                    <option value="">Select one</option>
                    @foreach ($SubCatData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="input-title">Brand</label>
            <div class="col-lg-2">
                <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                    <option value="">Select one</option>
                    @foreach ($BrandData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                    @endforeach
                </select>
            </div>

        </div> --}} -->

<!-- Search Option End -->


<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="3%" class="text-center">SL</th>
                        <th width="10%">Use Date</th>
                        <th width="10%">Bill No</th>
                        <th width="15%">Employee</th>
                        <th width="10%">Department</th>
                        <th width="20%">Products</th>
                        <th width="10%">Quantity</th>
                        <th width="10%">Branch</th>
                        <th width="12%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>

    function ajaxDataLoad(sDate = null, eDate = null, branchID = null,
                          pGroupID = null, categoryId = null, subCatID = null, brandID = null){

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: 1800,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
               "url": "{{route('INVUsesList')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        sDate: sDate,
                        eDate: eDate,
                        branchID: branchID,
                        pGroupID: pGroupID,
                        categoryId: categoryId,
                        subCatID: subCatID,
                        brandID: brandID
                    }
             },
            columns: [
                { data: 'id', className: 'text-center'},
                { data: 'uses_date'},
                { data: 'uses_bill_no'},
                { data: 'emp_name', orderable: false},
                { data: 'dept_name', orderable: false},
                { data: 'product_name', orderable: false},
                { data: 'total_quantity', className: 'text-center'},
                { data: 'branch_name', orderable: false},
                { data: 'action', name: 'action', orderable: false, className: 'text-center'},

            ],
            'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                
                // var hiturl = "<?= url('inv/use/invoice')?>/"+aData.uses_bill_no;
                // actionHTML += '<a href="'+hiturl+'" title="Invoice" class="btnView"><i class="fa fa-file-powerpoint-o"></i></a>';
                
                $('td:last', nRow).html(actionHTML);
            }
        });
    }

    $(document).ready( function () {

        ajaxDataLoad();

        $('#useSearch').click(function(){

            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            var branchID = $('#branch_id').val();
            var pGroupID = $('#group_id').val();
            var categoryId = $('#category_id').val();
            var subCatID = $('#sub_cat_id').val();
            var brandID = $('#brand_id').val();

            ajaxDataLoad(sDate, eDate, branchID, pGroupID, categoryId, subCatID, brandID);
        });
    });

    // Delete Data
    function fnDelete(RowID) {

        // return false;
        /**
         * para 1 = link to delete without id
         * para 2 = ajax check link same for all
         * para 3 = id of deleting item
         * para 4 = matching column
         * para 5 = condition2
         * para 6 = table 1
         * para 7 = table 2
         * para 8 = table 3
         */

        fnDeleteCheck(
            "{{url('inv/use/delete/')}}",
            "{{url('/ajaxDeleteCheck')}}",
            RowID,
            "{{base64_encode('uses_bill_no')}}",
            "{{base64_encode('is_delete,0')}}",
            "{{base64_encode('inv_use_return_m')}}"
        );
    }

</script>

@endsection
