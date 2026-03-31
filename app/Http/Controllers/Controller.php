<?php

namespace App\Http\Controllers;

use App\Services\CommonService as Common;
use DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Route;
use Session;
use View;
use Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $current_route_name;
    protected $GlobalRole;

    public function __construct()
    {
        $this->current_route_name = Route::getCurrentRoute()->uri();

        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor

            $RolePermissionAll = (!empty(session()->get('LoginBy.user_role.role_permission'))) ? session()->get('LoginBy.user_role.role_permission') : array();

            $this->GlobalRole = (isset($RolePermissionAll[$this->current_route_name])) ? $RolePermissionAll[$this->current_route_name] : array();

            return $next($request);
        });

        View::share('current_route_name', $this->current_route_name);
        View::share('GlobalRole', $this->GlobalRole);

        DB::listen(function ($query) {

            if (strpos($query->sql, 'access_query_log')) {
                return false;
            }

            $insert = "insert";
            $update = "update";
            $delete = "delete from";

            if (preg_match("/{$insert}/i", $query->sql) || preg_match("/{$update}/i", $query->sql) || preg_match("/{$delete}/i", $query->sql)) {

                $q      = $query->sql;
                $needle = '?';
                foreach ($query->bindings as $replace) {
                    $pos = strpos($q, $needle);
                    if ($pos !== false) {

                        if (is_numeric($replace)) {
                            $replace = $replace;
                        }
                        elseif(is_a($replace, 'DateTime')){
                            $replace = $replace->format('Y-m-d');
                        } else {
                            $replace = "'" . $replace . "'";
                        }

                        $q = substr_replace($q, $replace, $pos, strlen($needle));
                    }
                }

                $accessLogData  = session()->get('accessLogData');
                session()->forget('accessLogData');

                if ($accessLogData != null) {

                    $accessLogData['company_id'] = Common::getCompanyId();
                    $accessLogData['query_sql']  = $q;
                    $accessLogData['execution_by']  = Auth::user()->id;

                    if (in_array($accessLogData['table_name'], DB::table('access_query_table')->where('is_active', 1)->pluck('table_name')->toArray())) {
                        DB::table('access_query_log')->insert($accessLogData);
                    }
                }
            }

            // if (strpos($query->sql, 'query_log_branch') || strpos($query->sql, 'query_log_trans') || strpos($query->sql, 'query_log_fixed')) {
            //     return false;
            // }

            // $insert = "insert";
            // $update = "update";
            // $delete = "delete from";

            // if (preg_match("/{$insert}/i", $query->sql) || preg_match("/{$update}/i", $query->sql) || preg_match("/{$delete}/i", $query->sql)) {
            //     $q      = $query->sql;
            //     $needle = '?';
            //     foreach ($query->bindings as $replace) {
            //         $pos = strpos($q, $needle);
            //         if ($pos !== false) {

            //             if (is_numeric($replace)) {
            //                 $replace = $replace;
            //             } else {
            //                 // $replace = ' "'.addslashes($replace).'" ';
            //                 $replace = "'" . $replace . "'";
            //             }

            //             $q = substr_replace($q, $replace, $pos, strlen($needle));
            //         }
            //     }

            //     $logArray      = array();
            //     $dataFromModel = session()->get('dblogData');
            //     session()->forget('dblogData');

            //     $logArray['company_id'] = Common::getCompanyId();
            //     $logArray['query_sql']  = $q;

            //     if ($dataFromModel != null) {

            //         $logArray = array_merge($logArray, $dataFromModel);

            //         if ($logArray['table_name'] == "pos_transfers_m" || $logArray['table_name'] == "pos_transfers_d") {

            //             DB::table('query_log_transfer')->insert($logArray);

            //         } else {

            //             $logArray_duplicate = $logArray;
            //             unset($logArray_duplicate['branch_to']);
            //             unset($logArray_duplicate['branch_from']);

            //             $logArray_duplicate['branch_id'] = Common::getBranchId();

            //             if (Common::getBranchId() == 1) {

            //                 $transactions = DB::table('query_db_ho')->pluck('table_name')->toArray();

            //                 if (in_array($logArray_duplicate['table_name'], $transactions)) {
            //                     if ($logArray_duplicate['table_name'] == "acc_voucher_details") {
            //                         $logArray_duplicate['voucher_code'] = session()->get('voucher_code');
            //                     }

            //                     DB::table('query_log_trans')->insert($logArray_duplicate);

            //                 } else {
            //                     $ignore_table = DB::table('query_db_ho_ig')->pluck('table_name')->toArray();

            //                     if (!in_array($logArray_duplicate['table_name'], $ignore_table)) {
            //                         DB::table('query_log_fixed')->insert($logArray_duplicate);
            //                     }
            //                 }
            //             } else {
            //                 $transactions = DB::table('query_db_branch')->pluck('table_name')->toArray();

            //                 if (in_array($logArray_duplicate['table_name'], $transactions)) {
            //                     DB::table('query_log_branch')->insert($logArray_duplicate);
            //                 }
            //             }
            //         }
            //     }
            // }
        });
    }
}
