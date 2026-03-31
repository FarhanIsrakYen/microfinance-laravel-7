<div class="row text-center d-print-block">
    <div class="col-lg-12" style="color:#000;">
        <strong>{{$branch->comp_name}}</strong><br>
        <span>{{$branch->comp_addr}}</span><br>
        <strong>Member Migration Balance</strong><br>
    </div>
</div>
<div class="row d-print-none text-right" data-html2canvas-ignore="true">
    <div class="col-lg-12">
        <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;"
            class="btnPrint mr-2">
            <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="getPDF();">
            <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
        </a>
        <a href="javascript:void(0)" style="background-color:transparent;border:none;" onclick="fnDownloadXLSX();">
            <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
        </a>
    </div>
</div>

<div class="row">

    <div class="col-lg-12" style="font-size: 12px;">
        <span style="color: black; float: right;">
            <table style="border-collapse:separate;
            border-spacing:10px 10px;">
                <tbody>
                    <tr>
                        <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Reporting Date: </span>
                                <span>
                                    {{ (\Carbon\Carbon::parse($date)->format('d-m-Y'))}}</span>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="color: black;" class="float-left">
                                <span style="font-weight: bold;">Print at : </span>
                                <span> {{(\Carbon\Carbon::now()->format('d-m-Y H:m:s'))}}</span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </span>

        <table style="border-collapse:separate;
        border-spacing:10px 10px;">
            <tbody>
                <tr>
                    <td>
                        <span style="color: black;" class="float-left">
                            <span style="font-weight: bold;">Branch : </span>
                            <span> {{ $branch->branch_code . ' - ' . $branch->branch_name}}</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<table class="table table-striped table-bordered">
    <thead class="text-center">

        <tr>
            <th rowspan="2">SL#</th>
            <th rowspan="2">Samity Code</th>
            <th rowspan="2">Samity Name</th>
            <th colspan="{{$withServiceCharge + 8}}">Loan Information</th>
            <th colspan="4">Savings Information</th>
        </tr>
        <tr>
            {{-- loan --}}
            <th>Product</th>
            <th>Gender</th>
            <th>Member No.</th>
            <th>Disburse Amount</th>
            <th>Principal Recovery</th>
            @if($withServiceCharge)
            <th>Service Charge Recovery</th>
            @endif
            <th>Outstanding Balance</th>
            <th>Rebate</th>
            <th>Overdue</th>
            {{-- Savings --}}
            @foreach ($savProducts as $savProduct)
            <th>{{ $savProduct->name }}</th>
            @endforeach

        </tr>
    </thead>
    <tbody>
        @php
        $slNo = 1;
        $currentSamityId = 0;
        @endphp
        @foreach ($samities as $key => $samity)
        @php
        $samityMembers = $members->where('samityId', $samity->id);
        $samityLoans = $loans->where('samityId', $samity->id);

        $samityProductIds = array_merge($samityMembers->pluck('primaryProductId')->toArray(),
        $samityLoans->pluck('productId')->toArray());

        $samityProducts = $loanProducts->whereIn('id', $samityProductIds);

        $rowSpan = $samityProducts->count() * count($genders);

        $currentProductId = 0;

        @endphp

        @foreach ($samityProducts as $samityProduct)
        @foreach ($genders as $gender)
        @php
        // calculate data
        $memberNo = $samityMembers->where('primaryProductId', $samityProduct->id)->where('gender', $gender)->count();

        $duplicateMemberNo = $loans->where('samityId', $samity->id)->where('productId',
        $samityProduct->id)->where('primaryProductId', '!=', $samityProduct->id)->where('gender', $gender)->count();

        $disburseAmount = $loans->where('samityId', $samity->id)->where('productId',
        $samityProduct->id)->where('gender', $gender)->sum('loanAmount');

        $principalRecovery = $loans->where('samityId', $samity->id)->where('productId',
        $samityProduct->id)->where('gender', $gender)->sum('principalRecovery');

        $serviceChargeRecovery = $loans->where('samityId', $samity->id)->where('productId',
        $samityProduct->id)->where('gender', $gender)->sum('serviceChargeRecovery');

        if ($withServiceCharge) {
        $outStanding = $loans->where('samityId', $samity->id)->where('productId', $samityProduct->id)->where('gender',
        $gender)->sum('totalOutStanding');

        $dueAmount = $loans->where('samityId', $samity->id)->where('productId', $samityProduct->id)->where('gender',
        $gender)->sum('dueAmount');
        }
        else{
        $outStanding = $loans->where('samityId', $samity->id)->where('productId', $samityProduct->id)->where('gender',
        $gender)->sum('outStandingPrinciple');

        $dueAmount = $loans->where('samityId', $samity->id)->where('productId', $samityProduct->id)->where('gender',
        $gender)->sum('dueAmountPrincipal');
        }

        $rebateAmount = $loans->where('samityId', $samity->id)->where('productId', $samityProduct->id)->where('gender',
        $gender)->sum('rebateAmount');

        @endphp
        <tr>
            @if ($currentSamityId != $samity->id)
            @php
            $currentSamityId = $samity->id;
            @endphp
            <td rowSpan={{ $rowSpan }}>{{ $slNo++ }}</td>
            <td rowSpan={{ $rowSpan }}>{{ $samity->samityCode }}</td>
            <td rowSpan={{ $rowSpan }}>{{ $samity->name }}</td>
            @endif

            @if ($currentProductId != $samityProduct->id)
            @php
            $currentProductId = $samityProduct->id;
            @endphp

            <td rowspan="{{ count($genders) }}">{{ $samityProduct->name }}</td>
            @endif

            <td>{{ $gender }}</td>
            <td class="text-center">{{ ($memberNo + $duplicateMemberNo) . ' (' . $duplicateMemberNo . '*)' }}</td>
            <td class="text-right">{{ number_format($disburseAmount, 2) }}</td>
            <td class="text-right">{{ number_format($principalRecovery, 2) }}</td>
            @if($withServiceCharge)
            <td class="text-right">{{ number_format($serviceChargeRecovery, 2) }}</td>
            @endif
            <td class="text-right">{{ number_format($outStanding, 2) }}</td>
            <td class="text-right">{{ number_format($rebateAmount, 2) }}</td>
            <td class="text-right">{{ number_format($dueAmount, 2) }}</td>

            @foreach ($savProducts as $savProduct)
            @php
            $savMemberIds = $samityMembers->where('primaryProductId', $samityProduct->id)->where('gender',
            $gender)->pluck('id')->toArray();

            $balance = $deposits->whereIn('memberId', $savMemberIds)->where('savingsProductId',
            $savProduct->id)->sum('amount') - $withdraws->whereIn('memberId', $savMemberIds)->where('savingsProductId',
            $savProduct->id)->sum('amount');

            @endphp
            <td class="text-right">{{ number_format($balance, 2) }}</td>
            @endforeach
        </tr>
        @endforeach
        @endforeach
        {{-- samity total --}}
        @php
        $duplicateMemberNo = count($loans->where('samityId', $samity->id)->filter(function ($obj) {
        return $obj->productId != $obj->primaryProductId;
        }));
        @endphp
        <tr style="font-weight: bold">
            <td colspan="5" class="text-center">Samity Total</strong></td>
            <td class="text-center">
                {{ $members->where('samityId', $samity->id)->count() . ' (' .$duplicateMemberNo .'*)' }}</td>

            <td class="text-right">{{ number_format($loans->where('samityId', $samity->id)->sum('loanAmount') ,2) }}
            </td>
            <td class="text-right">
                {{ number_format($loans->where('samityId', $samity->id)->sum('principalRecovery') ,2) }}</td>

            @if($withServiceCharge)
            <td class="text-right">
                {{ number_format($loans->where('samityId', $samity->id)->sum('serviceChargeRecovery') ,2) }}</td>
            <td class="text-right">
                {{ number_format($loans->where('samityId', $samity->id)->sum('totalOutStanding') ,2) }}</td>
            @else
            <td class="text-right">
                {{ number_format($loans->where('samityId', $samity->id)->sum('outStandingPrinciple') ,2) }}</td>
            @endif
            <td class="text-right">{{ number_format($loans->where('samityId', $samity->id)->sum('rebateAmount') ,2) }}
            </td>
            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->where('samityId', $samity->id)->sum('dueAmount') ,2) }}</td>
            @else
            <td class="text-right">
                {{ number_format($loans->where('samityId', $samity->id)->sum('dueAmountPrincipal') ,2) }}</td>
            @endif

            {{-- Savinggs --}}
            @foreach ($savProducts as $savProduct)
            @php
            $balance = $deposits->where('samityId', $samity->id)->where('savingsProductId',
            $savProduct->id)->sum('amount') -
            $withdraws->where('samityId', $samity->id)->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($balance ,2) }}</td>
            @endforeach
        </tr>
        {{-- end samity total --}}
        @endforeach

        {{-- branch Total --}}
        @php
        $duplicateMemberNo = count($loans->filter(function ($obj) {
        return $obj->productId != $obj->primaryProductId;
        }));
        @endphp
        <tr style="font-weight: bold">
            <td colspan="5" class="text-center">Branch Total </strong></td>
            <td class="text-center">{{ $members->count() . ' (' .$duplicateMemberNo .'*)' }}</td>

            <td class="text-right">{{ number_format($loans->sum('loanAmount') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('principalRecovery') ,2) }}</td>

            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('serviceChargeRecovery') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('totalOutStanding') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('outStandingPrinciple') ,2) }}</td>
            @endif
            <td class="text-right">{{ number_format($loans->sum('rebateAmount') ,2) }}</td>
            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('dueAmount') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('dueAmountPrincipal') ,2) }}</td>
            @endif

            {{-- Savinggs --}}
            @foreach ($savProducts as $savProduct)
            @php
            $balance = $deposits->where('savingsProductId', $savProduct->id)->sum('amount') -
            $withdraws->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($balance ,2) }}</td>
            @endforeach
        </tr>
        {{-- end branch Total --}}

    </tbody>
