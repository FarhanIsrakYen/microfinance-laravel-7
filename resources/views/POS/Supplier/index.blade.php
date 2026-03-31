@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
use App\Services\CommonService as Common;

$branchId =  Common::getBranchId();

?>

<!-- Page -->

    <!-- Search Option Start -->
    <div class="row align-items-center pb-20 d-flex justify-content-center">
      <div class="col-lg-2">
          <label class="input-title">Supplier Type</label>
          <div class="input-group">
              <select class="form-control clsSelect2" name="supplier_type" id="supplier_type">
                  <option value="">Select All</option>
                  <option value="1">Purchase</option>
                  <option value="2">Commission</option>
              </select>
          </div>
      </div>
        <div class="col-lg-2 pt-20 text-center">
            <a href="javascript:void(0)" id="searchButton"  class="btn btn-primary btn-round">Search</a>
        </div>
    </div>
    <!-- Search Option End -->
<!-- Page -->

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width:4%;">SL</th>
                        <th>Name</th>
                        <th>Supplier Type</th>
                        <th>Supplier Company</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th style="width:15%;" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- End Page -->
<script>
function ajaxDataLoad(supplier_type = null) {
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        order: [[ 0, "DESC" ]],
        stateSave: true,
        stateDuration: 1800,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax": {
            "url": "{{route('supplierDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                // _token: "{{csrf_token()}}"
                supplier_type: supplier_type,
            }
        },
        columns: [

            {
                data: 'id',
                name: 'id',
                className: 'text-center'
            },
            {
                data: 'sup_name'
            },
            {
                data: 'supplier_type',
                name: 'supplier_type'
            },
            {
                data: 'sup_comp_name',
                name: 'sup_comp_name'
            },
            {
                data: 'sup_email',
                name: 'sup_email'
            },
            {
                data: 'sup_phone',
                name: 'sup_phone'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                className: 'text-center d-print-none'
            },

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        }
    });
}

$(document).ready(function() {
    ajaxDataLoad();
  $('#searchButton').click(function() {
      var supplier_type = $('#supplier_type').val();
      ajaxDataLoad(supplier_type);

  });
});

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
        "{{url('pos/supplier/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('supplier_id')}}",
        "{{base64_encode('is_delete,0')}}",
        "{{base64_encode('pos_products')}}",
        "{{base64_encode('pos_purchases_m')}}"
    );
}
</script>
@endsection
