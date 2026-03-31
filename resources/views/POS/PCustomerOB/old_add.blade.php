@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<div class="page">
    <div class="page-header">
        <h4 class="">New Product OB Due Sale Entry</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('/pos')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Product</a></li>
            <li class="breadcrumb-item"><a href="{{url('/pos/product/obduesale')}}">Product OB Due Sale List</a></li>
            <li class="breadcrumb-item active">Entry Product OB Due Sale</li>
        </ol>
    </div>
    <div class="page-content">
        <div class="panel">
            <div class="panel-body">

                <form method="post" class="form-horizontal" action="{{route('storeprodobduesale')}}"
                    data-toggle="validator" novalidate="true">
                    @csrf

                    <div class="row ">
                        <div class="col-lg-9 offset-lg-3">
                            <!-- Html View Load  -->
                            <!-- {!! HTML::forCompanyFeild() !!} -->
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title">Branch</label>
                                <div class="col-lg-5">
                                    <div class="form-group">

                                        <div class="input-group ">
                                            <select class="form-control clsSelect2"
                                                name="branch_id" id="branch_id" onblur="fnCheckDuplicate(
                                                 this.value, this.name,
                                                 '{{url('/ajaxCheckDuplicate')}}',
                                                 '{{base64_encode('pos_customers')}}',
                                                 'txtCodeError', 'branch_id');">
                                                <option value="0000" selected="selected">0000 - Head Office</option>
                                                @foreach ($BranchData as $Row)
                                                <option value="{{$Row->id}}">
                                                    {{sprintf("%04d", $Row->branch_code)." - ".$Row->branch_name}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Product</label>
                                <div class="col-lg-5">
                                    <div class="form-group">

                                        <div class="input-group ">
                                            <select class="form-control clsSelect2" 
                                                name="product_id" id="product_id" required
                                                data-error="Please select Product name." onblur="fnCheckDuplicate(
                                             this.value, this.name,
                                             '{{url('/ajaxCheckDuplicate')}}',
                                             '{{base64_encode('pos_purchases_d')}}',
                                             'txtCodeError', 'product_id');">
                                                <option value="">Select Product</option>
                                                @foreach ($ProductData as $Row)
                                                <option value="{{$Row->id}}">{{$Row->product_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Bill No</label>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="text" class="form-control round" id="bill_no" name="bill_no"
                                                placeholder="Enter Opening Balance Amount" required="required">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Product Quantity</label>
                                <div class="col-lg-5">
                                    <div class="form-group">

                                        <div class="input-group ">
                                            <input type="number" class="form-control round" id="product_quantity"
                                                name="product_quantity" placeholder="Enter Opening Balance Quantity"
                                                required="required">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Sale Amount</label>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="number" class="form-control round" id="sale_amount"
                                                name="sale_amount" placeholder="Enter Sale Amount" required="required">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Installment Amount</label>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="number" class="form-control round" id="installment_amount"
                                                name="installment_amount" placeholder="Enter Installment Amount"
                                                required="required">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <label class="col-lg-3 input-title RequiredStar">Due Amount</label>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="number" class="form-control round" id="due_amount"
                                                name="due_amount" placeholder="Enter Due Amount" required="required">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="form-group d-flex justify-content-center">
                                        <div class="example example-buttons">
                                            <a href="{{url('/pos/product/obduesale')}}"
                                                class="btn btn-default btn-round">Back</a>
                                            <button type="submit" class="btn btn-primary btn-round"
                                                id="validateButton2">Save</button>
                                            <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<!-- End Page -->

@endsection