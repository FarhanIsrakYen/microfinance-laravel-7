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
                      <th>Title</th>
                      <th>Day</th>
                      <th>Description</th>
                      <th>Effective Day</th>
                      <th>Company</th>
                      <th style="width:15%;" class="text-center">Action</th>
                  </tr>
              </thead>
              <tbody>
                 <?php
                 $i= 0;
                 ?>
                  @foreach ($CompHolidayData as $Row)
                      <tr>
                        <td scope="row"> {{++$i}}</td>
                        <td> {{$Row->ch_title}}</td>
                        <td>
                             {{$Row->ch_day}}
                        </td>
                        <td> {{$Row->ch_description}}</td>
                        <td> {{date('d-m-Y',strtotime($Row->ch_eff_date))}}</td>
                        <td> {{$Row->company['comp_name'] }} </td>
                      
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
          "{{url('gnl/compholiday/delete/')}}",
          "{{url('/ajaxDeleteCheck')}}",
          RowID,
          // "{{base64_encode('group_id')}}",
          // "{{base64_encode('is_delete,0')}}",
          // "{{base64_encode('gnl_companies')}}"
      );
  }
</script>

@endsection
