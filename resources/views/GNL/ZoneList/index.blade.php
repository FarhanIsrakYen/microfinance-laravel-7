@extends('Layouts.erp_master')

@section('content')
  <div class="row">
      <div class="col-lg-12">
          <table class="table w-full table-hover table-bordered table-striped clsDataTable">
              <thead>
                  <tr>
                      <th style="width:5%;">SL</th>
                      <th>Name</th>
                      <th class="text-center" style="width:15%;">Code </th>
                      <th>Area</th>
                      <!-- <th>Company</th> -->
                      <th style="width:15%;" class="text-center">Action</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  $i = 0;
                  ?>


              </tbody>
          </table>
      </div>
  </div>
<!-- End Page -->
<script>
function ajaxDataLoad(){
    $('.clsDataTable').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: 1800,
        // ordering: false,
        // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
        "ajax":{
                 "url": "{{route('zoneDatatable')}}",
                 "dataType": "json",
                 "type": "post",
                 "data":{ _token: "{{csrf_token()}}"}
               },
        columns: [

              { data: 'id', name: 'id' },
              { data: 'zone_name', name: 'zone_name' },
              { data: 'zone_code', name: 'zone_code' ,className: 'text-center'},
              { data: 'area_name', name: 'area_name', orderable: false },
              // { data: 'comp_name', name: 'comp_name' },
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
        "{{url('gnl/zone/delete/')}}",
        "{{url('/ajaxDeleteCheck')}}",
        RowID,
        "{{base64_encode('zone_id')}}",
        "",
        "{{base64_encode('gnl_map_region_zone')}}"
    );
}
</script>
@endsection
