@extends('Layouts.erp_master')

@section('content')
<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;



$customerData = Common::ViewTableOrderIn('pos_customers',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','customer_no', 'customer_name'],
    ['customer_name', 'ASC']);
 ?>
<!-- Search Option Start -->
<div class="row align-items-center pb-20 d-flex justify-content-center">
  <div class="col-lg-2">
      <label class="input-title">Customer</label>
      <div class="input-group">
          <select class="form-control clsSelect2" name="customer_id" id="customer_id">
              <option value="">Select Option</option>
              @foreach ($customerData as $row)
              <option value="{{ $row->customer_no }}">{{ $row->customer_name."(".$row->customer_no.")" }}</option>
              @endforeach
          </select>
      </div>
  </div>

    <div class="col-lg-2">
        <label class="input-title">Company</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="company_id" id="company_id">
                <option value="">Select All</option>
                <option value="1">Usha Foundation	</option>
            </select>
        </div>
    </div>

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" id="searchButton" name="searchButton" class="btn btn-primary btn-round">Search</a>
    </div>
</div>
<!-- Search Option End -->
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th width="5%">SL</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Customer</th>
                        <th>Company</th>
                        <th width="15%"  class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- End Page -->
<script>
function ajaxDataLoad(CustomerID = null, CompID = null){
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax":{
                 "url": "{{route('GuarantorDatatable')}}",
                 "dataType": "json",
                 "type": "post",
                  "data":{ _token: "{{csrf_token()}}",
                 CustomerID:CustomerID,
                 CompID:CompID,
                 }
               },
        columns: [

              { data: 'id', name: 'id' ,className: 'text-center'},
              { data: 'gr_name', name: 'gr_name' },
              { data: 'gr_mobile', name: 'gr_mobile' },
              { data: 'gr_email', name: 'gr_email' },
              { data: 'customer_name', name: 'customer_name', orderable: false },
              { data: 'comp_name', name: 'comp_name', orderable: false },
              {data: 'action', name: 'action', orderable: false,className: 'text-center'},

        ],
        'fnRowCallback': function(nRow, aData, Index) {
            var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
            $('td:last', nRow).html(actionHTML);
        }
    });
}

$(document).ready( function () {

    ajaxDataLoad();

    $('#searchButton').click(function(){
        var CustomerID = $('#customer_id').val();
        var CompID = $('#company_id').val();
        ajaxDataLoad(CustomerID,CompID);
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
        "{{url('pos/guarantor/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        // "{{base64_encode('gr_id')}}",
        // "{{base64_encode('is_delete,0')}}",
        // "{{base64_encode('#')}}"
    );
}
</script>
@endsection
