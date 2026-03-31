@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="page">
    <div class="page-content">
        <div class="panel">
            <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-2">
                                <div class="col-lg-1">
                                    <label class="input-title">Branch</label>
                                </div>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="branch_id" id="branch_id">
                                        <option value="">Select Option</option>
                                        @foreach ($BranchData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <a href="javascript:void(0)" class="btn btn-primary btn-round"
                                        id="CustomerSearch">Search</a>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="row">
                  <div class="col-lg-11"></div>
                  <div class="col-lg-1 text-right">
                      <a href="" style="background-color:transparent;border:none;float:left;" class="mr-2"><i class="fa fa-print fa-lg " style="font-size:20px;"></i></a>
                      <a href="" style="background-color:transparent;border:none;float:left;"><i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i></a>
                  </div>
                </div>
                <div id="salesReportTable">
                    <p class="text-center">
                        <span style="color:black;"><b>USHA Foundation</b></span>
                        <br>
                        <span style="color:black;"><b>Branch Name: Head Office </b></span>
                        <br>
                        <span style="color:black;"><b>Branch Customer Report</b></span>
                    </p>
                    <!-- <p class="text-right">Printed Date:14/1/2020</p> -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table w-full table-hover table-bordered table-striped
                                clsDataTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">SL</th>
                                            <th>Branch Name</th>
                                            <th>No of Customers</th>
                                        </tr>
                                    </thead>

                                  <tfoot>
                                      <tr>
                                          <td colspan="2" style="text-align:right;" id="TQuantity"><b>Total:</b></td>
                                           <td id="TCCount"><b>0.00</b></td>
                                          <!-- <td style="text-align:center;" id="TUnitPrice"><b></b></td> -->
                                          <!-- <td style="text-align:center;" id="TAmount"><b>0.00</b></td> -->
                                      </tr>
                                  </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
​   function ajaxDataLoad(BranchID = null){
      $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // ordering: false,
            // lengthMenu: [[10, 20, 30, 50], [10, 20, 30, 50]],
            "ajax":{
               "url": "{{route('CustomerDatatable')}}",
               "dataType": "json",
               "type": "post",
               "data":{ _token: "{{csrf_token()}}",
                        BranchID: BranchID
                    }
             },
            columns: [
                { data: 'id',
                className: 'text-center'},
                { data: 'branch_name'},
                { data: 'customer_count', orderable: false, targets: [0]},
​
            ],
            drawCallback:
                function (oResult) {
                  $('#TCCount').html(oResult.json.CCounttotal);
                },
        });
    }
​
    $(document).ready( function () {
        ajaxDataLoad();
        $('#CustomerSearch').click(function(){
            var BranchID = $('#branch_id').val();
            ajaxDataLoad(BranchID);
        });
    });
</script>
@endsection
