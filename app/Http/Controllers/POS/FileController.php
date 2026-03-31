<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\File;
use App\Services\CommonService as Common;
use Illuminate\Http\Request;
use Redirect;

class FileController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $FileData = File::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.File.index', compact('FileData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'file_name' => 'required',
                'file_url' => 'required',
                // 'file_url' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $RequestData = $request->all();
            $RequestData['file_url'] = null;
            $isInsert = File::create($RequestData);
            $SuccessFlag = false;

            if ($isInsert) {
                $SuccessFlag = true;
                $lastInsertQuery = File::latest()->first();

                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->id;

                $uploadFile = $request->file('file_url');

                if ($uploadFile != null) {

                    $FileType = $uploadFile->getMimeType();
                    $FileSize = $uploadFile->getSize();

                    // if (($FileType != "image/jpeg")
                    //     && ($FileType != "image/pjpeg")
                    //     && ($FileType != "image/jpg")
                    //     && ($FileType != "image/png")
                    //     && ($FileType != "image/gif")
                    //     && ($FileType != "text/plain")
                    //     && ($FileType != "application/pdf")
                    //     && ($FileType != "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
                    //     && ($FileType != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
                    // {
                    //     $uploadFile = null;
                    // } else {

                        $FileName = str_replace(" ","_",$RequestData['file_name']);

                        $upload = Common::fileUpload($uploadFile, $tableName, $pid, $FileName);

                        $lastInsertQuery->file_url = $upload;
                        $lastInsertQuery->file_size = $FileSize;
                        $lastInsertQuery->file_type = $FileType;

                        $isSuccess = $lastInsertQuery->update();

                        if ($isSuccess) {
                            $SuccessFlag = true;
                        } else {
                            $SuccessFlag = false;
                        }
                    // }
                }
            }

            if ($SuccessFlag) {
                $notification = array(
                    'message' => 'Successfully Upload',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/file_management')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Upload Failed',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            return view('POS.File.add');
        }

    }

    public function edit(Request $request, $id = null)
    {

        $FileData = File::where(['id' => $id, 'is_delete' => 0])->first();
        $tableName = $FileData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'file_name' => 'required',
                // 'file_url' => 'required',
                // 'file_url' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $Data = $request->all();

            $uploadFile = $request->file('file_url');

            if ($uploadFile != null) {
                $FileType = $uploadFile->getMimeType();
                $FileSize = $uploadFile->getSize();


                // if (($FileType != "image/jpeg")
                //         && ($FileType != "image/pjpeg")
                //         && ($FileType != "image/jpg")
                //         && ($FileType != "image/png")
                //         && ($FileType != "image/gif")
                //         && ($FileType != "text/plain")
                //         && ($FileType != "application/pdf")
                //         && ($FileType != "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
                //         && ($FileType != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
                // {
                //     $uploadFile = null;
                // } else {
                    $FileName = str_replace(" ","_",$Data['file_name']);

                    $upload = Common::fileUpload($uploadFile, $tableName, $pid, $FileName);
                    $Data['file_url'] = $upload;
                    $Data['file_size'] = $FileSize;
                    $Data['file_type'] = $FileType;
                // }
            }

            $isUpdate = $FileData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Upload',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/file_management')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Upload Failed',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        return view('POS.File.edit', compact('FileData'));
    }

    public function delete($id = null)
    {

        $FileData = File::where('id', $id)->first();

        $FileData->is_delete = 1;
        $delete = $FileData->save();
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

    public function isActive($id = null)
    {
        $FileData = File::where('id', $id)->first();
        if ($FileData->is_active == 1) {
            $FileData->is_active = 0;
        } else {
            $FileData->is_active = 1;
        }
        $Status = $FileData->save();

        if ($Status) {
            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Update',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

}
