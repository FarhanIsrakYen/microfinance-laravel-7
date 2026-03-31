@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="4" style="color: #000;">
                    Share Acconut Information
                </th>
            </tr>
        </thead>
        <tbody style="color: #000;">

            <tr>
                <td width="20%">Member</td>
                <td width="20%">{{$member->member}}</td>

                <td width="20%">Number of Shares
                </td>
                <td width="20%">{{$ShareAcc->numberOfShare}}</td>
            </tr>
            <tr>
                <td width="20%">Purchase Date</td>
                <td width="20%">{{ \Carbon\Carbon::parse($ShareAcc->purchaseDate)->format('d-m-Y')}}</td>

                <td width="20%">Unit Price
                </td>
                <td width="20%">{{$ShareAcc->unitPrice}}</td>
            </tr>
            <tr>
                <td width="20%">Status</td>
                <td width="20%">{{ $ShareAcc->status}}</td>

                <td width="20%">Total Price
                </td>
                <td width="20%">{{$ShareAcc->totalPrice}}</td>
            </tr>
        </tbody>
    </table>
    <div class="row align-items-center  d-print-none">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                    <a href="javascript:void(0)" onClick="window.print();" class="btn btn-default btn-round clsPrint d-print-none">Print</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->


@endsection
