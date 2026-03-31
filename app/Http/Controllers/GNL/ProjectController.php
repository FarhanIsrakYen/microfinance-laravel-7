<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Group;
use App\Model\GNL\Project;
use Illuminate\Http\Request;
use Redirect;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $ProjectData = Project::where('is_delete', 0)->orderBy('project_code', 'ASC')->get();
        return view('GNL.Project.index', compact('ProjectData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'group_id' => 'required',
                'project_name' => 'required',
                'project_code' => 'required',
            ]);

            $RequestData = $request->all();
            $isInsert = Project::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Project Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/project')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Project',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Project.add', compact('GroupData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $ProjectData = Project::where('id', $id)->first();
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'group_id' => 'required',
                'project_name' => 'required',
                'project_code' => 'required',
            ]);

            $Data = $request->all();
            $isUpdate = $ProjectData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Project Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/project')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Project',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $ProjectData = Project::where('id', $id)->first();
            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Project.edit', compact('ProjectData', 'GroupData'));
        }
    }

    public function view($id = null)
    {
        $ProjectData = Project::where('id', $id)->first();
        return view('GNL.Project.view', compact('ProjectData'));
    }

    public function delete($id = null)
    {
        $ProjectData = Project::where('id', $id)->first();
        $ProjectData->is_delete = 1;
        $delete = $ProjectData->save();

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
        $ProjectData = Project::where('id', $id)->first();
        if ($ProjectData->is_active == 1) {
            $ProjectData->is_active = 0;
        } else {
            $ProjectData->is_active = 1;
        }
        $Status = $ProjectData->save();

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
