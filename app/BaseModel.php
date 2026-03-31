<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Config;

class BaseModel extends Model
{
    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::saving(function ($model) {

            if(in_array('created_by', $model->fillable)){
                $model->created_by = Auth::id();
            }

            // $branch_to_table = [
            //     'pos_issues_m',
            //     'pos_issues_d'
            // ];

            // $branch_from_table = [
            //     'pos_requisitions_m',
            //     'pos_requisitions_d',
            //     'pos_issues_r_m',
            //     'pos_issues_r_d'
            // ];

            $branch_multiple_table = [
                'pos_issues_m',
                'pos_issues_d',
                'pos_issues_r_m',
                'pos_issues_r_d',
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_transfers_m',
                'pos_transfers_d'
            ];

            if($model->table != 'access_query_log')
            {
                $accessLogData = array();

                $accessLogData['table_name'] = $model->table;

                if (in_array($accessLogData['table_name'], $branch_multiple_table)) {
                    $accessLogData['branch_id'] = $model->branch_from;
                    $accessLogData['branch_to'] = $model->branch_to;
                }
                else{
                    $accessLogData['branch_id'] = $model->branch_id;
                }

                // if (in_array($accessLogData['table_name'], $branch_from_table)) {
                //     $accessLogData['branch_id'] = $model->branch_from;
                // }
                // elseif (in_array($accessLogData['table_name'], $branch_to_table)) {
                //     $accessLogData['branch_to'] = $model->branch_to;
                // }
                // elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                //     $accessLogData['branch_id'] = $model->branch_id;
                // }

                // if($accessLogData['table_name'] == "pos_transfers_m" || $accessLogData['table_name'] == 'pos_transfers_d'){
                //     $accessLogData['branch_to'] = $model->branch_to;
                //     $accessLogData['branch_from'] = $model->branch_from;
                // }

                $accessLogData['operation_type'] = "insert";

                session()->put('accessLogData', $accessLogData);
            }

            /* $ignoreArr = [
                'query_log_branch',
                'query_log_fixed',
                'query_log_trans'
            ];

            $branch_to_table = [
                'pos_issues_m',
                'pos_issues_d'
            ];

            $branch_from_table = [
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_issues_r_m',
                'pos_issues_r_d'
            ];

            if(!in_array($model->table, $ignoreArr))
            {
                $dblogData = array();

                $dblogData['table_name'] = $model->table;

                if (in_array($dblogData['table_name'], $branch_from_table)) {
                    $dblogData['branch_id'] = $model->branch_from;
                }
                elseif (in_array($dblogData['table_name'], $branch_to_table)) {
                    $dblogData['branch_id'] = $model->branch_to;
                }
                elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                    $dblogData['branch_id'] = $model->branch_id;
                }

                if($dblogData['table_name'] == "pos_transfers_m" || $dblogData['table_name'] == 'pos_transfers_d'){
                    $dblogData['branch_to'] = $model->branch_to;
                    $dblogData['branch_from'] = $model->branch_from;
                }

                if($dblogData['table_name'] == "acc_voucher"){
                    $dblogData['voucher_code'] = $model->voucher_code;
                    session()->put('voucher_code', $dblogData['voucher_code']);
                }

                $dblogData['fillable'] = implode(',', $model->fillable);
                $dblogData['attributes'] = implode(',', array_keys($model->attributes));
                $dblogData['attr_values'] = implode(',', $model->attributes);
                $dblogData['operation_type'] = "insert";

                session()->put('dblogData', $dblogData);
            } */
        });

