@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
  <div class="row">
      <div class="col-lg-12">
        <div class="table-responsive">
          <table class="table w-full table-hover table-bordered table-striped clsDataTable">
              <thead>
                  <tr>
                      <th style="width:3%;">SL</th>
                      <th style="width:20%;">Name</th>
                      <th style="width:7%;" class="text-center"> Code</th>

                      <th style="width:60%; word-wrap: break-word;">Branch</th>
                      <!-- <th>Company</th> -->
                      <th style="width:10%;" class="text-center">Action</th>
                  </tr>
              </thead>
              <?php
                $i = 0;
              ?>

          </table>
        </div>
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
                   "url": "{{route('areaDatatable')}}",
                   "dataType": "json",
                   "type": "post",
                   "data":{ _token: "{{csrf_token()}}"}
                 },
          columns: [

                { data: 'id', name: 'id'},
                { data: 'area_name', name: 'area_name' },
                { data: 'area_code', name: 'area_code' ,className: 'text-center'},
                { data: 'branch_name', name: 'branch_name', orderable: false },
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
          "{{url('gnl/area/delete/')}}",
          "{{url('/ajaxDeleteCheck')}}",
          RowID,
          "{{base64_encode('area_id')}}",
          "",
          "{{base64_encode('gnl_map_zone_area')}}"
      );
  }
</script>


@endsection
