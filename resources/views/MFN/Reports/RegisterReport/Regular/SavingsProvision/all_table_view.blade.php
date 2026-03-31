<div class="panel" id="reportTableDiv">
    <div class="panel-body">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branch }}</strong><br>
                <span>{{ $branchAddress }}</span><br>
                <span>Savings Provision Report</span><br>
                <span>Reporting Date: {{ $dateFrom }} to {{ $dateTo }}</span>
            </div>
        </div>
        <div class="row d-print-none text-right" data-html2canvas-ignore="true">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;"
                    class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <td>SL#</td>
                                <td>Samity Code</td>
                                <td>Samity Name</td>
                                <td>Provision Amount</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @foreach ($savingsProvision as $row)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $row->samityId }}</td>
                                    <td>{{ $row->samityName }}</td>
                                    <td class="text-right">{{ $row->provisionAmount }}</td>
                                </tr>                                
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-center"><b>Total</b></td>
                                <td class="text-right"><b>{{ $savingsProvision->sum('provisionAmount') }}</b></td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <table class="table w-full table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <td>SL#</td>
                                <td>Product Name</td>
                                <td class="text-right">Total Provision Amount</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $j = 1; ?>
                            @foreach ($savingsProvisionProduct as $product)
                                <tr>
                                    <td>{{ $j++ }}</td>
                                    <td>{{ $product->primaryProduct }}</td>
                                    <td class="text-right">{{ $product->provisionAmount }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-center"><b>Total</b></td>
                                <td class="text-right"><b>{{ $savingsProvisionProduct->sum('provisionAmount') }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>