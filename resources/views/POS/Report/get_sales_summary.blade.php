@extends('Layouts.erp_master_full_width')
@section('content')
<!-- Page -->
<!-- <div class="page"> -->
    <!-- <div class="page-header"> -->
    <!-- <h4 class="">Purchases List</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Transaction</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Purchases</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Purchases List</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{ url('/pos/purchase/add') }}">
                <i class="icon wb-link" aria-hidden="true"></i>
                <span class="hidden-sm-down">New Entry</span>
            </a>
        </div> -->
    <!-- </div> -->

   <!--  <div class="page-content">
        <div class="panel">
            <div class="panel-body"> -->
                <form enctype="multipart/form-data" method=""  data-toggle="validator"
                    novalidate="true" id="supModalFormId">
                    @csrf
                   
    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-2">

                                <div class="col-lg-1">
                                    <label class="input-title">Group</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group" >

                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="group_id" id="groupID">
                                        <option value="">Select Option</option>
                                    </select>

                                </div>
                                <div class="col-lg-1">
                                    <label class="input-title">Category</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group" >

                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="Category_id" id="CategoryID">
                                        <option value="">Select Option</option>
                                    </select>

                                </div>
                                <div class="col-lg-1">
                                    <label class="input-title"> Sub Category</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group" >

                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="sub_cat_id" id="sub_catID">
                                        <option value="">Select Option</option>
                                    </select>

                                </div>
                                <div class="col-lg-1">
                                    <label class="input-title">Brand</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group" >

                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="brand_id" id="brandID">
                                        <option value="">Select Option</option>
                                    </select>

                                </div>
                               
                            </div>
                        </div>
                    </div>




                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-2">
                                <div class="col-lg-1">
                                    <label class="input-title"> Branch</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group">
                                    <select class="form-control clsSelect2" name="branch_id" id="branchID">
                                        <option value="">Select Option</option>
                                    </select>

                                </div>
                               

                                <div class="col-lg-1">
                                    <label class="input-title">Start Date</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group">
                                    <input type="text" name="startDate"
                                        class="form-control round datepicker" placeholder="DD-MM-YYYY" autocomplete="off">
                                </div>
                                ​
                                <div class="col-lg-1">
                                    <label class="input-title">End Date</label>
                                </div>
                                ​
                                <div class="col-lg-2 input-group">
                                    <input type="text" name="endDate"
                                        class="form-control round datepicker" placeholder="DD-MM-YYYY"
                                        autocomplete="off">
                                </div>
                                
                                
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-2">

                                <div class="col-lg-10"></div>

                                <div class="col-lg-2 ">
                                    <button type="submit" class="btn btn-primary btn-round"
                                        id="validateButton2">Search</button>
                                </div>
                               
                            </div>
                        </div>
                    </div>


                </form>
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
                        <span style="color:black;"><b>Sales Summary Report (Branch Wise)</b></span>
                    </p>
                    <p class="text-right">Printed Date:14/1/2020</p>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table w-full table-hover table-bordered table-striped"
                                    id="PurchaseReportDataTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th width="5%">SL#</th>
                                            <th>Branch Name</th>
                                            <th>Total Quantity</th>
                                            <th>Total Sales Amount</th>
                                            <th>1st Installment</th>
                                            
                                        </tr>
                                    </thead>


                          <?php $i = 1;?>

                              <tfoot>
                                  <tr>
                                      <td colspan="2" style="text-align:right;" id="PRQuantity"><b>Grand Total:</b></td>
                                      <td style="text-align:center;" id="TQuantity"><b>0.00</b></td>
                                      <td style="text-align:center;" id="TAmount"><b>0.00</b></td>
                                      <td style="text-align:center;" id="INSAmount"><b>0.00</b></td>
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
$(document).ready( function () {


    $('#PurchaseReportDataTable').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
        "ajax":{
                       "url": "{{route('SalesSummaryDetailsReportDatatable')}}",
                       "dataType": "json",
                       "type": "post",
                       "data":{ _token: "{{csrf_token()}}"}
                     },

        columns: [{
                data: 'id'
            },
            {
                data: 'branch_name'
            },
            {
                data: 'tt_qnt'
            },
            {
                data: 'tt_amount'
            },
            {
                data: 'tt_ins'
            },
            
        ],
        drawCallback: function (data) {
        $('#TAmount').html(data.json.totalAmount);
        $('#TQuantity').html(data.json.totalQnt);
        $('#INSAmount').html(data.json.totalIns);
      },


    });
});

</script>
@endsection
