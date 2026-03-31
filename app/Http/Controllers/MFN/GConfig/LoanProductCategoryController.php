<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Services\RoleService;
use App\Http\Controllers\Controller;
use App\Model\MFN\LoanProductCategory;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class LoanProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req) {

        if ($req->ajax()) {
            $columns = ['mfn_loan_product_category.name', 'mfn_loan_product_category.shortName'];

            $limit = $req->length;
            $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
            $order = $columns[$orderColumnIndex];
            $dir = $req->input('order.0.dir');
            // Searching variable
            $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

            $loanPCategories = DB::table('mfn_loan_product_category')
                ->where('is_delete', 0)
                ->orderBy($order, $dir)
                ->limit($limit);

            if ($search != null) {
                $loanPCategories->where(function ($query) use ($search) {
                    $query->where('mfn_loan_product_category.name', 'LIKE', "%{$search}%")
                          ->orWhere('mfn_loan_product_category.shortName', 'LIKE', "%{$search}%");
                });
            }
            $loanPCategories = $loanPCategories->get();

            $totalData = DB::table('mfn_loan_product_category')->where('is_delete', 0)->count('id');

            $sl = (int)$req->start + 1;
            foreach ($loanPCategories as $key => $loanPCat) {
                $loanPCategories[$key]->sl = $sl++;
                $loanPCategories[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $loanPCategories[$key]->id);
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $loanPCategories,
            );

            return response()->json($data);
        }else {
            return view('MFN.LoanProductCategory.index');
        }
    }


    public function view($loanProdCatId)
    {
        $loanPCategory = LoanProductCategory::where('id', $loanProdCatId)->first();

        $data = array(
            'loanPCategory'     => $loanPCategory
        );

        return view('MFN.LoanProductCategory.view', $data);
    }


    public function add(Request $req) {

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $loanPCategory = new LoanProductCategory();
            $loanPCategory->name        = $req->name;
            $loanPCategory->shortName    = $req->shortName;
            $loanPCategory->created_by  = Auth::user()->id;
            $loanPCategory->created_at  = Carbon::now();
            $loanPCategory->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('MFN.LoanProductCategory.add');
        }
    }

    public function edit(Request $req) {

        $loanPCategoryList = LoanProductCategory::where('id', $req->loanProdCatId)->first();;

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $loanPCategory = LoanProductCategory::find($loanPCategoryList->id);
            $loanPCategory->name        = $req->name;
            $loanPCategory->shortName    = $req->shortName;
            $loanPCategory->updated_by  = Auth::user()->id;
            $loanPCategory->updated_at  = Carbon::now();
            $loanPCategory->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('MFN.LoanProductCategory.edit',compact('loanPCategoryList'));
        }
    }


    public function delete($id = null) {
        $loanPCategoryData = LoanProductCategory::where('id', $id)->first();

        $loanPCategoryData->is_delete = 1;

        $delete = $loanPCategoryData->save();

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

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;

        $validator = Validator::make($req->all(), [
            'name'   => 'required',
            'shortName'  => 'required',
        ]);

        if ($validator->fails()) {
            $errorMsg = implode(' || ', $validator->messages()->all());
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
