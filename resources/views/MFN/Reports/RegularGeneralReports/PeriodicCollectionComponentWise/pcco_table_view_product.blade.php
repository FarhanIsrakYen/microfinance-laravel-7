<?php
    $subTotals=[];
    $totals = [];
    $allLoanInfos= [];
    $tempArray = array('AdditionalFees' => 0 ,'InsuranceAmount'=> 0,'ServiceChargePhotography'=> 0, 'DisbursementAmount' => 0 ,'RegularRecoverable' => 0 ,'Regular' => 0 ,'Due' => 0 ,'Advance' => 0 ,'Rebate' => 0 ,'Principal' => 0 ,'ServiceChanrge' => 0, 'Total' => 0 );                
    foreach ($savingsProducts as $sp){
        $tempArray["savings-".(string)$sp->id] = 0;
        $tempArray["interest-".(string)$sp->id] = 0;
        $tempArray["refund-".(string)$sp->id] = 0;
    }
    $totals= $tempArray;

    use App\Services\MfnService as MFN;
?>
<style>
    .subtotal > td{
        font-weight: bold;
    }
</style>
<div id="reportTableDiv">
    <table class="table w-full table-hover table-bordered table-striped"  style="font-size: 9.5px">
        <thead class="text-center">
            <tr>
                <th colspan="2">Field Worker</th>
                <th colspan="2">Samity</th>
                <th rowspan="3">Component</th>
                <th colspan="{{count($savingsProducts)}}">Saving Collection</td>
                <th colspan="{{count($savingsProducts)}}">Interest On savings</td>
                <th colspan="{{count($savingsProducts)}}">Saving Refund</td>
                <th rowspan="3">Additional Fees<br>Collection</th>
                <th rowspan="3">Disbursement <br>Amount</th>
                <th rowspan="3">Regular <br>Recoverable</th>
                <th colspan="7">Loan Collection</th>
                <th rowspan="3">Insc. Claim</th>
                <th rowspan="3">Service <br> Charge<br>Photography</th>
            </tr>
            <tr>
                <th rowspan="2">CODE</th>
                <th rowspan="2">Name</th>

                <th rowspan="2">CODE</th>
                <th rowspan="2">Name</th> 
                @foreach ($savingsProducts as $sp)
                <th rowspan="2">{{ $sp->shortName }}</th>
                @endforeach
                @foreach ($savingsProducts as $sp)
                <th rowspan="2">{{ $sp->shortName }}</th>    
                @endforeach
                @foreach ($savingsProducts as $sp)
                <th rowspan="2">{{ $sp->shortName }}</th>    
                @endforeach
                <th rowspan="2">Regular</th>
                <th rowspan="2">Due</th>
                <th rowspan="2">Advance</th>
                <th rowspan="2">Rebate</th>
                <th colspan="3">Total Collection</th>
            </tr>
            <tr>
                <th>Loan Received <br>(Principal)</th>
                <th>Loan Received <br>(Service charge)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($uniqueFieldOfficers as $officerId)
                @php
                   
                    $tfoSamities = $samities->where('fieldOfficerEmpId', $officerId)->values();
                    $officerName = $tfoSamities->first()->fieldOfficerName;
                    $officerCode = $tfoSamities->first()->fieldOfficerCode;

                    //initialize subtotal array
                    $tempArray = array('AdditionalFees' => 0 , 'InsuranceAmount'=> 0,'ServiceChargePhotography'=> 0, 'DisbursementAmount' => 0 ,'RegularRecoverable' => 0 ,'Regular' => 0 ,'Due' => 0 ,'Advance' => 0 ,'Rebate' => 0 ,'Principal' => 0 ,'ServiceChanrge' => 0, 'Total' => 0 );
                    
                    foreach ($savingsProducts as $sp){
                        $tempArray["savings-".(string)$sp->id] = 0;
                        $tempArray["interest-".(string)$sp->id] = 0;
                        $tempArray["refund-".(string)$sp->id] = 0;
                    }
                    $subTotals[$officerName]= $tempArray;

                   
                    $bigrowsize=0;
                    $productsForSamity= array();
                    foreach($tfoSamities as $index => $samity){
                        $primaryProductIds = array();
                        $primaryProductIds = array_merge($primaryProductIds,$deposits->where('samityId',$samity->id)->pluck('primaryProductId')->toArray());
                        $primaryProductIds = array_merge($primaryProductIds,$withdraws->where('samityId',$samity->id)->pluck('primaryProductId')->toArray());
                        $primaryProductIds = array_merge($primaryProductIds,$loans->where('samityId',$samity->id)->pluck('productId')->toArray());
                        $primaryProductIds = array_merge($primaryProductIds,$loanCollection->where('samityId',$samity->id)->pluck('productId')->toArray());
                        $primaryProductIds = array_unique($primaryProductIds);
                        $products = $loanProducts->whereIn('id',$primaryProductIds);
                        $productsForSamity[$index] = $products;
                        $bigrowsize+=count($products);
                    }
                @endphp
                <tr>
                    <td rowspan="{{ $bigrowsize }}" class="text-center">{{ $officerCode  }}</td>
                    <td rowspan="{{ $bigrowsize }}">{{ $officerName }}</td>
                  
                  
                    @foreach ($tfoSamities as $index => $samity)
                        @if ($index != 0)
                            <tr>
                        @endif    
                    
                            @php
                            $loanIds = $loans->where('samityId',$samity->id)->pluck('id')->toArray();
                            $loanIds = array_unique($loanIds);

                            $timeLength = array();
                            foreach ($filedOfficers as $officer){
                                if($officer['fieldOfficerId'] == $officerId && $officer['samityId'] == $samity->id){
                                    array_push($timeLength, array('dateFrom'=> $officer['dateFrom'], 'dateTo'=> $officer['dateTo']));
                                }
                            }

                            $allLoanInfos= array();
                            foreach ($timeLength as $key => $value) {
                                if(count($loanIds) > 0){
                                    $loanInfo = MFN::getLoanStatus($loanIds,  $value['dateFrom'], $value['dateTo']);
                                    $allLoanInfos = array_merge($allLoanInfos, $loanInfo);
                                }
                            }
                            $allLoanInfos = collect($allLoanInfos);

                            //dd(MFN::getLoanStatus($loanIds,  "2020-12-31", "2021-01-31"));
                            // $primaryProductIds = array();
                            // $primaryProductIds = array_merge($primaryProductIds,$deposits->where('samityId',$samity->id)->pluck('primaryProductId')->toArray());
                            // $primaryProductIds = array_merge($primaryProductIds,$withdraws->where('samityId',$samity->id)->pluck('primaryProductId')->toArray());
                            // $primaryProductIds = array_merge($primaryProductIds,$loans->where('samityId',$samity->id)->pluck('productId')->toArray());
                            // $primaryProductIds = array_merge($primaryProductIds,$loanCollection->where('samityId',$samity->id)->pluck('productId')->toArray());
                            // $primaryProductIds = array_unique($primaryProductIds);

                            // $products = $loanProducts->whereIn('id',$primaryProductIds);
                            $products = $productsForSamity[$index];
                            
                            @endphp
                            <td rowspan="{{ count($products) }}" class="text-center">{{ $samity->samityCode }}</td>
                            <td rowspan="{{ count($products) }}">{{ $samity->name }}</td>
                            
                            @foreach ($products as $indexp => $product)
                            @if($indexp != 0)
                                <tr>    
                            @endif
                            
                                <td class="text-center">{{$product->name}}</td>
                                @foreach ($savingsProducts as $sp)
                                    @php
                                        
                                        $savingsAmount= 0;
                                     

                                        foreach ($timeLength as $key => $value) {
                                            
                                            $savingsAmount +=  $deposits->where('samityId',$samity->id)->where('primaryProductId',$product->id)
                                                                  ->where('savingsProductId',$sp->id)
                                                                  ->where('date', '>=', $value['dateFrom'])
                                                                  ->where('date', '<=', $value['dateTo'])
                                                                  ->sum('amount');
                                        }

                                        $subTotals[$officerName]["savings-".(string)$sp->id] += $savingsAmount;
                                        $totals["savings-".(string)$sp->id] += $savingsAmount;
                                    @endphp
                                    <td class="text-right">{{ number_format($savingsAmount,2)}}</td>
                                @endforeach

                                @foreach ($savingsProducts as $sp)
                                @php
                                    
                                    $interest= 0;
                                 

                                    foreach ($timeLength as $key => $value) {
                                        
                                        $interest +=  $interests->where('samityId',$samity->id)->where('primaryProductId',$product->id)
                                                                  ->where('savingsProductId',$sp->id)
                                                                  ->where('date', '>=', $value['dateFrom'])
                                                                  ->where('date', '<=', $value['dateTo'])
                                                                  ->sum('amount');
                                    }

                                    $subTotals[$officerName]["interest-".(string)$sp->id] += $interest;
                                    $totals["interest-".(string)$sp->id] += $interest;
                                @endphp
                                    <td class="text-right">{{ number_format($interest,2)}}</td>
                                @endforeach

                                @foreach ($savingsProducts as $sp)
                                @php
                                    
                                    $savingsWithdraw= 0;
                                 

                                    foreach ($timeLength as $key => $value) {
                                        
                                        $savingsWithdraw += $withdraws->where('samityId',$samity->id)->where('primaryProductId',$product->id)
                                                                ->where('savingsProductId',$sp->id)
                                                                ->where('date', '>=', $value['dateFrom'])
                                                                ->where('date', '<=', $value['dateTo'])
                                                                ->sum("amount");
                                    }

                                    $subTotals[$officerName]["refund-".(string)$sp->id] += $savingsWithdraw;
                                    $totals["refund-".(string)$sp->id] += $savingsWithdraw;

                                @endphp
                                    <td class="text-right">{{ number_format($savingsWithdraw,2)}}</td>
                                @endforeach
                                
                                @php
                                    $loanIdsForThisProduct = $loans->where('samityId',$samity->id)
                                                                    ->where('disbursementDate','>=', $from)
                                                                    ->where('disbursementDate','<=', $to)
                                                                    ->where('productId',$product->id)->pluck('id')->toArray();
                                                                    
                                    $loanIdsForThisProduct = array_unique($loanIdsForThisProduct);
                                    $additionalFees = $loanDetails->whereIn('loanId', $loanIdsForThisProduct)->sum('additionalFee');
                                    $ServiceChargePhotography = $loanDetails->whereIn('loanId', $loanIdsForThisProduct)->sum('loanFormFee');
                                    $insuranceAmount = $loans->whereIn('id', $loanIdsForThisProduct)->sum('insuranceAmount');
                                    $disbursementAmount = $loans->where('samityId',$samity->id)->where('productId',$product->id)->where('disbursementDate','>=',$from)->where('disbursementDate','<=',$to)->sum('loanAmount');
                                    
                                    
                                    // dd($allLoanInfos, $product->id);
                                    $loanInfo = $allLoanInfos->where('productId', $product->id);
                                    
                                    $recoverableAmount = 0;
                                    $onPeriodDueAmount = 0;
                                    $onPeriodAdvanceAmount = 0;
                                    $rebateAmount = 0;
                                    $onPeriodReularCollection = 0;
                                    $payableAmountPrincipal = 0;
                                    $onPeriodCollectionInterest =0;
                                    $onPeriodCollectionPrincipal =0;

                                    foreach ($loanInfo as $info) {
                                        $recoverableAmount += $info['onPeriodPayable'];
                                        $onPeriodDueAmount += $info['onPeriodDueCollection'];
                                        $onPeriodAdvanceAmount += $info['onPeriodAdvanceAmount'];
                                        $rebateAmount += $info['onPeriodRebateAmount'];
                                        $onPeriodReularCollection += $info['onPeriodReularCollection'];
                                        $onPeriodCollectionPrincipal +=  $info['onPeriodCollectionPrincipal'];          
                                        $onPeriodCollectionInterest += $info['onPeriodCollectionInterest'];
                                    }

                                    //increment in subtotal array
                                    $subTotals[$officerName]["AdditionalFees"] += $additionalFees;
                                    $subTotals[$officerName]["InsuranceAmount"] += $insuranceAmount;
                                    $subTotals[$officerName]["ServiceChargePhotography"] += $ServiceChargePhotography;
                                    $subTotals[$officerName]["DisbursementAmount"] += $disbursementAmount;
                                    $subTotals[$officerName]["RegularRecoverable"] += $recoverableAmount;
                                    $subTotals[$officerName]["Regular"] += $onPeriodReularCollection;
                                    $subTotals[$officerName]["Due"] += $onPeriodDueAmount;
                                    $subTotals[$officerName]["Advance"] += $onPeriodAdvanceAmount;
                                    $subTotals[$officerName]["Rebate"] += $rebateAmount;
                                    $subTotals[$officerName]["Principal"] += $onPeriodCollectionPrincipal;
                                    $subTotals[$officerName]["ServiceChanrge"] += $onPeriodCollectionInterest;
                                    $subTotals[$officerName]["Total"] += $onPeriodCollectionPrincipal;
                                    $subTotals[$officerName]["Total"] += $onPeriodCollectionInterest;

                                    //increment in total array
                                    $totals["AdditionalFees"] += $additionalFees;
                                    $totals["InsuranceAmount"] += $insuranceAmount;
                                    $totals["ServiceChargePhotography"] += $ServiceChargePhotography;
                                    $totals["DisbursementAmount"] += $disbursementAmount;
                                    $totals["RegularRecoverable"] += $recoverableAmount;
                                    $totals["Regular"] += $onPeriodReularCollection;
                                    $totals["Due"] += $onPeriodDueAmount;
                                    $totals["Advance"] += $onPeriodAdvanceAmount;
                                    $totals["Rebate"] += $rebateAmount;
                                    $totals["Principal"] += $onPeriodCollectionPrincipal;
                                    $totals["ServiceChanrge"] += $onPeriodCollectionInterest;
                                    $totals["Total"] += $onPeriodCollectionPrincipal;
                                    $totals["Total"] += $onPeriodCollectionInterest;
                                    
                                @endphp

                                <td class="text-right">{{number_format($additionalFees, 2)}}</td>
                                <td class="text-right">{{number_format($disbursementAmount, 2)}}</td>
                                <td class="text-right">{{number_format($recoverableAmount, 2)}}</td>

                                <td class="text-right">{{number_format($onPeriodReularCollection, 2)}}</td>
                                <td class="text-right">{{number_format($onPeriodDueAmount, 2)}}</td>
                                <td class="text-right">{{number_format($onPeriodAdvanceAmount, 2)}}</td>
                                <td class="text-right">{{number_format($rebateAmount, 2)}}</td>
                                <td class="text-right">{{number_format($onPeriodCollectionPrincipal,2)}}</td>
                                <td class="text-right">{{number_format($onPeriodCollectionInterest,2)}}</td>
                                <td class="text-right">{{number_format($onPeriodCollectionPrincipal + $onPeriodCollectionInterest,2)}}</td>
                                <td class="text-right">{{number_format($insuranceAmount, 2)}}</td>
                                <td class="text-right">{{number_format($ServiceChargePhotography, 2)}}</td>
                            </tr>
                            @endforeach

                        </tr>
                    @endforeach
                </tr>
                <tr class="subtotal">
                    
                    <td colspan="5" class="text-center">Subtotal</td>

                    @foreach ($savingsProducts as $sp)
                        <td class="text-right">{{ number_format($subTotals[$officerName]['savings-'.(string)$sp->id],2) }}</td>
                    @endforeach
                    @foreach ($savingsProducts as $sp)
                        <td class="text-right">{{ number_format($subTotals[$officerName]['interest-'.(string)$sp->id],2) }}</td>
                    @endforeach
                    @foreach ($savingsProducts as $sp)
                        <td class="text-right">{{ number_format($subTotals[$officerName]['refund-'.(string)$sp->id],2) }}</td>
                    @endforeach

                    <td class="text-right">{{ number_format($subTotals[$officerName]['AdditionalFees'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['DisbursementAmount'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['RegularRecoverable'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Regular'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Due'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Advance'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Rebate'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Principal'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['ServiceChanrge'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['Total'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['InsuranceAmount'],2) }}</td>
                    <td class="text-right">{{ number_format($subTotals[$officerName]['ServiceChargePhotography'],2) }}</td>
                    
                </tr>           
            @endforeach
        </tbody>
     
        <tfoot>
            <tr class="subtotal">
                <td colspan="5" class="text-center" class="text-right"><b>Total</b></td>

                @foreach ($savingsProducts as $sp)
                    <td class="text-right">{{ number_format($totals['savings-'.(string)$sp->id],2) }}</td>
                @endforeach
                @foreach ($savingsProducts as $sp)
                    <td class="text-right">{{ number_format($totals['interest-'.(string)$sp->id],2) }}</td>
                @endforeach
                @foreach ($savingsProducts as $sp)
                    <td class="text-right">{{ number_format($totals['refund-'.(string)$sp->id],2) }}</td>
                @endforeach

                <td class="text-right">{{ number_format($totals['AdditionalFees'],2) }}</td>
                <td class="text-right">{{ number_format($totals['DisbursementAmount'],2) }}</td>
                <td class="text-right">{{ number_format($totals['RegularRecoverable'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Regular'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Due'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Advance'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Rebate'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Principal'],2) }}</td>
                <td class="text-right">{{ number_format($totals['ServiceChanrge'],2) }}</td>
                <td class="text-right">{{ number_format($totals['Total'],2) }}</td>
                <td class="text-right">{{ number_format($totals['InsuranceAmount'],2) }}</td>
                <td class="text-right">{{ number_format($totals['ServiceChargePhotography'],2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>