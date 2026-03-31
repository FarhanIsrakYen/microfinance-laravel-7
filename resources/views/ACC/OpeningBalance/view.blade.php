@extends('Layouts.erp_master')
@section('content')
<?php 
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-8 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($OBDataM->company_id, 'disabled') !!}
    </div>
</div>

<div class="row">
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <th colspan="4">
                 Opening Balance Information
                </th>
            </thead>
            <tbody style="color: #000;">
                <tr>
                    <th width="20%">Branch</th>
                    <td width="20%">{{$OBDataM->branch['branch_name']}}</td>

                    <th width="20%">Opening Date </th>
                    <td width="20%">{{date('d-m-Y', strtotime($OBDataM->opening_date))}}</td>
                      </tr>
                      <tr>
                    <th width="20%">Project</th>
                    <td width="20%">{{$OBDataM->project['project_name']}}</td>

                    <th width="20%">Project Type</th>
                    <td width="20%">{{$OBDataM->projectType['project_type_name']}}</td>

                </tr>
            </tbody>

        </table>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <!-- <th width="20%">Account Type</th> -->
                    <th width="20%" class="text-left">Account Head</th>
                    <th width="20%" class="text-right">Debit Amount</th>
                    <th width="20%" class="text-right">Credit Amount</th>
                    <th width="20%" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($OBDataD as $data)
                <tr>
                    <td> {{$data->ledger['name']}}</td>
                    <td class="text-right"><input type="hidden" class="clsQuantity"
                          >{{ $data->debit_amount}}</td>
                    <td class="text-right">{{ $data->credit_amount}}</td>
                    <td class="text-right"><input type="hidden" class="clsTotal">{{ $data->balance_amount}}</td>

                </tr>

                @endforeach

                <tr>
                    <th width="20%" class="text-right">Total:</th>
                    <td width="20%" class="text-right">{{ $OBDataM->total_debit_amount}}</td>

                    <th width="20%" class="text-right">{{ $OBDataM->total_credit_amount}} </th>
                    <td width="20%" class="text-right">{{ $OBDataM->total_balance}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                <a href="javascript:void(0)" onClick="window.print();"
                    class="btn btn-default btn-round clsPrint d-print-none">Print</a>
            </div>
        </div>
    </div>
</div>

@endsection
