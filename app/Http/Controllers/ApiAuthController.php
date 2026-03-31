<?php

namespace App\Http\Controllers;

use App\Model\GNL\SysUserDevice;
use App\Model\GNL\SysUserHistory;
use App\Model\GNL\SysUserRole;
use App\Services\AccService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {

            $user  = Auth::user();
            $token = $user->createToken('appToken')->accessToken;

            DB::beginTransaction();
            try {

                $userRoleData = SysUserRole::where('id', $user->sys_user_role_id)
                    ->select(['id', 'parent_id', 'role_name', 'serialize_module', 'serialize_menu', 'serialize_permission',
                        'modules', 'menus', 'permissions'])
                    ->first();

                $data['sys_username']     = $request->username;
                $data['sys_user_id']      = $user->id;
                $data['sys_user_role_id'] = $user->sys_user_role_id;
                $data['login_time']       = Carbon::now();
                $data['ip_address']       = $_SERVER['REMOTE_ADDR'];
                $data['http_user_agent']  = $_SERVER['HTTP_USER_AGENT'];

                $insertHistory = SysUserHistory::create($data);

                $historys_id = $insertHistory->id;

                $userDeviceData = SysUserDevice::where(
                    ['sys_user_id'    => $user->id,
                        'ip_address'      => $_SERVER['REMOTE_ADDR'],
                        'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    ])
                    ->select(['id'])
                    ->first();

                if ($userDeviceData) {
                    $logout_time    = $data['login_time'];
                    $updateDeviceTB = SysUserDevice::where('id', $userDeviceData->id)->update(['updated_at' => $logout_time]);

                } else {

                    $HTTP_USER_AGENT        = $_SERVER['HTTP_USER_AGENT'];
                    $HTTP_USER_AGENT_Array  = explode('(', $HTTP_USER_AGENT);
                    $HTTP_USER_AGENT_Array2 = explode(')', $HTTP_USER_AGENT_Array[0]);
                    $data['device_name']    = $HTTP_USER_AGENT_Array2[0];
                    $InsertDeviceTB         = SysUserDevice::create($data);
                }

                $samities = DB::table('mfn_samity')
                    ->where('is_delete', 0)
                    ->where('closingDate', '0000-00-00')
                    ->where('branchId', $user->branch_id)
                    ->get();

                $members = DB::table('mfn_members')
                    ->where('is_delete', 0)
                    ->where('closingDate', '0000-00-00')
                    ->where('branchId', $user->branch_id)
                    ->get();

                $productIds = DB::table('mfn_savings_product')
                    ->where('productTypeId', 1)
                    ->pluck('id')
                    ->all();

                $accounts = DB::table('mfn_savings_accounts')
                    ->where('is_delete', 0)
                    ->where('closingDate', '0000-00-00')
                    ->whereIn('savingsProductId', $productIds)
                    ->where('branchId', $user->branch_id)
                    ->get();

                $memberBalance = MfnService::getSavingsAccountsBalance($user->branch_id);
                $ledgerData    = AccService::getLedgerAccount($user->branch_id, null, null, 5);
                $branchDate    = MfnService::systemCurrentDate($user->branch_id);

                $ledgerDataArr = array();
                foreach ($ledgerData as $key => $row) {
                    $ledgerDataArr[$key]['id']    = $row->id;
                    $ledgerDataArr[$key]['label'] = $row->name;
                }

                $loans = DB::table('mfn_loans')
                    ->where('is_delete', 0)
                    ->where('loanType', 'Regular')
                    ->where('branchId', $user->branch_id)
                    ->get();

                $loanStatusArr = array();
                foreach ($loans as $key => $row) {

                    $loanStatus = MfnService::getLoanStatus($row->id)[0];

                    $loanStatusArr[$key]['branchId']               = $row->branchId;
                    $loanStatusArr[$key]['samityId']               = $row->samityId;
                    $loanStatusArr[$key]['memberId']               = $row->memberId;
                    $loanStatusArr[$key]['memberName']             = DB::table('mfn_members')->where('id', $row->memberId)->value('name');
                    $loanStatusArr[$key]['loanId']                 = $loanStatus['loanId'];
                    $loanStatusArr[$key]['payableAmount']          = $loanStatus['payableAmount'];
                    $loanStatusArr[$key]['payableAmountPrincipal'] = $loanStatus['payableAmountPrincipal'];
                    $loanStatusArr[$key]['paidAmount']             = $loanStatus['paidAmount'];
                    $loanStatusArr[$key]['paidAmountPrincipal']    = $loanStatus['paidAmountPrincipal'];
                    $loanStatusArr[$key]['dueAmount']              = $loanStatus['dueAmount'];
                    $loanStatusArr[$key]['dueAmountPrincipal']     = $loanStatus['dueAmountPrincipal'];
                    $loanStatusArr[$key]['advanceAmount']          = $loanStatus['advanceAmount'];
                    $loanStatusArr[$key]['advanceAmountPrincipal'] = $loanStatus['advanceAmountPrincipal'];
                    $loanStatusArr[$key]['outstanding']            = $loanStatus['outstanding'];
                    $loanStatusArr[$key]['outstandingPrincipal']   = $loanStatus['outstandingPrincipal'];
                }

                $loginData = [
                    'user_config'     => [
                        'company_id'   => $user->company_id,
                        'branch_id'    => $user->branch_id,
                        'branch_date'  => $branchDate,
                        'counter_no'   => '00',
                        'company_logo' => '',
                        'user_id'      => $user->id,
                        'employee_id'  => $user->employee_id,
                    ],
                    'branchData'      => [
                        'samities'      => $samities,
                        'members'       => $members,
                        'accounts'      => $accounts,
                        'memberBalance' => $memberBalance,
                        'ledgerData'    => $ledgerDataArr,
                        'loans'         => $loans,
                        'loanStatus'    => $loanStatusArr,
                    ],
                    'user_role'       => [
                        'role_module'     => unserialize(base64_decode($userRoleData->serialize_module)),
                        'role_menu'       => unserialize(base64_decode($userRoleData->serialize_menu)),
                        'role_permission' => unserialize(base64_decode($userRoleData->serialize_permission)),
                    ],
                    'login_role_name' => $userRoleData->role_name,
                    'historys_id'     => $historys_id,
                    'last_login_ip'   => $_SERVER['REMOTE_ADDR'],
                    'last_login_time' => $data['login_time'],
                ];

            } catch (\Exception $e) {

                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => $e . 'Something went wrong!! Try again..',
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'token'   => $token,
                'data'    => $loginData,
            ]);

        } else {

            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
    }

    public function logout()
    {
        if (Auth::user()) {
            $user = Auth::user()->token();
            $user->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logout successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to Logout',
            ]);
        }
    }
}
