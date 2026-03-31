
<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();
$BranchID = Common::getBranchId();
$branchInfo = Common::ViewTableFirst('gnl_branchs', [['is_delete', 0], ['is_active', 1], ['id', $BranchID]], ['id', 'branch_name','branch_addr']);
$groupInfo = Common::ViewTableFirst('gnl_groups', [['is_delete', 0], ['is_active', 1]], ['id', 'group_name','group_addr']);

?>
<style type="text/css">
    .main {  
        position: relative;
        width: 100%;
    }  
    .bottom { 
        position:absolute;                  
        bottom:0;  
    }
</style>

<div class="text-dark ExportHeading">
    {{-- <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12" style="text-align:center;"> --}}
        <div class="row">
            <div class="main col-xl-4 col-lg-4 col-sm-4 col-md-4 col-4">
                <span class="bottom" style="font-size: 12px;color: #000">
                    <br>
                    <?php
                        $count = 1;
                        $max = 4;
                    ?>

                    @if(isset($customerDesig) && $customerDesig)
                        <?php $count++; ?>
                    @endif

                    @if(isset($ledgerHead) && $ledgerHead)
                        <?php $count++; ?>
                    @endif

                    @if(isset($projectName) && $projectName)
                        <?php $count++; ?>
                    @endif

                    @if(isset($projectTypeName) && $projectTypeName)
                        <?php $count++; ?>
                    @endif

                    <?php $count = $max - $count; ?>
                    @if($count > 0)
                    @for ($i = 0; $i <= $count; $i++)
                        <br>
                    @endfor
                    @endif

                    @if(isset($customerDesig) && $customerDesig)
                    <?php $count++; ?>
                    <span>
                        <strong><span id="designation"></span> Name:</strong> 
                        <span id="empName"></span>
                    </span>
                    <br>
                    @endif

                    @if(isset($ledgerHead) && $ledgerHead)
                    <?php $count++; ?>
                    <span>
                        <strong>Ledger Head: </strong>
                        <span id="ledgerHead"></span>
                    </span>
                    <br>
                    @endif

                    @if(isset($projectName) && $projectName)
                    <?php $count++; ?>
                    <span>
                        <strong>Project Name: </strong>
                        <span id="projectName"></span>
                    </span>
                    <br>
                    @endif

                    @if(isset($projectTypeName) && $projectTypeName)
                    <?php $count++; ?>
                    <span>
                        <strong>Project Type: </strong>
                        <span id="projectTypeName"></span>
                    </span>
                    <br>
                    @endif

                    <strong>Period :</strong>
                    <span id="start_date_txt">{{ $StartDate }}</span>
                    <span id="text_to">to</span>
                    <span id="end_date_txt">{{ $EndDate }}</span>
                </span>
            </div>

            <div class="col-xl-4 col-lg-4 col-sm-4 col-md-4 col-4" style="text-align: center;">
                <strong class="text-uppercase">{{ $groupInfo->group_name }}</strong><br>
                <span style="font-size: 11px; color:#000;">Address : {{ $groupInfo->group_addr }}</span><br>
                <strong style="font-size: 13px">Branch : <span id="reportBranch">{{ $branchInfo->branch_name }}</span></strong><br>
                <span style="color:#000; text-align:center;">{{ $title }}</span>
            </div>

            <div class="col-xl-4 col-lg-4 col-sm-4 col-md-4 col-4" style="text-align: right">
                <span class="d-print-none">
                    <a href="javascript:void(0)" onClick="window.print();"
                    style="background-color:transparent;border:none;" class="btnPrint">
                        <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                    </a>
                    <a href="javascript:void(0)" style="background-color:transparent;border:none;"
                    onclick="getDownloadPDF();">
                        <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>

                    <a href="javascript:void(0)" title="Excel" style="background-color:transparent;border:none;"
                        onclick="fnDownloadExcel('ExportHeading,ExportDiv', '{{ $title_excel }}_{{ (new Datetime())->format('d-m-Y') }}');">
                        <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                    </a>
                </span>
                <br><br>
                @if(isset($totalCustomer) && $totalCustomer)
                    <span  style="font-size: 12px;color: #000"><strong>Total Customer:</strong> <span id="totalRowDiv">0</span><span><br>
                @else 
                    <br>
                @endif
                
                <span style="color:#000;font-size:12px;"><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }}</span>
            </div>
            
        </div>
    {{-- </div> --}}
</div>


