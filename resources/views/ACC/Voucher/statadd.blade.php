@extends('Layouts.erp_master')
@section('content')

<!-- Page -->


<div class="panel-body">
    <div class="row">
    
        <div class="col-lg-12">
            <div class="example-wrap">
                <div class="nav-tabs-horizontal" data-plugin="tabs">
                    <ul class="nav nav-tabs nav-tabs-reverse" role="tablist">


                    <?php $flag = true; ?>
                                @foreach ($vtype as $vdata)
                                   
                                  <li class="nav-item" role="presentation"><a 
                                  {{ ($flag == true) ? 'class="nav-link  active"' : 'class="nav-link"' }}  data-toggle="tab" 
                                  href='#{{ preg_replace("/\s+/", "", "$vdata->name")}}' aria-controls='{{ preg_replace("/\s+/", "", "$vdata->name")}}' role="tab">{{$vdata->name}}</a></li>
                                 <?php $flag = false; ?>
                                @endforeach


                        <li class="nav-item" role="presentation"><a class="nav-link active" data-toggle="tab" 
                        href="#DebitVoucher" aria-controls="DebitVoucher" role="tab">Debit Voucher</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" data-toggle="tab"
                         href="#CreditVoucher" aria-controls="CreditVoucher" role="tab">Credit Voucher</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" data-toggle="tab" 
                        href="#JournalVoucher" aria-controls="JournalVoucher" role="tab">Journal Voucher</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" data-toggle="tab" 
                        href="#ContraVoucher" aria-controls="ContraVoucher" role="tab">Contra Voucher</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" data-toggle="tab" 
                        href="#FundTransfer" aria-controls="FundTransfer" role="tab">Fund Transfer</a></li>
                    </ul>
                    <div class="tab-content pt-20">
                        <div class="tab-pane active" id="DebitVoucher" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <label class="panel-title col-md-4">Debit Voucher</label>
                                            <div class="panel-title col-md-5">
                                                <label >Total Amount: <span id="totalAmountColumnPV">0.00</span> Tk</label>
                                            </div>
                                            <div class="panel-title col-md-3">
                                                <label>Branch: <strong>Head Office</strong></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <label class="col-lg-1 input-title" for="DebitVoucherProject">Project</label>
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="DebitVoucherProject" id="DebitVoucherProject" disabled>
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="DebitVoucherProjectType">Project Type</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="DebitVoucherProjectType" id="DebitVoucherProjectType" disabled>
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="DebitVoucherDate">Voucher Date</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="DebitVoucherDate" name="DebitVoucherDate" data-plugin="datepicker" placeholder="DD-MM-YYYY" disabled>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="DebitVoucherCode">Voucher Code</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="DebitVoucherCode" name="DebitVoucherCode" value="" disabled>
                                        </div>
                                    </div>

                                    <div class="row align-items-center">
                                        <label class="col-lg-1 input-title" for="DebitVoucherCredit">Credit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="DebitVoucherCredit" id="DebitVoucherCredit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="DebitVoucherDebit">Debit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="DebitVoucherDebit" id="DebitVoucherDebit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="DebitVoucherAmount">Amount</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="DebitVoucherAmount" name="DebitVoucherAmount" placeholder="Enter Amount">
                                        </div>

                                        <label class="col-lg-1 input-title" for="DebitVoucherNarration">Narration/ Cheque Details</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="DebitVoucherNarration" name="DebitVoucherNarration" placeholder="Enter Details">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Credit Account</th>
                                                <th>Debit Account</th>
                                                <th>Amount</th>
                                                <th>Narration/ Cheque Details</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>21101- Cash In Hand</td>
                                                <td>1- Loan to others</td>
                                                <td>20000</td>
                                                <td>Details..</td>
                                                <td>
                                                    <a href="#"><i class="icon wb-trash blue-grey-600"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-2 input-title" for="VoucherGlobalNarration">Global Narration Details</label>
                                        <div class="col-lg-10 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="VoucherGlobalNarration" name="VoucherGlobalNarration" placeholder="Please Enter Narration.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex justify-content-center">
                                                <div class="example example-buttons">
                                                    <a href="#" class="btn btn-default btn-round">Close</a>
                                                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit & Print</button>
                                                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="CreditVoucher" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <label class="panel-title col-md-4">Credit Voucher</label>
                                            <div class="panel-title col-md-5">
                                                <label>Total Amount: <span id="totalAmountColumnPV">0.00</span> Tk</label>
                                            </div>
                                            <div class="panel-title col-md-3">
                                                <label>Branch: <strong>Head Office</strong></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <label class="col-lg-1 input-title" for="CreditVoucherProject">Project</label>
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="CreditVoucherProject" id="CreditVoucherProject">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="CreditVoucherProjectType">Project Type</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="CreditVoucherProjectType" id="CreditVoucherProjectType">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="CreditVoucherDate">Voucher Date</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="CreditVoucherDate" name="CreditVoucherDate" data-plugin="datepicker" placeholder="DD-MM-YYYY" disabled>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="CreditVoucherCode">Voucher Code</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="CreditVoucherCode" name="CreditVoucherCode" value="" disabled>
                                        </div>
                                    </div>

                                    <div class="row align-items-center">
                                        <label class="col-lg-1 input-title" for="CreditVoucherDebit">Debit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="CreditVoucherDebit" id="CreditVoucherDebit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="CreditVoucherCredit">Credit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="CreditVoucherCredit" id="CreditVoucherCredit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="CreditVoucherAmount">Amount</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="CreditVoucherAmount" name="CreditVoucherAmount" placeholder="Enter Amount">
                                        </div>

                                        <label class="col-lg-1 input-title" for="CreditVoucherNarration">Narration/ Cheque Details</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="CreditVoucherNarration" name="CreditVoucherNarration" placeholder="Enter Details">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Debit Account</th>
                                                <th>Credit Account</th>
                                                <th>Amount</th>
                                                <th>Narration/ Cheque Details</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>21101- Cash In Hand</td>
                                                <td>1- Loan to others</td>
                                                <td>20000</td>
                                                <td>Details..</td>
                                                <td>
                                                    <a href="#"><i class="icon wb-trash blue-grey-600"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-2 input-title" for="VoucherGlobalNarration">Global Narration Details</label>
                                        <div class="col-lg-10 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="VoucherGlobalNarration" name="VoucherGlobalNarration" placeholder="Please Enter Narration.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex justify-content-center">
                                                <div class="example example-buttons">
                                                    <a href="#" class="btn btn-default btn-round">Close</a>
                                                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit & Print</button>
                                                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="JournalVoucher" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <label class="panel-title col-md-4">Journal Voucher</label>
                                            <div class="panel-title col-md-5">
                                                <label>Total Amount: <span id="totalAmountColumnPV">0.00</span> Tk</label>
                                            </div>
                                            <div class="panel-title col-md-3">
                                                <label>Branch: <strong>Head Office</strong></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <label class="col-lg-1 input-title" for="JournalVoucherProject">Project</label>
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="JournalVoucherProject" id="JournalVoucherProject">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="JournalVoucherProjectType">Project Type</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="JournalVoucherProjectType" id="JournalVoucherProjectType">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="JournalVoucherDate">Voucher Date</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="JournalVoucherDate" name="JournalVoucherDate" data-plugin="datepicker" placeholder="DD-MM-YYYY" disabled>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="JournalVoucherCode">Voucher Code</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="JournalVoucherCode" name="JournalVoucherCode" value="" disabled>
                                        </div>
                                    </div>

                                    <div class="row align-items-center">
                                        <label class="col-lg-1 input-title" for="JournalVoucherDebit">Debit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="JournalVoucherDebit" id="JournalVoucherDebit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="JournalVoucherCredit">Credit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="JournalVoucherCredit" id="JournalVoucherCredit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="JournalVoucherAmount">Amount</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="JournalVoucherAmount" name="JournalVoucherAmount" placeholder="Enter Amount">
                                        </div>

                                        <label class="col-lg-1 input-title" for="JournalVoucherNarration">Narration/ Cheque Details</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="JournalVoucherNarration" name="JournalVoucherNarration" placeholder="Enter Details">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Debit Account</th>
                                                <th>Credit Account</th>
                                                <th>Amount</th>
                                                <th>Narration/ Cheque Details</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>21101- Cash In Hand</td>
                                                <td>1- Loan to others</td>
                                                <td>20000</td>
                                                <td>Details..</td>
                                                <td>
                                                    <a href="#"><i class="icon wb-trash blue-grey-600"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-2 input-title" for="VoucherGlobalNarration">Global Narration Details</label>
                                        <div class="col-lg-10 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="VoucherGlobalNarration" name="VoucherGlobalNarration" placeholder="Please Enter Narration.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex justify-content-center">
                                                <div class="example example-buttons">
                                                    <a href="#" class="btn btn-default btn-round">Close</a>
                                                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit & Print</button>
                                                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="ContraVoucher" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <label class="panel-title col-md-4">Contra Voucher</label>
                                            <div class="panel-title col-md-5">
                                                <label>Total Amount: <span id="totalAmountColumnPV">0.00</span> Tk</label>
                                            </div>
                                            <div class="panel-title col-md-3">
                                                <label>Branch: <strong>Head Office</strong></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <label class="col-lg-1 input-title" for="ContraVoucherProject">Project</label>
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="ContraVoucherProject" id="ContraVoucherProject">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="ContraVoucherProjectType">Project Type</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="ContraVoucherProjectType" id="ContraVoucherProjectType">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="ContraVoucherDate">Voucher Date</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="ContraVoucherDate" name="ContraVoucherDate" data-plugin="datepicker" placeholder="DD-MM-YYYY" disabled>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="ContraVoucherCode">Voucher Code</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="ContraVoucherCode" name="ContraVoucherCode" value="" disabled>
                                        </div>
                                    </div>

                                    <div class="row align-items-center">
                                        <label class="col-lg-1 input-title" for="ContraVoucherDebit">Debit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="ContraVoucherDebit" id="ContraVoucherDebit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="ContraVoucherCredit">Credit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="ContraVoucherCredit" id="ContraVoucherCredit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="ContraVoucherAmount">Amount</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="ContraVoucherAmount" name="ContraVoucherAmount" placeholder="Enter Amount">
                                        </div>

                                        <label class="col-lg-1 input-title" for="ContraVoucherNarration">Narration/ Cheque Details</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="ContraVoucherNarration" name="ContraVoucherNarration" placeholder="Enter Details">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Debit Account</th>
                                                <th>Credit Account</th>
                                                <th>Amount</th>
                                                <th>Narration/ Cheque Details</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>21101- Cash In Hand</td>
                                                <td>1- Loan to others</td>
                                                <td>20000</td>
                                                <td>Details..</td>
                                                <td>
                                                    <a href="#"><i class="icon wb-trash blue-grey-600"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-2 input-title" for="VoucherGlobalNarration">Global Narration Details</label>
                                        <div class="col-lg-10 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="VoucherGlobalNarration" name="VoucherGlobalNarration" placeholder="Please Enter Narration.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex justify-content-center">
                                                <div class="example example-buttons">
                                                    <a href="#" class="btn btn-default btn-round">Close</a>
                                                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit & Print</button>
                                                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="FundTransfer" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <label class="panel-title col-md-4">Fund Transfer Voucher</label>
                                            <div class="panel-title col-md-5">
                                                <label>Total Amount: <span id="totalAmountColumnPV">0.00</span> Tk</label>
                                            </div>
                                            <div class="panel-title col-md-3">
                                                <label>Branch: <strong>Head Office</strong></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <label class="col-lg-1 input-title" for="FundVoucherProject">Project</label>
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="FundVoucherProject" id="FundVoucherProject">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="FundVoucherProjectType">Project Type</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="FundVoucherProjectType" id="FundVoucherProjectType">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="FundVoucherDate">Voucher Date</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control round" id="FundVoucherDate" name="FundVoucherDate" data-plugin="datepicker" placeholder="DD-MM-YYYY" disabled>
                                        </div>
                                        ​
                                        <label class="col-lg-1 input-title" for="FundVoucherCode">Voucher Code</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="FundVoucherCode" name="FundVoucherCode" value="" disabled>
                                        </div>
                                    </div>

                                    <div class="row align-items-center">
                                        <label class="col-lg-1 input-title" for="TargetBranch">Target Branch</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="TargetBranch" id="TargetBranch">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>
                                        <label class="col-lg-1 input-title" for="TargetBranchCash">Target Branch Cash/Bank</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="TargetBranchCash" id="TargetBranchCash">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="FundVoucherNarration">Narration/ Cheque Details</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="FundVoucherNarration" name="FundVoucherNarration" placeholder="Enter Details">
                                        </div>

                                        <label class="col-lg-1 input-title" for="FundVoucherDebit">Debit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="FundVoucherDebit" id="FundVoucherDebit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="FundVoucherCredit">Credit Account</label>
                                        ​
                                        <div class="col-lg-2 input-group">
                                            <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="FundVoucherCredit" id="FundVoucherCredit">
                                                <option value="">Select Option</option>
                                                <option value="1">Connecticut</option>
                                                <option value="2">Delaware</option>
                                                <option value="3">Florida</option>
                                                <option value="4">Georgia</option>
                                            </select>
                                        </div>

                                        <label class="col-lg-1 input-title" for="FundVoucherAmount">Amount</label>

                                        <div class="col-lg-2 input-group">
                                            <input type="text" class="form-control round" id="FundVoucherAmount" name="FundVoucherAmount" placeholder="Enter Amount">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
                                        <thead>
                                            <tr>
                                                <th>Target Branch</th>
                                                <th>Target Cash/Bank</th>
                                                <th>Debit Account</th>
                                                <th>Credit Account</th>
                                                <th>Amount</th>
                                                <th>Narration</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>21101- Cash In Hand</td>
                                                <td>21101- Cash In Hand</td>
                                                <td>21101- Cash In Hand</td>
                                                <td>1- Loan to others</td>
                                                <td>20000</td>
                                                <td>Details..</td>
                                                <td>
                                                    <a href="#"><i class="icon wb-trash blue-grey-600"></i></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-row align-items-center">
                                        <label class="col-lg-2 input-title" for="VoucherGlobalNarration">Global Narration Details</label>
                                        <div class="col-lg-10 form-group">
                                            <div class="input-group ">
                                                <input type="text" class="form-control round" id="VoucherGlobalNarration" name="VoucherGlobalNarration" placeholder="Please Enter Narration.">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex justify-content-center">
                                                <div class="example example-buttons">
                                                    <a href="#" class="btn btn-default btn-round">Close</a>
                                                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit & Print</button>
                                                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
