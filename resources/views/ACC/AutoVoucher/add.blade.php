@extends('Layouts.erp_master')
@section('content')
<!-- Page -->

<div class="panel-body">
    <form action="" method="" data-toggle="validator" novalidate="true">
    <div class="row">
        <div class="col-lg-12">
            <div class="row mb-2 align-items-center">
                <label class="col-lg-2 input-title" for="sales_type">Sales Type</label>
                <div class="col-lg-2 input-group">
                    <select class="form-control round browser-default" data-plugin="selectpicker" 
                    data-style="btn-outline btn-primary" name="sales_type" id="sales_type">
                                <option value="0">Select Sales Type</option>
                                @foreach ($misType as $Row)
                                <option value="{{$Row->id}}" >{{$Row->name}}</option>
                                @endforeach
                    </select>
                </div>
                ​
                <label class="col-lg-2 input-title" for="AutoVoucherConfigVoucher">Voucher Type</label>
                ​
                <div class="col-lg-2 input-group">
                    <select class="form-control round browser-default" data-plugin="selectpicker" data-style="btn-outline btn-primary" name="AutoVoucherConfigVoucher" id="AutoVoucherConfigVoucher">
                        <option value="">Select Voucher Type</option>
                                @foreach ($vdata as $Row)
                                <option value="{{$Row->id}}" >{{$Row->name}}</option>
                                @endforeach
                    </select>
                </div>

                <div class="col-lg-4">
                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button>
                </div>
            </div>
        </div>
    </div>
    </form>

    <div class="row">
    <div class="col-lg-12">
    <table class="table w-full table-hover table-bordered table-striped dataTable" data-plugin="dataTable">
        <thead>
            <tr>
                <th>MIS Name</th>
                <th>Ledger Code</th>
                <th>Amount Type</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cash</td>
                <td>
                <input type="text" class="form-control" id="LedgerCode" name="LedgerCode">
                </td>
                <td>
                <input type="text" class="form-control" id="AmountType" name="AmountType">
                </td>
            </tr>
            {{-- <tr>
                <td>Sale</td>
                <td>
                <input type="text" class="form-control" id="LedgerCode" name="LedgerCode">
                </td>
                <td>
                <input type="text" class="form-control" id="AmountType" name="AmountType">
                </td>
            </tr>
            <tr>
                <td>Cost of goods sold</td>
                <td>
                <input type="text" class="form-control" id="LedgerCode" name="LedgerCode">
                </td>
                <td>
                <input type="text" class="form-control" id="AmountType1" name="AmountType">
                </td>
            </tr>
            <tr>
                <td>Inventory</td>
                <td>
                <input type="text" class="form-control" id="LedgerCode" name="LedgerCode">
                </td>
                <td>
                <input type="text" class="form-control" id="AmountType2" name="AmountType">
                </td>
            </tr> --}}
        </tbody>
    </table>

    <div class="form-row align-items-center">
        <label class="col-lg-2 input-title" for="LocalNarration">Local Narration</label>
        <div class="col-lg-10 form-group">
            <div class="input-group ">
                <input type="text" class="form-control round" id="LocalNarration" name="LocalNarration" placeholder="Please enter Narration.">
            </div>
        </div>
    </div>

    <div class="form-row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="#" class="btn btn-default btn-round">Close</a>
                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit</button>
                    <button type="button" class="btn btn-warning btn-round">Reset</button>
                </div>
            </div>
        </div>
    </div>

    </div> 
</div>           


<script>
$(document).ready(function() {

    $('#sales_type').change(function() {

        if ($(this).val() != null) {

            
            var ID = $('#sales_type').val();
            console.log(ID);
            $.ajax({
                method: "GET",
                url: "{{url('/ajaxAutoVoucheritem')}}",
                dataType: "json",
                data: {
                    ID: ID
                },
                success: function(data) {
                     console.log(data);
                   
                }
            });
          
        }
    });

    $('#sale_price').keyup(function() {
        if ($(this).val() != '') {
              $.ajax({
                method: "GET",
                url: "{{url('/ajaxSupplierInfo')}}",
                dataType: "json",
                data: {
                    ID: ID
                },
                success: function(data) {
                   
                }
            });
        }
    });

});
</script>


<!-- End Page -->
@endsection
