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
                <th rowspan="2">FUNDING ORGANIZATION</th>
                <th rowspan="2">LOAN PRODUCT</th>
                <th colspan="2">DEBIT</th>
                <th colspan="2">CREDIT</th>
            </tr>
            <tr>
                <th>DEBIT PRINCIPAL CODE</th>
                <th>DEBIT SERVICE CHARGE CODE</th>
                <th>CREDIT PRINCIPAL CODE</th>
                <th>CREDIT SERVICE CHARGE CODE</th>
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
                            ->where('headFor', 'Debit')
                            ->where('loanProductId', $loanProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentDebitPrincipals[' . $component->id . '][' . $loanProduct->id . ']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Debit')
                            ->where('loanProductId', $loanProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentDebitInterests[' . $component->id . '][' . $loanProduct->id . ']' }}"
                        value="{{ $value }}">
                </td>
                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Credit')
                            ->where('loanProductId', $loanProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentCreditPrincipals[' . $component->id . '][' . $loanProduct->id . ']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Credit')
                            ->where('loanProductId', $loanProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanProductComponentCreditInterests[' . $component->id . '][' . $loanProduct->id . ']' }}"
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
                <th rowspan="2">FUNDING ORGANIZATION</th>
                <th rowspan="2">LOAN PRODUCT</th>
                <th rowspan="2">SAVINGS PRODUCT</th>
                <th colspan="3">DEBIT</th>
                <th colspan="3">CREDIT</th>
            </tr>
            <tr>
                <th>DEBIT PRINCIPAL CODE</th>
                <th>DEBIT SERVICE CHARGE CODE</th>
                <th>DEBIT POVISION CODE</th>
                <th>CREDIT PRINCIPAL CODE</th>
                <th>CREDIT SERVICE CHARGE CODE</th>
                <th>CREDIT POVISION CODE</th>
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
                            ->where('headFor', 'Debit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductDebitPrincipals[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Debit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductDebitInterests[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>
                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Debit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestProvisionLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductDebitProvitions[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}"></td>
                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Credit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('principalLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductCreditPrincipals[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Credit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductCreditInterests[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
                        value="{{ $value }}">
                </td>

                <td>
                    @php
                        $ledgerId = $configurations
                            ->where('headFor', 'Credit')
                            ->where('loanProductId', $loanProduct->id)
                            ->where('savingsProductId', $savingsProduct->id)
                            ->max('interestProvisionLedgerId');
                        $value = $ledgers->where('id', $ledgerId)->max('code');
                    @endphp
                    <input type="text"
                        name="{{ 'loanAndSavingsProductCreditProvitions[' . $component->id . '][' . $loanProduct->id . ']['. $savingsProduct->id .']' }}"
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

