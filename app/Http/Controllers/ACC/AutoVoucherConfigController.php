<?php

namespace App\Http\Controllers\acc;
use App\Model\Acc\MisType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Acc\VoucherType;
use DB;
use Redirect;
use App\Model\Acc\Ledger;
use App\Model\Acc\AutoVoucherConfig;


class AutoVoucherConfigController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
           
        } else {


            config()->set('database.connections.mysql.strict', false);
            DB::reconnect();

            $data = AutoVoucherConfig::select('id','config_id','sales_type','voucher_type','local_narration')->where('is_delete', 0)->groupBy('config_id')->get();

            // dd($data);
            return view('ACC.AutoVoucherConfig.index', compact('data'));
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

           
            
            $validateData = $request->validate([
                'voucher_type' => 'required',
                'sales_type' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            $cid = AutoVoucherConfig::select('config_id')->max('config_id');

            $cid +=1;


            $RequestData['config_id'] =$cid ;

            //


            $code_arr = (isset($RequestData['code_arr']) ? $RequestData['code_arr'] : array());
            $amount_arr = (isset($RequestData['AmountType_arr']) ? $RequestData['AmountType_arr'] : array());
            $mis_config_id = (isset($RequestData['mis_config_id']) ? $RequestData['mis_config_id'] : array());
            $mis_config_name = (isset($RequestData['mis_config_name']) ? $RequestData['mis_config_name'] : array());
            $table_field_name = (isset($RequestData['table_field_name']) ? $RequestData['table_field_name'] : array());
            $supplier_id_arr = (isset($RequestData['supplier_id_arr']) ? $RequestData['supplier_id_arr'] : array());

            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            $SuccessFlag = True;
            foreach ($code_arr as $key => $value) {
                if (!empty($value)) {
                    $RequestData['ledger_code'] = $value;
                    /// get ledger id from ledger code :::: 

                    $data = Ledger::where('code', $value)->first();
                    $RequestData['ledger_id'] = $data->id;


                    $RequestData['mis_config_id'] = $mis_config_id[$key];
                    $RequestData['mis_config_name'] =$mis_config_name[$key];
                    $RequestData['table_field_name'] = $table_field_name[$key];
                    $RequestData['supplier_id'] = $supplier_id_arr[$key];
                    $RequestData['amount_type'] = $amount_arr[$key];
                    // dd($RequestData);

                    $isInsert = AutoVoucherConfig::create($RequestData);

                    if(!$isInsert){
                        $SuccessFlag = false;
                    }
                    // $isInsertDetails = Issued::create($RequestData);
                }
            }


            if ($SuccessFlag) {

                $notification = array(
                    'message' => 'Successfully inserted Auto Voucher Configaration ',
                    'alert-type' => 'success',
                );

                return Redirect::to('acc/auto_v_config')->with($notification);

            }else{

                $notification = array(
                            'message' => 'Unsuccessful to inserted Auto Voucher Configaration',
                            'alert-type' => 'error',
                        );
                        return redirect()->back()->with($notification);


            }

    
        } else {
            $misType = MisType::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            //dd( $misType);
            $vtype = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AutoVoucherConfig.add', compact('misType','vtype'));
        }
    }

    public function edit(Request $request, $id = null)
    {


        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'voucher_type' => 'required',
                'sales_type' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            $data = AutoVoucherConfig::where('id', $id)->first();
            // dd($data );
            $cid = $data->config_id;

            // $cid +=1;

           
            AutoVoucherConfig::where('config_id', $cid)->get()->each->delete();

            $RequestData['config_id'] =$cid ;

            //
            //dd($RequestData);

            $code_arr = (isset($RequestData['code_arr']) ? $RequestData['code_arr'] : array());
            $amount_arr = (isset($RequestData['AmountType_arr']) ? $RequestData['AmountType_arr'] : array());
            $mis_config_id = (isset($RequestData['mis_config_id']) ? $RequestData['mis_config_id'] : array());
            $mis_config_name = (isset($RequestData['mis_config_name']) ? $RequestData['mis_config_name'] : array());
            $table_field_name = (isset($RequestData['table_field_name']) ? $RequestData['table_field_name'] : array());
            $supplier_id_arr = (isset($RequestData['supplier_id_arr']) ? $RequestData['supplier_id_arr'] : array());

            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());
            $SuccessFlag = True;
            foreach ($code_arr as $key => $value) {
                if (!empty($value)) {
                    $RequestData['ledger_code'] = $value;
                    /// get ledger id from ledger code :::: 

                    $data = Ledger::where('code', $value)->first();
                    $RequestData['ledger_id'] = $data->id;


                    $RequestData['mis_config_id'] = $mis_config_id[$key];
                    $RequestData['mis_config_name'] =$mis_config_name[$key];
                    $RequestData['table_field_name'] = $table_field_name[$key];
                    $RequestData['supplier_id'] = $supplier_id_arr[$key];
                    $RequestData['amount_type'] = $amount_arr[$key];
                    // dd($RequestData);

                    $isInsert = AutoVoucherConfig::create($RequestData);
                    // $isInsertDetails = Issued::create($RequestData);
                    if(!$isInsert){
                        $SuccessFlag = false;
                    }
                }
            }


            if ($SuccessFlag) {

                $notification = array(
                    'message' => 'Successfully Updated Auto Voucher Configaration ',
                    'alert-type' => 'success',
                );

                return Redirect::to('acc/auto_v_config')->with($notification);

            }else{

                $notification = array(
                            'message' => 'Unsuccessful to Updated Auto Voucher Configaration',
                            'alert-type' => 'error',
                        );
                        return redirect()->back()->with($notification);


            }

        } else {

         
            // $misType = MisType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $data = AutoVoucherConfig::where('id', $id)->first();
            $dataset = AutoVoucherConfig::where('config_id', $data->config_id)->get();
            // $vtype = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AutoVoucherConfig.edit', compact('dataset'));
        }
    }

    public function view($id = null)
    {
            // $misType = MisType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $data = AutoVoucherConfig::where('id', $id)->first();
            $dataset = AutoVoucherConfig::where('config_id', $data->config_id)->get();
            // $vtype = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AutoVoucherConfig.view', compact('dataset'));
    }

    public function delete($id = null)
    {
        $data = AutoVoucherConfig::where('id', $id)->first();
        $delete = AutoVoucherConfig::where('config_id', $data->config_id)->update(['is_delete' => 1]);
        

        if ($delete) {
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

    // public function isActive($id = null)
    // {
    //     $AutoVoucher = AutoVoucher::where('id', $id)->first();
    //     if ($AutoVoucher->is_active == 1) {
    //         $AutoVoucher->is_active = 0;
    //         # code...
    //     } else {
    //         $AutoVoucher->is_active = 1;
    //     }

    //     $Status = $AutoVoucher->save();

    //     if ($Status) {
    //         $notification = array(
    //             'message' => 'Successfully Updated',
    //             'alert-type' => 'success',
    //         );
    //         return redirect()->back()->with($notification);
    //     } else {
    //         $notification = array(
    //             'message' => 'Unsuccessful to Update',
    //             'alert-type' => 'error',
    //         );
    //         return redirect()->back()->with($notification);
    //     }
    // }
}
