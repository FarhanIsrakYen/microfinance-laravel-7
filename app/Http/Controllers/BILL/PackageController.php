<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BILL\Product;
use App\Model\BILL\PCategory;
use App\Model\BILL\Package;
use Redirect;

class PackageController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $productList = Product::where([['is_delete', 0],['is_active',1]])->select('id','product_name')->get();
        $packages = Package::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();

        $totalData = Package::where([['is_delete', 0],['is_active',1]])->count('id');

        $dataSet = array();
        $PackageData = array();

        foreach ($packages as $key => $row) {
            $tempSet = array();
            $product_arr = array();

            foreach ($productList as $product) {
                if (in_array($product->id, explode(',',$row->package_products))) {
                    $product_txt[] = $product->product_name;
                    $product_arr = array_unique(array_merge($product_arr, $product_txt));
                }
            }

            $tempSet = [
                'id' => $row->id,
                'package_name' => $row->package_name,
                'product' => implode(", ", $product_arr),
                'package_price' => $row->package_price,
            ];
            unset($product_arr,$product_txt);
            $dataSet[] = $tempSet;
        }

        return view('BILL.Package.index', compact('dataSet'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            

            $validateData = $request->validate([
                'package_name' => 'required',
                'package_products' => 'required',
                'package_price' => 'required',
            ]);

            $RequestData = $request->all();
            $RequestData['package_products'] = implode(",", $RequestData['package_products']);

            $isInsert = Package::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Package Product Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/package')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Package Product data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $PCategoryData = PCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $ProductData = Product::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            // dd($PCategoryData);
            return view('BILL.Package.add', compact('ProductData','PCategoryData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $PackageData = Package::where('id', $id)->first();
        // dd($PSubCategoryData);
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                //'company_id' => 'required',
                'package_name' => 'required',
                'package_products' => 'required',
                'package_price' => 'required',
            ]);

            $Data = $request->all();
            $Data['package_products'] = implode(",", $Data['package_products']);

            $isUpdate = $PackageData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Package Product Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/package')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product Package Product data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
          $PCategoryData = PCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $ProductData = Product::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('BILL.Package.edit', compact('ProductData','PackageData','PCategoryData'));

        }

    }

    public function view($id = null)
    {
        $productList = Product::where([['is_delete', 0],['is_active',1]])->select('id','product_name')->get();
        $PackageData = Package::where('id', $id)->first();
        $ProductData = Product::where('id', $id)->first();

        $tempSet = array();
        $product_arr = array();

        foreach ($productList as $product) {
            if (in_array($product->id, explode(',',$PackageData->package_products))) {
                $product_txt[] = $product->product_name;
                $product_arr = array_unique(array_merge($product_arr, $product_txt));
            }
        }
        
        return view('BILL.Package.view', compact('PackageData','ProductData','product_arr'));

    }

    public function delete($id = null)
    {

        $PackageData = Package::where('id', $id)->first();
        $PackageData->is_delete = 1;
        $delete = $PackageData->save();

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
        $PackageData = Package::where('id', $id)->first();
        if ($PackageData->is_active == 1) {
            $PackageData->is_active = 0;
        } else {
            $PackageData->is_active = 1;
        }
        $Status = $PackageData->save();

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
