@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
  <div class="row">
      <div class="col-lg-12">
          <table class="table w-full table-hover table-bordered table-striped clsDataTable">
              <thead>
                  <tr>
                      <th style="width:5%;">SL</th>
                      <th>Title</th>
                      <th>Applicable For</th>
                      <th>Holiday From</th>
                      <th>Holiday To</th>
                      <th>Description</th>
                      <th>Company</th>
                      <th style="width:15%;" class="text-center">Action</th>
                  </tr>
              </thead>

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
                   "url": "{{route('specialholidayDatatable')}}",
                   "dataType": "json",
                   "type": "post",
                   "data":{ _token: "{{csrf_token()}}"}
                 },
          "columns": [

                { data: 'sid'},
                { data: 'sh_title', name: 'sh_title' },
                { data: 'sh_app_for', name: 'sh_app_for'},
                { data: 'sh_date_from', name: 'sh_date_from' },
                { data: 'sh_date_to', name: 'sh_date_to' },
                { data: 'sh_description', name: 'sh_description' },
                { data: 'comp_name', name: 'comp_name' },
                { data: 'action', name: 'action', orderable: false,className: 'text-center'},

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
          "{{url('hr/specialholiday/delete/')}}",
          "{{url('/ajaxDeleteCheck')}}",
          RowID,
          // "{{base64_encode('area_id')}}",
          // "",
          // "{{base64_encode('gnl_map_zone_area')}}"
      );
  }
</script>
@endsection
