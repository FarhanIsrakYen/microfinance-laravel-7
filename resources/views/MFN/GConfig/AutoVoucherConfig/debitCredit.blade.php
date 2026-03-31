@php
$currentFundingOrgId = 0;
$currentLoanProductId = 0;
@endphp
<div class="col-lg-12">
    {{-- loanProduct --}}
    @if ($component->relatedTo == 'loanProduct')
    <table class="table w-full table-hover table-bordered table-striped">
        <thead>
            <tr>
                <th>FUNDING ORGANIZATION</th>
                <th>LOAN PRODUCT</th>
                <th>PRINCIPAL CODE</th>
                <th>SERVICE CHARGE CODE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fundingOrgs as $fundingOrg)
            @foreach ($loanProducts as $loanProduct)
            <tr>
                @if ($fundingOrg->id != $currentFundingOrgId)
                @php
                $currentFundingOrgId = $fundingOrg->id;
                @endphp
                <td rowspan=" {{ $fundingOrg->loanRowSpan }} " style="width: 20%;">
                    {{ $fundingOrg->name }}
                </td>
                @endif
                <td>
                    {{ $loanProduct->productCode . ' - ' . $loanProduct->name }}
                </td>
                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('loanProductId', $loanProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentPrincipals[' . $component->id . '][' . $loanProduct->id . ']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('loanProductId', $loanProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentInterests[' . $component->id . '][' . $loanProduct->id . ']' }}"
                        value="{{ $value }}">
                </td>
            </tr>
            @endforeach {{-- Loan Producr --}}
            @endforeach {{-- Funding Org --}}
        </tbody>
    </table>
    @endif
    {{-- end loanProduct --}}

    {{-- loanAndSavingsProduct --}}
    @if ($component->relatedTo == 'loanAndSavingsProduct')
    <table class="table w-full table-hover table-bordered table-striped">
        <thead>
            <tr>
                <th>FUNDING ORGANIZATION</th>
                <th>LOAN PRODUCT</th>
                <th>SAVINGS PRODUCT</th>
                <th>PRINCIPAL CODE</th>
                <th>SERVICE CHARGE CODE</th>
                <th>POVISION CODE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($primaryProductFundingOrgs as $fundingOrg)
            @foreach ($primaryLoanProducts as $loanProduct)
            @foreach ($savingsProducts as $savingsProduct)
            <tr>

                @if ($fundingOrg->id != $currentFundingOrgId)
                @php
                $currentFundingOrgId = $fundingOrg->id;
                @endphp
                <td rowspan=" {{ $fundingOrg->loanAndSavingsRowSpan }} " style="width: 20%;">
                    {{ $fundingOrg->name }}</td>
                @endif

                @if ($loanProduct->id != $currentLoanProductId)
                @php
                $currentLoanProductId = $loanProduct->id;
                @endphp
                <td rowspan=" {{ $savingsProducts->count() }} " style="width: 20%;">
                    {{ $loanProduct->productCode . ' - ' . $loanProduct->name }}</td>
                @endif

                <td>{{ $savingsProduct->name }}</td>
                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductPrincipals[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductInterests[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestProvisionLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductProvitions[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}"></td>
            </tr>
            @endforeach {{-- Savings Product --}}
            @endforeach {{-- Loan Product --}}
            @endforeach {{-- Funding Org --}}
        </tbody>
    </table>
    @endif
    {{-- end loanAndSavingsProduct --}}
</div>