</table>

<div>
    <p>* Double Counting Members Avoided</p>
</div>

{{-- product wise information --}}
<table class="table table-striped table-bordered">
    <thead class="text-center">
        <tr>
            <th colspan="{{ 8 + $withServiceCharge + count($savProducts) }}">Product Wise Information</th>
        </tr>
        <tr>
            <th colspan="{{ 8 + $withServiceCharge }}">Loan Information</th>
            <th colspan="{{ count($savProducts) }}">Savings Information</th>
        </tr>
        <tr>
            <th>Product</th>
            <th>Gender</th>
            <th>Member No</th>
            <th>Disbursed Amount</th>
            <th>Principal Recovery</th>
            @if ($withServiceCharge)                
            <th>Service Charge Recovery</th>
            @endif
            <th>Loan Balance</th>
            <th>Rebate</th>
            <th>Overdue</th>

            @foreach ($savProducts as $savProduct)
            <th>{{ $savProduct->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($loanProducts as $loanProduct)
        @foreach ($genders as $key => $gender)
        @php
        $memberNo = $members->where('primaryProductId', $loanProduct->id)->where('gender', $gender)->count();

        $duplicateMemberNo = count($loans->where('productId', $loanProduct->id)->where('gender',
        $gender)->where('primaryProductId', '!=', $loanProduct->id));

        $disburseAmount = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('loanAmount');

        $principalRecovery = $loans->where('productId', $loanProduct->id)->where('gender',
        $gender)->sum('principalRecovery');

        $serviceChargeRecovery = $loans->where('productId', $loanProduct->id)->where('gender',
        $gender)->sum('serviceChargeRecovery');

        if ($withServiceCharge) {
        $outStanding = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('totalOutStanding');

        $dueAmount = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('dueAmount');
        }
        else{
        $outStanding = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('outStandingPrinciple');

        $dueAmount = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('dueAmountPrincipal');
        }

        $rebateAmount = $loans->where('productId', $loanProduct->id)->where('gender', $gender)->sum('rebateAmount');
        @endphp
        <tr>
            @if ($key == 0)
            <td rowspan="{{ count($genders) }}">{{ $loanProduct->name }}</td>
            @endif
            <td class="text-center">{{ $gender }}</td>
            <td class="text-center">{{ $memberNo . ' (' .$duplicateMemberNo .'*)' }}</td>
            <td class="text-right">{{ number_format($disburseAmount, 2) }}</td>
            <td class="text-right">{{ number_format($principalRecovery, 2) }}</td>
            @if ($withServiceCharge)
            <td class="text-right">{{ number_format($serviceChargeRecovery, 2) }}</td>                
            @endif
            <td class="text-right">{{ number_format($outStanding ,2) }}</td>
            <td class="text-right">{{ number_format($dueAmount ,2) }}</td>
            <td class="text-right">{{ number_format($rebateAmount ,2) }}</td>

            @foreach ($savProducts as $savProduct)
            @php
            $savMemberIds = $members->where('primaryProductId', $loanProduct->id)->where('gender',
            $gender)->pluck('id')->toArray();

            $savBalance = $deposits->whereIn('memberId', $savMemberIds)->where('savingsProductId',
            $savProduct->id)->sum('amount') - $withdraws->whereIn('memberId', $savMemberIds)->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp

            <td class="text-right">{{ number_format($savBalance ,2) }}</td>
            @endforeach
        </tr>
        @endforeach
        @endforeach

        {{-- branch Total --}}
        @php
        $duplicateMemberNo = count($loans->filter(function ($obj) {
        return $obj->productId != $obj->primaryProductId;
        }));
        @endphp
        <tr style="font-weight: bold">
            <td colspan="2" class="text-center">Total</td>
            <td class="text-center">{{ $members->count() . ' (' .$duplicateMemberNo .'*)' }}</td>

            <td class="text-right">{{ number_format($loans->sum('loanAmount') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('principalRecovery') ,2) }}</td>

            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('serviceChargeRecovery') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('totalOutStanding') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('outStandingPrinciple') ,2) }}</td>
            @endif
            <td class="text-right">{{ number_format($loans->sum('rebateAmount') ,2) }}</td>
            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('dueAmount') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('dueAmountPrincipal') ,2) }}</td>
            @endif

            {{-- Savinggs --}}
            @foreach ($savProducts as $savProduct)
            @php
            $balance = $deposits->where('savingsProductId', $savProduct->id)->sum('amount') -
            $withdraws->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($balance ,2) }}</td>
            @endforeach
        </tr>
        {{-- end branch Total --}}

    </tbody>
