<?php

namespace App\Http\Controllers\MFN\Mail;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Model\MFN\MailVerification;
use App\Model\MFN\MemberDetails;
use DB;
use Illuminate\Http\Request;
use App\Services\MfnService;

class MailVarificationController extends Controller
{
    public function isVerified(Request $req)
    {
        $memberMailverify = MailVerification::where('memberId', decrypt($req->memberId))->first();

        if ($memberMailverify->isVerified == 'yes') {
            return 'Already verified your email.';
        }

        if ($memberMailverify->eToken == $req->eToken) {

            DB::beginTransaction();
            try {

                $memberMailverify->update(['isVerified' => 'yes']);

                $memberDetails = MemberDetails::where('memberId', $memberMailverify->memberId)->first();

                $memberDetails->update(['email' => $memberMailverify->email]);

                MfnService::sendMail('mfn_members', $memberMailverify->memberId, $memberDetails->created_at);

                $savingAccount = DB::table('mfn_savings_accounts')->where('memberId', $memberMailverify->memberId)->get();

                if (count($savingAccount) > 0) {
                    foreach ($savingAccount as $row) {
                        MfnService::sendMail('mfn_savings_accounts', $row->memberId, $row->created_at);
                    }
                }

                $loan = DB::table('mfn_loans')->where('memberId', $memberMailverify->memberId)->get();

                if (count($loan) > 0) {
                    foreach ($loan as $row) {
                        MfnService::sendMail('mfn_loans', $row->memberId, $row->created_at, $loan->loanAmount);
                    }
                }

                DB::commit();
                return 'Successfully verified your email.';

            } catch (\Exception $e) {
                DB::rollback();
                return 'Unable to verify your email.';
            }
        }

        return 'Unable to verify your email.';
    }

    public function store(Request $req)
    {
        $isInsert = MailVerification::create($req->all());

        if ($isInsert) {
            $notification = array(
                'alert-type' => 'success',
                'message'    => 'Successfully Inserted',
            );

            return response()->json($notification);
        }

        $notification = array(
            'alert-type' => 'error',
            'message'    => 'Something went wrong',
        );

        return response()->json($notification);
    }

    public function update(Request $req)
    {
        $mailVerification = MailVerification::where('memberId', $req->memberId)->first();

        try {

            $mailVerification->update(['email' => $req->email, 'eToken' => $req->eToken, 'isVerified' => 'no']);

            $notification = array(
                'alert-type' => 'success',
                'message'    => 'Successfully Inserted',
            );

            return response()->json($notification);

        } catch (\Exception $e) {

            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }
}
