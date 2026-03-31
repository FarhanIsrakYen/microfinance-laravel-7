<?php

namespace App\Http\Controllers\GNL;
use App\Model\GNL\SignatureSettings;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class SignatureSettingsController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     parent::__construct();
    // }
    
    public function index(Request $req) {

        

        $dataSignature =SignatureSettings::where([['gnl_signature_setting.is_delete', 0],])
                    ->join('gnl_sys_modules', 'gnl_sys_modules.id', 'gnl_signature_setting.module_id')
                    ->select('gnl_signature_setting.*','gnl_sys_modules.module_name')
                    ->orderby('gnl_signature_setting.module_id')
                    ->get();



       
            $data = array(
                // 'module_data' => $module_data,
                'dataSignature' => $dataSignature,
                // 'signatureImageFilename' => $signatureImageFilename,
            );
            

        return view('GNL.SignatureSettings.index',$data);
    }

    public function add(Request $req) {

        if ($req->isMethod('post')) {
            $RequestData = $req->all();
            // dd($RequestData);


          

            DB::beginTransaction();

            try {

                $head_title = (isset($RequestData['head_title']) ? $RequestData['head_title'] : array());
                $head_signatorDesignationId = (isset($RequestData['head_signatorDesignationId']) ? $RequestData['head_signatorDesignationId'] : array());
                $head_signatorEmployeeId = (isset($RequestData['head_signatorEmployeeId']) ? $RequestData['head_signatorEmployeeId'] : array());
                $head_positionOrder = (isset($RequestData['head_positionOrder']) ? $RequestData['head_positionOrder'] : array());
    
                $RequestData2['module_id'] = $RequestData['module_id'];
                    foreach ($head_title as $key => $headtitle) {
                        if (!empty($headtitle) && !empty($head_signatorDesignationId[$key])) {
                            $RequestData2['applicableFor'] = 'HeadOffice';
                            $RequestData2['title'] = $headtitle;
                            $RequestData2['signatorDesignationId'] = $head_signatorDesignationId[$key];
                            $RequestData2['signatorEmployeeId'] = $head_signatorEmployeeId[$key];                            
                            $RequestData2['positionOrder'] = $head_positionOrder[$key];
                            $isInsertDetails = SignatureSettings::create($RequestData2);
                        }
                    }
                
                $branch_title = (isset($RequestData['branch_title']) ? $RequestData['branch_title'] : array());
                $branch_signatorDesignationId = (isset($RequestData['branch_signatorDesignationId']) ? $RequestData['branch_signatorDesignationId'] : array());
                // $branch_signatorEmployeeId = (isset($RequestData['branch_signatorEmployeeId']) ? $RequestData['branch_signatorEmployeeId'] : array());
                $branch_positionOrder = (isset($RequestData['branch_positionOrder']) ? $RequestData['branch_positionOrder'] : array());
    

                    foreach ($branch_title as $key => $branchtitle) {
                        if (!empty($branchtitle) && !empty($branch_signatorDesignationId[$key])) {
                            $RequestData2['applicableFor'] = 'Branch';
                            $RequestData2['title'] = $branchtitle;
                            $RequestData2['signatorDesignationId'] = $branch_signatorDesignationId[$key];
                            // $RequestData2['signatorEmployeeId'] = $branch_signatorEmployeeId[$key];                            
                            $RequestData2['positionOrder'] = $branch_positionOrder[$key];
                            $isInsertDetails = SignatureSettings::create($RequestData2);
                        }
                    }

               
                // Your Code here
                DB::commit();
                // return
                $notification = array(
                    'message' => 'Successfully inserted Signature Settings',
                    'alert-type' => 'success',
                );

                return Redirect::to('gnl/signature_set')->with($notification);
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Issue List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

           
        } 
        $EmpDesignations = DB::table('hr_designations')
                        ->where([['is_delete', 0],])
                        ->get();


        $dataSignature =SignatureSettings::where([['gnl_signature_setting.is_delete', 0],])->select('module_id')->distinct('module_id')
                                        ->get();

                                        
        $module_data =DB::table('gnl_sys_modules')->whereNotIn('id', $dataSignature->pluck('module_id')->toArray())
        ->where([['is_delete', 0],])
        ->get();

        // dd($dataSignature,array_unique($dataSignature->pluck('date')->toArray()),$module_data);

        $data = array(
            'module_data' => $module_data,
            'EmpDesignations' => $EmpDesignations,
            // 'signatureImageFilename' => $signatureImageFilename,
        );
        return view('GNL.SignatureSettings.add',$data);
    }


    public function edit(Request $req,$id = null) {
        $module_id = SignatureSettings::where([['is_delete', 0],])
                        ->where('id',$id)
                        ->first()->module_id;
        if ($req->isMethod('post')) {

            $RequestData = $req->all();
            SignatureSettings::where('module_id',$module_id)->delete();
            // dd($RequestData);


          

            DB::beginTransaction();

            try {
                $RequestData2['module_id'] = $RequestData['module_id'];

                $head_title = (isset($RequestData['head_title']) ? $RequestData['head_title'] : array());
                $head_signatorDesignationId = (isset($RequestData['head_signatorDesignationId']) ? $RequestData['head_signatorDesignationId'] : array());
                $head_signatorEmployeeId = (isset($RequestData['head_signatorEmployeeId']) ? $RequestData['head_signatorEmployeeId'] : array());
                $head_positionOrder = (isset($RequestData['head_positionOrder']) ? $RequestData['head_positionOrder'] : array());
    
                   
                    foreach ($head_title as $key => $headtitle) {
                        if (!empty($headtitle) && !empty($head_signatorDesignationId[$key])) {
                            $RequestData2['applicableFor'] = 'HeadOffice';
                            $RequestData2['title'] = $headtitle;
                            $RequestData2['signatorDesignationId'] = $head_signatorDesignationId[$key];
                            $RequestData2['signatorEmployeeId'] = $head_signatorEmployeeId[$key];                            
                            $RequestData2['positionOrder'] = $head_positionOrder[$key];
                            $isInsertDetails = SignatureSettings::create($RequestData2);
                        }
                    }
                
                $branch_title = (isset($RequestData['branch_title']) ? $RequestData['branch_title'] : array());
                $branch_signatorDesignationId = (isset($RequestData['branch_signatorDesignationId']) ? $RequestData['branch_signatorDesignationId'] : array());
                // $branch_signatorEmployeeId = (isset($RequestData['branch_signatorEmployeeId']) ? $RequestData['branch_signatorEmployeeId'] : array());
                $branch_positionOrder = (isset($RequestData['branch_positionOrder']) ? $RequestData['branch_positionOrder'] : array());
    

                    foreach ($branch_title as $key => $branchtitle) {
                        if (!empty($branchtitle) && !empty($branch_signatorDesignationId[$key])) {
                            $RequestData2['applicableFor'] = 'Branch';
                            $RequestData2['title'] = $branchtitle;
                            $RequestData2['signatorDesignationId'] = $branch_signatorDesignationId[$key];
                            // $RequestData2['signatorEmployeeId'] = $branch_signatorEmployeeId[$key];                            
                            $RequestData2['positionOrder'] = $branch_positionOrder[$key];
                            $isInsertDetails = SignatureSettings::create($RequestData2);
                        }
                    }

               
                // Your Code here
                DB::commit();
                // return
                $notification = array(
                    'message' => 'Successfully inserted Signature Settings',
                    'alert-type' => 'success',
                );

                return Redirect::to('mfn/signature_set')->with($notification);
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Issue List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } 
        $employeeData = DB::table('hr_employees')
        ->where([['is_delete', 0],])
        ->get();
        
        
        $headData = SignatureSettings::where([['is_delete', 0],])
                        ->where('applicableFor','HeadOffice')
                        ->where('module_id',$module_id)
                        ->get();
        $branchData = SignatureSettings::where([['is_delete', 0],])
                    ->where('applicableFor','Branch')
                    ->where('module_id',$module_id)
                    ->get();
        $EmpDesignations = DB::table('hr_designations')
                    ->where([['is_delete', 0],])
                    ->get();

        $module_data =DB::table('gnl_sys_modules')->where('id', $module_id)
        ->where([['is_delete', 0],])
        ->first();

                    
        $data = array(
            'module_data' => $module_data,
            'module_id' => $module_id,
            'branchData' => $branchData,
            'headData' => $headData,
            'employeeData' => $employeeData,
            'EmpDesignations' => $EmpDesignations,
        );
        return view('GNL.SignatureSettings.edit',$data);
    }
    public function delete(Request $req)
    {
        $wareaData = SignatureSettings::where('id', $req->id)->first();


        $wareaData->is_delete = 1;

        $delete = $wareaData->save();

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

    public function isActive(Request $req)
    {
        $wareaData = SignatureSettings::where('id', $req->id)->first();


        if( $wareaData->status== 1){
            $wareaData->status = 0;
        }else{
            $wareaData->status = 1;
        }
        

        $upate = $wareaData->save();

        if ($upate) {
            $notification = array(
                'message' => 'Successfully Status Changed',
                'alert-type' => 'success',
            );
           return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful changing Status',
                'alert-type' => 'error',
            );
           return redirect()->back()->with($notification);
        }
    }
}
