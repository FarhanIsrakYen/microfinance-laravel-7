@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="page">

    <div class="page-content">
        <div class="panel">
            <div class="panel-body">
                <form enctype="multipart/form-data" method=""  data-toggle="validator"
                    novalidate="true" id="supModalFormId">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="row mb-2">
                                <label class="col-lg-1 input-title">Start Date</label>
                                <div class="col-lg-2 input-group">
                                    <input type="text" name="start_date" id="start_date" 
                                        class="form-control round datepicker" placeholder="DD-MM-YYYY" 
                                        autocomplete="off">
                                </div>
                                ​
                                <label class="col-lg-1 input-title">End Date</label>
                                <div class="col-lg-2 input-group">
                                    <input type="text" name="end_date" id="end_date" 
                                        class="form-control round datepicker" placeholder="DD-MM-YYYY"
                                        autocomplete="off">
                                </div>

                                <label class="col-lg-1 input-title">Zone</label>
                                <div class="col-lg-2 input-group">
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="zone_id" id="zone_id">
                                        <option value="">Select Option</option>
                                        @foreach ($BranchData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-lg-1 input-title">Area</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="area_id" id="area_id">
                                        <option value="">Select Option</option>
                                        @foreach ($SupplierData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->sup_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">

                                <label class="col-lg-1 input-title">Branch</label>
                                <div class="col-lg-2 input-group">
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="branch_id" id="branch_id">
                                        <option value="">Select Option</option>
                                        @foreach ($BranchData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-lg-1 input-title">Supplier</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="supplier_id" id="supplier_id">
                                        <option value="">Select Option</option>
                                        @foreach ($SupplierData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->sup_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <label class="col-lg-1 input-title">Group</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="prod_group_id" id="prod_group_id">
                                        <option value="">Select Option</option>
                                        @foreach ($GData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->group_name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <label class="col-lg-1 input-title">Category</label>
                                <div class="col-lg-2 input-group">
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="prod_cat_id" id="prod_cat_id">
                                        <option value="">Select Option</option>
                                        @foreach ($CData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="row mb-2">
                                <label class="col-lg-1 input-title">Sub Category</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="prod_sub_cat_id" id="prod_sub_cat_id">
                                        <option value="">Select Option</option>
                                        @foreach ($SData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-lg-1 input-title">Brand</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="prod_brand_id" id="prod_brand_id">
                                        <option value="">Select Option</option>
                                        @foreach ($BData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-lg-1 input-title">Model</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="prod_model_id" id="prod_model_id">
                                        <option value="">Select Option</option>
                                        @foreach ($MData as $Row)
                                        <option value="{{ $Row->id }}">{{ $Row->model_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="col-lg-1 input-title">Name</label>
                                <div class="col-lg-2 input-group">
                                    <input type="text" name="name" id="name" class="form-control round">
                                </div>

                            </div>

                            <div class="row">

                                <label class="col-lg-1 input-title">Stock</label>
                                <div class="col-lg-2 input-group" >
                                    <select class="form-control round browser-default" data-plugin="selectpicker"
                                        data-style="btn-outline btn-primary" name="stock" id="stock">
                                        <option value="0">With Zero</option>
                                        <option value="1">Without Zero</option>
                                    </select>
                                </div>

                                <div class="col-lg-2">
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
                      <a href="javascript:void" style="background-color:transparent;border:none;float:left;" class="mr-2"><i class="fa fa-print fa-lg " style="font-size:20px;"></i></a>
                      <a href="javascript:void" style="background-color:transparent;border:none;float:left;"><i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i></a>

                  </div>
                </div>
                <div id="transferReportTable">
                    <p class="text-center">
                        <span style="color:black;"><b>USHA Foundation</b></span>
                        <br>
                        <span style="color:black;"><b>All Branches</b></span>
                        <br>
                        <span style="color:black;"><b>Stock Report : Inventory</b></span>
                    </p>
                    <!-- <p class="text-right">Printed Date:14/1/2020</p> -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table w-full table-hover table-bordered table-striped"
                                    id="StockReportDataTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th width="5%">SL#</th>
                                            <th>Supplier</th>
                                            <th>Group</th>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Product Name</th>
                                            <th>Barcode</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>


                          <?php $i = 1;?>

                              <tfoot>
                                  <tr>
                                      <td colspan="8" style="text-align:right;" id="TQuantity"><b>Total:</b></td>
                                      <td style="text-align:center;" id="TQuantity"><b>0.00</b></td>
                                      <td style="text-align:center;" id="TAmount"><b>0.00</b></td>
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
@endsection