        // create a event to happen on updating
        static::updating(function ($model) {

            if(in_array('updated_by', $model->fillable)){
                $model->updated_by = Auth::id();
            }

            $branch_multiple_table = [
                'pos_issues_m',
                'pos_issues_d',
                'pos_issues_r_m',
                'pos_issues_r_d',
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_transfers_m',
                'pos_transfers_d'
            ];

            // $branch_to_table = [
            //     'pos_issues_m',
            //     'pos_issues_d'
            // ];

            // $branch_from_table = [
            //     'pos_requisitions_m',
            //     'pos_requisitions_d',
            //     'pos_issues_r_m',
            //     'pos_issues_r_d'
            // ];

            if($model->table != 'access_query_log')
            {
                $accessLogData = array();

                $accessLogData['table_name'] = $model->table;

                if (in_array($accessLogData['table_name'], $branch_multiple_table)) {
                    $accessLogData['branch_id'] = $model->branch_from;
                    $accessLogData['branch_to'] = $model->branch_to;
                }
                else{
                    $accessLogData['branch_id'] = $model->branch_id;
                }

                // if (in_array($accessLogData['table_name'], $branch_from_table)) {
                //     $accessLogData['branch_id'] = $model->branch_from;
                // }
                // elseif (in_array($accessLogData['table_name'], $branch_to_table)) {
                //     $accessLogData['branch_id'] = $model->branch_to;
                // }
                // elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                //     $accessLogData['branch_id'] = $model->branch_id;
                // }

                // if($accessLogData['table_name'] == "pos_transfers_m" || $accessLogData['table_name'] == 'pos_transfers_d'){
                //     $accessLogData['branch_to'] = $model->branch_to;
                //     $accessLogData['branch_from'] = $model->branch_from;
                // }

                if ($model->is_delete == 1) {
                    $accessLogData['operation_type'] = "delete";
                }
                else{
                    $accessLogData['operation_type'] = "update";
                }

                session()->put('accessLogData', $accessLogData);
            }

            /* $ignoreArr = [
                'query_log_branch',
                'query_log_fixed',
                'query_log_trans'
            ];

            $branch_to_table = [
                'pos_issues_m',
                'pos_issues_d'
            ];

            $branch_from_table = [
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_issues_r_m',
                'pos_issues_r_d'
            ];

            if(!in_array($model->table, $ignoreArr))
            {
                $dblogData = array();

                $dblogData['table_name'] = $model->table;

                if (in_array($dblogData['table_name'], $branch_from_table)) {
                    $dblogData['branch_id'] = $model->branch_from;
                }
                elseif (in_array($dblogData['table_name'], $branch_to_table)) {
                    $dblogData['branch_id'] = $model->branch_to;
                }
                elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                    $dblogData['branch_id'] = $model->branch_id;
                }

                $dblogData['fillable'] = implode(',', $model->fillable);
                $dblogData['attributes'] = implode(',', array_keys($model->attributes));
                $dblogData['attr_values'] = implode(',', $model->attributes);

                if ($model->is_delete == 1) {
                    $dblogData['operation_type'] = "delete";
                }
                else{
                    $dblogData['operation_type'] = "update";
                }

                session()->put('dblogData', $dblogData);
            } */
        });

        // // create a event to happen on deleting
        static::deleting(function ($model) {
            // $model->deleted_by = Auth::user()->username;


            $branch_multiple_table = [
                'pos_issues_m',
                'pos_issues_d',
                'pos_issues_r_m',
                'pos_issues_r_d',
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_transfers_m',
                'pos_transfers_d'
            ];

            // $branch_to_table = [
            //     'pos_issues_m',
            //     'pos_issues_d'
            // ];

            // $branch_from_table = [
            //     'pos_requisitions_m',
            //     'pos_requisitions_d',
            //     'pos_issues_r_m',
            //     'pos_issues_r_d'
            // ];

            if($model->table != 'access_query_log')
            {
                $accessLogData = array();

                $accessLogData['table_name'] = $model->table;

                if (in_array($accessLogData['table_name'], $branch_multiple_table)) {
                    $accessLogData['branch_id'] = $model->branch_from;
                    $accessLogData['branch_to'] = $model->branch_to;
                }
                else{
                    $accessLogData['branch_id'] = $model->branch_id;
                }

                // if (in_array($accessLogData['table_name'], $branch_from_table)) {
                //     $accessLogData['branch_id'] = $model->branch_from;
                // }
                // elseif (in_array($accessLogData['table_name'], $branch_to_table)) {
                //     $accessLogData['branch_id'] = $model->branch_to;
                // }
                // elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                //     $accessLogData['branch_id'] = $model->branch_id;
                // }

                // if($accessLogData['table_name'] == "pos_transfers_m" || $accessLogData['table_name'] == 'pos_transfers_d'){
                //     $accessLogData['branch_to'] = $model->branch_to;
                //     $accessLogData['branch_from'] = $model->branch_from;
                // }

                $accessLogData['operation_type'] = "hard_delete";

                session()->put('accessLogData', $accessLogData);
            }

            /* $ignoreArr = [
                'query_log_branch',
                'query_log_fixed',
                'query_log_trans'
            ];

            $branch_to_table = [
                'pos_issues_m',
                'pos_issues_d'
            ];
            $branch_from_table = [
                'pos_requisitions_m',
                'pos_requisitions_d',
                'pos_issues_r_m',
                'pos_issues_r_d'
            ];

            if(!in_array($model->table, $ignoreArr))
            {
                $dblogData = array();

                $dblogData['table_name'] = $model->table;

                if (in_array($dblogData['table_name'], $branch_from_table)) {
                    $dblogData['branch_id'] = $model->branch_from;
                }
                elseif (in_array($dblogData['table_name'], $branch_to_table)) {
                    $dblogData['branch_id'] = $model->branch_to;
                }
                elseif (!empty($model->branch_id) || !is_null($model->branch_id)) {
                    $dblogData['branch_id'] = $model->branch_id;
                }

                $dblogData['fillable'] = implode(',', $model->fillable);
                $dblogData['attributes'] = implode(',', array_keys($model->attributes));
                $dblogData['attr_values'] = implode(',', $model->attributes);

                $dblogData['operation_type'] = "deleting";

                session()->put('dblogData', $dblogData);
            } */
        });

    }
}
