<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Model\MFN\AutoVoucherConfig;

class AutoVoucherConfigurationController extends Controller
{
    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $components = DB::table('mfn_auto_voucher_components')->get();
        $configurations = DB::table('mfn_auto_voucher_config')->get();

        $loanProducts = DB::table('mfn_loan_products')
            ->where('is_delete', 0)
            ->select('id', 'productCode', 'name', 'fundingOrgId', 'isPrimaryProduct')
            ->get();

        $primaryLoanProducts = $loanProducts->where('isPrimaryProduct', 1);

        $fundingOrgs = DB::table('mfn_funding_orgs')
            ->whereIn('id', $loanProducts->pluck('fundingOrgId'))
            ->get();

        $primaryProductFundingOrgs = DB::table('mfn_funding_orgs')
            ->whereIn('id', $primaryLoanProducts->pluck('fundingOrgId'))
            ->get();

        $savingsProducts = DB::table('mfn_savings_product')->where('is_delete', 0)->get();

        foreach ($fundingOrgs as $key => $fundingOrg) {
            $fundingOrgs[$key]->loanRowSpan = $loanProducts->where('fundingOrgId', $fundingOrg->id)->count();
        }

        foreach ($primaryProductFundingOrgs as $key => $fundingOrg) {
            $primaryProductFundingOrgs[$key]->loanAndSavingsRowSpan = $primaryLoanProducts->where('fundingOrgId', $fundingOrg->id)->count() * $savingsProducts->count();
        }

