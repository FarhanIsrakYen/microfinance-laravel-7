<?php

namespace App\Http\Controllers;

use App\Model\GNL\SysUser;
use App\Model\GNL\SysUserDevice;
use App\Model\GNL\SysUserFailedLogin;
use App\Model\GNL\SysUserHistory;
use App\Model\GNL\SysUserRole;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Services\RoleService as Role;
use Session;
use App\Services\HtmlService as HTML;
use App\Services\CommonService as Common;

class AuthController extends Controller
{

    public function index()
    {
        if (Auth::check()) {
            
            if (!empty(Session::get('LoginBy.user_role.role_module'))) {
                $SysModules = Session::get('LoginBy.user_role.role_module');
            } else {
                $SysModules = array();
            }
            return view('module_dashboard', compact('SysModules'));
        } else {
            return view('login');
        }
    }

    public function postLogin(Request $request)
    {
        request()->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $Today = new DateTime();
        $TodayDateTime = $Today->format('Y-m-d H:i:s');

        $RequestData = $request->all();

        // if (Auth::attempt(['username' => $request->username, 'password' => $request->password, 'is_active' => 1]))
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {

            // Get the currently authenticated user...
            $UserInfo = Auth::user();
            $UserID = Auth::id();

            // $UserInfo['is_active']
            if ($UserInfo->is_active == 1) {

                // Login TRUE
                // $UserInfo['sys_user_role_id']
                $RoleID = $UserInfo->sys_user_role_id;

                // Fetch User Role Data
                $UserRoleData = SysUserRole::where('id', $RoleID)
                    ->select(['id', 'parent_id', 'role_name', 'serialize_module', 'serialize_menu', 'serialize_permission',
                        'modules', 'menus', 'permissions'])
                    ->first();

                // Data Insert in System History Table
                $RequestData['sys_username'] = $RequestData['username'];
                $RequestData['sys_user_id'] = $UserID;
                $RequestData['sys_user_role_id'] = $RoleID;
                $RequestData['login_time'] = $TodayDateTime;
                $RequestData['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $RequestData['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                $InsertHistory = SysUserHistory::create($RequestData);

                if ($InsertHistory) {
                    // dd($InsertHistory);
                    $lastInsertQuery = SysUserHistory::latest()->first();
                    $historys_id = $lastInsertQuery->id;

                    // $historys_id = $InsertHistory->id;

                    // Check User Device Table & Insert
                    $UserDeviceData = SysUserDevice::where(['sys_user_id' => $UserID,
                        'ip_address' => $_SERVER['REMOTE_ADDR'],
                        'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    ])
                        ->select(['id'])
                        ->orderBy('id', 'DESC')
                        ->first();

                    //dd($UserDeviceData);

                    // Last Login time insert in User device or Update
                    if ($UserDeviceData) {
                        $logout_time = $TodayDateTime;
                        $UpdateDeviceTB = SysUserDevice::where('id', $UserDeviceData->id)->update(['updated_at' => $logout_time]);
                    } else {
                        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
                        $HTTP_USER_AGENT_Array = explode('(', $HTTP_USER_AGENT);
                        $HTTP_USER_AGENT_Array2 = explode(')', $HTTP_USER_AGENT_Array[1]);
                        $RequestData['device_name'] = $HTTP_USER_AGENT_Array2[0];
                        $InsertDeviceTB = SysUserDevice::create($RequestData);
                    }

                    // 'user_config' => [
                    //     'company_id' => $UserInfo['company_id'],
                    //     'branch_id' => $UserInfo['branch_id'],
                    //     'company_logo' => '',
                    //     'employee_id' => $UserInfo['employee_id']
                    // ]

                    // Store data in Session
                    $LoginData = [
                        'user_config' => [
                            'company_id' => $UserInfo->company_id,
                            'branch_id' => $UserInfo->branch_id,
                            'counter_no' => '00',
                            'company_logo' => '',
                            'employee_id' => $UserInfo->employee_id
                        ],
                        'user_role' => [
                            'role_module' => unserialize(base64_decode($UserRoleData->serialize_module)),
                            'role_menu' => unserialize(base64_decode($UserRoleData->serialize_menu)),
                            'role_permission' => unserialize(base64_decode($UserRoleData->serialize_permission))
                        ],
                        'login_role_name' => $UserRoleData->role_name,
                        'historys_id' => $historys_id,
                        'last_login_ip' => $_SERVER['REMOTE_ADDR'],
                        'last_login_time' => $TodayDateTime,
                    ];

                    // Write in Session
                    $Session = $request->session();
                    $Session->put('LoginBy', $LoginData);
                    $lastInsertQuery->update(['session_key' => $Session->getId()]);
                    // dd($Session->getId());   

                    // $UserInfo['full_name']
                    $notification = array(
                        'message' => '!!!! Welcome ' . $UserInfo->full_name . ' !!!!',
                        'alert-type' => 'success',
                    );

                    return redirect()->intended('/')->with($notification);
                    // return Redirect::to('login')->with($notification);
                }
            } else {
                /* Logout auth if user inactive */
                Session::flush();
                Auth::logout();
                $notification = array(
                    'message' => 'Inactive user. Please contact to administration.',
                    'alert-type' => 'error',
                );
                return Redirect::to('/')->with($notification);
            }
        } else {

            /* Failed Login */

            $RequestData['attempt_time'] = $TodayDateTime;
            $RequestData['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $RequestData['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            if (SysUserFailedLogin::create($RequestData)) {
                $notification = array(
                    'message' => 'Oppes! Username or password is incorrect',
                    'alert-type' => 'error',
                );
                return Redirect::to('/')->with($notification);
            }
        }
    }

    public function logout()
    {
        $UserID = Auth::id();
        $LoginByData = Session::get('LoginBy');

        // Update User Table Data
        if ($UserID) {
            $UpdateUserTB = SysUser::where('id', $UserID)->update([
                'last_login_ip' => $LoginByData['last_login_ip'],
                'last_login_time' => $LoginByData['last_login_time'],
            ]);
        }

        // Update History Table Data
        $HistoryID = $LoginByData['historys_id'];
        if ($HistoryID) {

            $Today = new DateTime();
            $TodayDateTime = $Today->format('Y-m-d H:i:s');

            $UpdateHistoryTB = SysUserHistory::where('id', $HistoryID)->update(['logout_time' => $TodayDateTime]);

            if ($UpdateHistoryTB) {
                Session::flush();
                Auth::logout();
                // return Redirect('login');
                // return Redirect::to('/')->with($notification);
                return Redirect::to('/');
            }
        }

        Session::flush();
        Auth::logout();
        return Redirect::to('login');
    }

    public function moduleDashboard()
    {
        // dd(1);
        if (Auth::check()) {

            // dd(Common::systemNextWorkingDay('2020-01-29'));

            if (!empty(Session::get('LoginBy.user_role.role_module'))) {
                $SysModules = Session::get('LoginBy.user_role.role_module');
            } else {
                $SysModules = array();
            }

            return view('module_dashboard', compact('SysModules'));
        }
        return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

    public function ajaxSetModuleID(Request $request)
    {

        if ($request->ajax()) {

            // $ModuleID = $request->ModuleID;
            $ModuleLink = $request->ModuleLink;
            // Store in Seesion
            // $request->session()->put('ModuleID', $ModuleID);
            $request->session()->put('ModuleID', $ModuleLink);

            // $request->session()->forget('ModuleID');

            // Retrive session Data
            $TestData = ($request->session()->get('ModuleID') !== null) ? 1 : 0;

            echo json_encode($TestData);
        }
    }


    // public function registration()
    // {
    //     if (Auth::check()) {
    //         return view('registration');
    //     }

    //     return Redirect::to("login")->withSuccess('Opps! You do not have access');

    // }

    // public function postRegistration(Request $request)
    // {
    //     request()->validate([
    //         'full_name' => 'required',
    //         'username' => 'required|unique:gnl_sys_users',
    //         'email' => 'required|email',
    //         'password' => 'required|min:6',
    //     ]);

    //     $data = $request->all();
    //     $check = $this->create($data);

    //     return Redirect::to("module_dashboard")->withSuccess('Great! You have Successfully loggedin');
    // }

    // public function create(array $data)
    // {
    //     return User::create([
    //         'full_name' => $data['full_name'],
    //         'email' => $data['email'],
    //         'username' => $data['username'],
    //         'password' => Hash::make($data['password']),
    //     ]);
    // }

    public function pageNotFound()
    {
        dd(1);
        if (Auth::check()) {
            return view('errors.page_not_found');
        } else {
            return view('login');
        }
    }

    public function accessDenied()
    {
        if (Auth::check()) {
            return view('errors.access_denied');
        } else {
            return view('login');
        }
    }

    public function underConstruction()
    {
        if (Auth::check()) {
            return view('errors.under_construction');
        } else {
            return view('login');
        }
    }

}