</table>
{{-- end product wise information --}}

{{-- product-category wise information --}}

<table class="table table-striped table-bordered">
    <thead class="text-center">
        <tr>
            <th colspan="{{ 7 + $withServiceCharge + count($savProducts) }}">Product Category Wise Information</th>
        </tr>
        <tr>
            <th colspan="{{ 7 + $withServiceCharge }}">Loan Information</th>
            <th colspan="{{ count($savProducts) }}">Savings Information</th>
        </tr>
        <tr>
            <th>Product Category</th>
            <th>Member No</th>
            <th>Disbursed Amount</th>
            <th>Principal Recovery</th>
            @if ($withServiceCharge)                
            <th>Service Charge Recovery</th>
            @endif
            <th>Loan Balance</th>
            <th>Rebate</th>
            <th>Overdue</th>

            @foreach ($savProducts as $savProduct)
            <th>{{ $savProduct->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($loanProductCategories as $loanProductCategory)
        @php
        $loanProductIds = $loanProducts->where('productCategoryId', $loanProductCategory->id)->pluck('id')->toArray();
        $memberNo = $members->whereIn('primaryProductId', $loanProductIds)->where('gender', $gender)->count();

        $duplicateMemberNo = count($loans->whereIn('productId', $loanProductIds)->filter(function ($obj) {
        return $obj->productId != $obj->primaryProductId;
        }));

        $disburseAmount = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('loanAmount');

        $principalRecovery = $loans->whereIn('productId', $loanProductIds)->where('gender',
        $gender)->sum('principalRecovery');

        $serviceChargeRecovery = $loans->whereIn('productId', $loanProductIds)->where('gender',
        $gender)->sum('serviceChargeRecovery');

        if ($withServiceCharge) {
        $outStanding = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('totalOutStanding');

        $dueAmount = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('dueAmount');
        }
        else{
        $outStanding = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('outStandingPrinciple');

        $dueAmount = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('dueAmountPrincipal');
        }

        $rebateAmount = $loans->whereIn('productId', $loanProductIds)->where('gender', $gender)->sum('rebateAmount');
        @endphp
        <tr>
            <td>{{ $loanProductCategory->name }}</td>
            <td class="text-center">{{ $memberNo . ' (' .$duplicateMemberNo .'*)' }}</td>
            <td class="text-right">{{ number_format($disburseAmount ,2) }}</td>
            <td class="text-right">{{ number_format($principalRecovery ,2) }}</td>
            @if ($withServiceCharge)
            <td class="text-right">{{ number_format($serviceChargeRecovery ,2) }}</td>                
            @endif
            <td class="text-right">{{ number_format($outStanding ,2) }}</td>
            <td class="text-right">{{ number_format($dueAmount ,2) }}</td>
            <td class="text-right">{{ number_format($rebateAmount ,2) }}</td>

            @foreach ($savProducts as $savProduct)
            @php
            $savMemberIds = $members->whereIn('primaryProductId', $loanProductIds)->where('gender',
            $gender)->pluck('id')->toArray();

            $savBalance = $deposits->whereIn('memberId', $savMemberIds)->where('savingsProductId',
            $savProduct->id)->sum('amount') - $withdraws->whereIn('memberId', $savMemberIds)->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp

            <td class="text-right">{{ number_format($savBalance ,2) }}</td>
            @endforeach
        </tr>
        @endforeach

        {{-- branch Total --}}
        @php
        $duplicateMemberNo = count($loans->filter(function ($obj) {
        return $obj->productId != $obj->primaryProductId;
        }));
        @endphp
        <tr style="font-weight: bold">
            <td class="text-center">Total</td>
            <td class="text-center">{{ $members->count() . ' (' .$duplicateMemberNo .'*)' }}</td>

            <td class="text-right">{{ number_format($loans->sum('loanAmount') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('principalRecovery') ,2) }}</td>

            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('serviceChargeRecovery') ,2) }}</td>
            <td class="text-right">{{ number_format($loans->sum('totalOutStanding') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('outStandingPrinciple') ,2) }}</td>
            @endif
            <td class="text-right">{{ number_format($loans->sum('rebateAmount') ,2) }}</td>
            @if($withServiceCharge)
            <td class="text-right">{{ number_format($loans->sum('dueAmount') ,2) }}</td>
            @else
            <td class="text-right">{{ number_format($loans->sum('dueAmountPrincipal') ,2) }}</td>
            @endif

            {{-- Savinggs --}}
            @foreach ($savProducts as $savProduct)
            @php
            $balance = $deposits->where('savingsProductId', $savProduct->id)->sum('amount') -
            $withdraws->where('savingsProductId', $savProduct->id)->sum('amount');
            @endphp
            <td class="text-right">{{ number_format($balance ,2) }}</td>
            @endforeach
        </tr>
        {{-- end branch Total --}}
    </tbody>
</table>

{{-- end product-category wise information --}}