        $ledgers = DB::table('acc_account_ledger')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['is_group_head', 0],
            ])
            ->select('id', 'code')
            ->get();


        $data = array(
            'configurations'            => $configurations,
            'components'                => $components,
            'fundingOrgs'               => $fundingOrgs,
            'primaryProductFundingOrgs' => $primaryProductFundingOrgs,
            'loanProducts'              => $loanProducts,
            'primaryLoanProducts'       => $primaryLoanProducts,
            'savingsProducts'           => $savingsProducts,
            'ledgers'                   => $ledgers,
        );

        return view('MFN.GConfig.AutoVoucherConfig.add', $data);
    }

    public function store(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = '');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $ledgers = DB::table('acc_account_ledger')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['is_group_head', 0],
            ])
            ->select('id', 'code')
            ->get();

        DB::beginTransaction();

        try {

            $componentVoucherTypes = array();

            foreach ($req->voucherTypeComponentIds as $key => $componentId) {
                DB::table('mfn_auto_voucher_components')
                    ->where('id', $componentId)
                    ->update([
                        'voucherType' => $req->voucherTypes[$key],
                        'status' => $req->status[$key] == 'active' ? 1 : 0,
                    ]);
                $componentVoucherTypes[$componentId] = $req->voucherTypes[$key];
            }

            // for Debit/Credit Vouchers
            if (isset($req->loanProductComponentPrincipals)) {
                foreach ($req->loanProductComponentPrincipals as $componentId => $loanProductsPrincipals) {
                    foreach ($loanProductsPrincipals as $loanProductId => $principalLedgerCode) {

                        $interestLedgerCode = $req->loanProductComponentInterests[$componentId][$loanProductId];

                        if ($principalLedgerCode == '' && $interestLedgerCode == '') {
                            continue;
                        }

                        AutoVoucherConfig::updateOrCreate(
                            [
                                'componentId' => $componentId,
                                'loanProductId' => $loanProductId,
                                'headFor' => $componentVoucherTypes[$componentId]
                            ],
                            [
                                'principalLedgerId' => $ledgers->where('code', $principalLedgerCode)->max('id'),
                                'interestLedgerId' => $ledgers->where('code', $interestLedgerCode)->max('id')
                            ]
                        );
                    }
                }
            }            

            if (isset($req->loanAndSavingsProductPrincipals)) {
                foreach ($req->loanAndSavingsProductPrincipals as $componentId => $loanProductsPrincipals) {
                    foreach ($loanProductsPrincipals as $loanProductId => $savingsProductsPrincipals) {
                        foreach ($savingsProductsPrincipals as $savingsProductId => $principalLedgerCode) {

                            $interestLedgerCode = $req->loanAndSavingsProductInterests[$componentId][$loanProductId][$savingsProductId];
                            $provitionLedgerCode = $req->loanAndSavingsProductProvitions[$componentId][$loanProductId];

                            if ($principalLedgerCode == '' && $interestLedgerCode == '' && $provitionLedgerCode == '') {
                                continue;
                            }

                            AutoVoucherConfig::updateOrCreate(
                                [
                                    'componentId' => $componentId,
                                    'loanProductId' => $loanProductId,
                                    'savingsProductId' => $savingsProductId,
                                    'headFor' => $componentVoucherTypes[$componentId]
                                ],
                                [
                                    'principalLedgerId' => $ledgers->where('code', $principalLedgerCode)->max('id'),
                                    'interestLedgerId' => $ledgers->where('code', $interestLedgerCode)->max('id'),
                                    'interestProvisionLedgerId' => $ledgers->where('code', $provitionLedgerCode)->max('id')
                                ]
                            );
                        }
                    }
                }
            }
            

            // for Journal Voucher
            if (isset($req->loanProductComponentDebitPrincipals)) {
                foreach ($req->loanProductComponentDebitPrincipals as $componentId => $loanProductsPrincipals) {
                    foreach ($loanProductsPrincipals as $loanProductId => $debitPrincipalLedgerCode) {
                        // for debit
                        $debitInterestLedgerCode = $req->loanProductComponentDebitInterests[$componentId][$loanProductId];

                        // for credit
                        $creditPrincipalLedgerCode = $req->loanProductComponentCreditPrincipals[$componentId][$loanProductId];
                        $creditInterestLedgerCode = $req->loanProductComponentCreditInterests[$componentId][$loanProductId];

                        if ($debitPrincipalLedgerCode == '' && $debitInterestLedgerCode == '' && $creditPrincipalLedgerCode == '' && $creditInterestLedgerCode == '') {
                            continue;
                        }

                        // for debit
                        AutoVoucherConfig::updateOrCreate(
                            [
                                'componentId' => $componentId,
                                'loanProductId' => $loanProductId,
                                'headFor' =>  'Debit'
                            ],
                            [
                                'principalLedgerId' => $ledgers->where('code', $debitPrincipalLedgerCode)->max('id'),
                                'interestLedgerId' => $ledgers->where('code', $debitInterestLedgerCode)->max('id')
                            ]
                        );
    
                        // for credit
                        AutoVoucherConfig::updateOrCreate(
                            [
                                'componentId' => $componentId,
                                'loanProductId' => $loanProductId,
                                'headFor' =>  'Credit'
                            ],
                            [
                                'principalLedgerId' => $ledgers->where('code', $creditPrincipalLedgerCode)->max('id'),
                                'interestLedgerId' => $ledgers->where('code', $creditInterestLedgerCode)->max('id')
                            ]
                        );
                    }
                }
            }            

            if (isset($req->loanAndSavingsProductDebitPrincipals)) {
                foreach ($req->loanAndSavingsProductDebitPrincipals as $componentId => $loanProductsPrincipals) {
                    foreach ($loanProductsPrincipals as $loanProductId => $savingsProductsPrincipals) {
                        foreach ($savingsProductsPrincipals as $savingsProductId => $debitPrincipalLedgerCode) {
                            // for debit
                            $debitInterestLedgerCode = $req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId];
                            $debitInterestProvisionLedgerCode = $req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId];

                            // for credit
                            $creditPrincipalLedgerCode = $req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId][$savingsProductId];
                            $creditInterestLedgerCode = $req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId];
                            $creditInterestProvisionLedgerCode = $req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId];


                            if ($debitPrincipalLedgerCode == '' && $debitInterestLedgerCode == '' && $debitInterestProvisionLedgerCode == '' && $creditPrincipalLedgerCode == '' && $creditInterestLedgerCode == '' && $creditInterestProvisionLedgerCode == '') {
                                continue;
                            }

                            // for debit
                            AutoVoucherConfig::updateOrCreate(
                                [
                                    'componentId' => $componentId,
                                    'loanProductId' => $loanProductId,
                                    'savingsProductId' => $savingsProductId,
                                    'headFor' =>  'Debit'
                                ],
                                [
                                    'principalLedgerId' => $ledgers->where('code', $debitPrincipalLedgerCode)->max('id'),
                                    'interestLedgerId' => $ledgers->where('code', $debitInterestLedgerCode)->max('id'),
                                    'interestProvisionLedgerId' => $ledgers->where('code', $debitInterestProvisionLedgerCode)->max('id')
                                ]
                            );

                            // for credit
                            AutoVoucherConfig::updateOrCreate(
                                [
                                    'componentId' => $componentId,
                                    'loanProductId' => $loanProductId,
                                    'savingsProductId' => $savingsProductId,
                                    'headFor' =>  'Credit'
                                ],
                                [
                                    'principalLedgerId' => $ledgers->where('code', $creditPrincipalLedgerCode)->max('id'),
                                    'interestLedgerId' => $ledgers->where('code', $creditInterestLedgerCode)->max('id'),
                                    'interestProvisionLedgerId' => $ledgers->where('code', $creditInterestProvisionLedgerCode)->max('id')
                                ]
                            );
                        }
                    }
                }
            }

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function getPassport($req, $operationType, $object = null)
    {
        $errorMsg = null;

        $rules = array(
            'voucherTypes.*' => 'required'
        );

        $validator = Validator::make($req->all(), $rules);
        $attributes = array(
            'voucherTypes' => 'Voucher Type'
        );

        $validator->setAttributeNames($attributes);

        if ($validator->fails()) {
            $errorMsg = implode(' || ', $validator->messages()->all());
        }

        $ledgerCodes = DB::table('acc_account_ledger')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['is_group_head', 0],
            ])
            ->pluck('code')
            ->toArray();

        // check all given ledger codes ar valid or not
        $notFounndLedgerCodes = [];
        if (isset($req->loanProductComponentPrincipals)) {
            foreach ($req->loanProductComponentPrincipals as $componentId => $loanProductsPrincipals) {
                foreach ($loanProductsPrincipals as $loanProductId => $principalLedgerCode) {
                    if (!in_array($principalLedgerCode, $ledgerCodes) && $principalLedgerCode != '') {
                        array_push($notFounndLedgerCodes, $principalLedgerCode);
                    }
                    if (!in_array($req->loanProductComponentInterests[$componentId][$loanProductId], $ledgerCodes) && $req->loanProductComponentInterests[$componentId][$loanProductId] != '') {
                        array_push($notFounndLedgerCodes, $req->loanProductComponentInterests[$componentId][$loanProductId]);
                    }
                }
            }
        }        

        if (isset($req->loanAndSavingsProductPrincipals)) {
            foreach ($req->loanAndSavingsProductPrincipals as $componentId => $loanProductsPrincipals) {
                foreach ($loanProductsPrincipals as $loanProductId => $savingsProductsPrincipals) {
                    foreach ($savingsProductsPrincipals as $savingsProductId => $principalLedgerCode) {
                        if (!in_array($principalLedgerCode, $ledgerCodes) && $principalLedgerCode != '') {
                            array_push($notFounndLedgerCodes, $principalLedgerCode);
                        }
                        if (!in_array($req->loanAndSavingsProductInterests[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductInterests[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductInterests[$componentId][$loanProductId][$savingsProductId]);
                        }
                        if (!in_array($req->loanAndSavingsProductProvitions[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductProvitions[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductProvitions[$componentId][$loanProductId][$savingsProductId]);
                        }
                    }
                }
            }
        }
        

        // for Journal
        if (isset($req->loanProductComponentDebitPrincipals)) {
            foreach ($req->loanProductComponentDebitPrincipals as $componentId => $loanProductsPrincipals) {
                foreach ($loanProductsPrincipals as $loanProductId => $principalLedgerCode) {
                    if (!in_array($principalLedgerCode, $ledgerCodes) && $principalLedgerCode != '') {
                        array_push($notFounndLedgerCodes, $principalLedgerCode);
                    }
                    if (!in_array($req->loanProductComponentDebitInterests[$componentId][$loanProductId], $ledgerCodes) && $req->loanProductComponentDebitInterests[$componentId][$loanProductId] != '') {
                        array_push($notFounndLedgerCodes, $req->loanProductComponentDebitInterests[$componentId][$loanProductId]);
                    }
                    if (!in_array($req->loanProductComponentCreditPrincipals[$componentId][$loanProductId], $ledgerCodes) && $req->loanProductComponentCreditPrincipals[$componentId][$loanProductId] != '') {
                        array_push($notFounndLedgerCodes, $req->loanProductComponentCreditPrincipals[$componentId][$loanProductId]);
                    }
                    if (!in_array($req->loanProductComponentCreditInterests[$componentId][$loanProductId], $ledgerCodes) && $req->loanProductComponentCreditInterests[$componentId][$loanProductId] != '') {
                        array_push($notFounndLedgerCodes, $req->loanProductComponentCreditInterests[$componentId][$loanProductId]);
                    }

                    // If Debit data is present then user should give Credit data also
                    if (($principalLedgerCode != '' && $req->loanProductComponentCreditPrincipals[$componentId][$loanProductId] == '') ||  ($principalLedgerCode == '' && $req->loanProductComponentCreditPrincipals[$componentId][$loanProductId] != '')) {
                        $errorMsg = 'For Journal Voucher, Debit and Credit both side are required.';
                    }
                    if (($req->loanProductComponentDebitInterests[$componentId][$loanProductId] != '' && $req->loanProductComponentCreditInterests[$componentId][$loanProductId] == '') ||  ($req->loanProductComponentDebitInterests[$componentId][$loanProductId] == '' && $req->loanProductComponentCreditInterests[$componentId][$loanProductId] != '')) {
                        $errorMsg = 'For Journal Voucher, Debit and Credit both side are required.';
                    }
                }
            }
        }
        
        if (isset($req->loanAndSavingsProductDebitPrincipals)) {
            foreach ($req->loanAndSavingsProductDebitPrincipals as $componentId => $loanProductsPrincipals) {
                foreach ($loanProductsPrincipals as $loanProductId => $savingsProductsPrincipals) {
                    foreach ($savingsProductsPrincipals as $savingsProductId => $principalLedgerCode) {
                        if (!in_array($principalLedgerCode, $ledgerCodes) && $principalLedgerCode != '') {
                            array_push($notFounndLedgerCodes, $principalLedgerCode);
                        }
                        if (!in_array($req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId][$savingsProductId]);
                        }
                        if (!in_array($req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId]);
                        }
                        if (!in_array($req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId][$savingsProductId]);
                        }
                        if (!in_array($req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId]);
                        }
                        if (!in_array($req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId], $ledgerCodes) && $req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId] != '') {
                            array_push($notFounndLedgerCodes, $req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId]);
                        }

                        // If Debit data is present then user should give Credit data also
                        if (($principalLedgerCode == '' && $req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId][$savingsProductId] != '') || ($principalLedgerCode != '' && $req->loanAndSavingsProductCreditPrincipals[$componentId][$loanProductId] == '')) {
                            $errorMsg = 'For Journal Voucher, Debit and Credit both side are required.';
                        }
                        if (($req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId][$savingsProductId] == '' && $req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId] != '') || ($req->loanAndSavingsProductDebitInterests[$componentId][$loanProductId][$savingsProductId] != '' && $req->loanAndSavingsProductCreditInterests[$componentId][$loanProductId][$savingsProductId] == '')) {
                            $errorMsg = 'For Journal Voucher, Debit and Credit both side are required.';
                        }
                        if (($req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId] == '' && $req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId] != '') || ($req->loanAndSavingsProductDebitProvitions[$componentId][$loanProductId][$savingsProductId] != '' && $req->loanAndSavingsProductCreditProvitions[$componentId][$loanProductId][$savingsProductId] == '')) {
                            $errorMsg = 'For Journal Voucher, Debit and Credit both side are required.';
                        }
                    }
                }
            }
        }        

        if (count($notFounndLedgerCodes) > 0) {
            $errorMsg = 'Following code/codes are not found : ' . implode(' , ', $notFounndLedgerCodes);
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

    public function makeConfigDiv(Request $req)
    {
        $component = DB::table('mfn_auto_voucher_components')->where('id', $req->componentId)->first();
        $loanProducts = DB::table('mfn_loan_products')
            ->where('is_delete', 0)
            ->select('id', 'productCode', 'name', 'fundingOrgId', 'isPrimaryProduct')
            ->get();
        $fundingOrgs = DB::table('mfn_funding_orgs')
            ->whereIn('id', $loanProducts->pluck('fundingOrgId'))
            ->get();

        foreach ($fundingOrgs as $key => $fundingOrg) {
            $fundingOrgs[$key]->loanRowSpan = $loanProducts->where('fundingOrgId', $fundingOrg->id)->count();
        }

        $primaryLoanProducts = $loanProducts->where('isPrimaryProduct', 1);
        $primaryProductFundingOrgs = DB::table('mfn_funding_orgs')
            ->whereIn('id', $primaryLoanProducts->pluck('fundingOrgId'))
            ->get();

        $savingsProducts = DB::table('mfn_savings_product')->where('is_delete', 0)->get();

        foreach ($primaryProductFundingOrgs as $key => $fundingOrg) {
            $primaryProductFundingOrgs[$key]->loanAndSavingsRowSpan = $primaryLoanProducts->where('fundingOrgId', $fundingOrg->id)->count() * $savingsProducts->count();
        }

        $configurations = DB::table('mfn_auto_voucher_config')->where('componentId', $req->componentId);

        if ($component->voucherType == 'Debit') {
            $configurations->where('headFor', 'Debit');
        }
        if ($component->voucherType == 'Credit') {
            $configurations->where('headFor', 'Credit');
        }

        $configurations = $configurations->get();

        $ledgers = DB::table('acc_account_ledger')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['is_group_head', 0],
            ])
            ->select('id', 'code')
            ->get();

        $data = array(
            'component'                 => $component,
            'loanProducts'              => $loanProducts,
            'fundingOrgs'               => $fundingOrgs,
            'primaryLoanProducts'       => $primaryLoanProducts,
            'primaryProductFundingOrgs' => $primaryProductFundingOrgs,
            'savingsProducts'           => $savingsProducts,
            'configurations'            => $configurations,
            'ledgers'                   => $ledgers,
        );

        if ($req->voucherType == 'Journal') {
            return view('MFN.GConfig.AutoVoucherConfig.journal', $data);
        } else {
            return view('MFN.GConfig.AutoVoucherConfig.debitCredit', $data);
        }
    }
}
