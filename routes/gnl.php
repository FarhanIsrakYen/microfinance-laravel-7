<?php

// $this->middleware(['auth', 'permission']);
// ['auth', 'web']
// 'auth'

// Employee Transfer routes
Route::group(['prefix' => 'gnl/employeeTransfer'], function () {
    Route::any('/', 'HR\EmployeeTransferController@index');
    Route::any('add', 'HR\EmployeeTransferController@add');
    Route::any('edit/{id}', 'HR\EmployeeTransferController@edit');
    Route::get('view/{id}', 'HR\EmployeeTransferController@view');
    Route::any('delete/{id}', 'HR\EmployeeTransferController@delete');
    Route::any('approve', 'HR\EmployeeTransferController@approve');
    Route::any('getData', 'HR\EmployeeTransferController@getData');
});

Route::group(['middleware' => ['auth', 'permission'], 'prefix' => 'gnl', 'namespace' => 'GNL'], function () {

    // Controllers Within The "App\Http\Controllers\Admin" Namespace
    Route::get('/', 'DashboardController@index');
  
    // // ## Group Route
    // match(['get', 'post'],
    Route::group(['prefix' => 'group'], function () {

        Route::any('/', 'GroupController@index');
        Route::any('add', 'GroupController@add');
        Route::any('edit/{id}', 'GroupController@edit');
        Route::get('view/{id}', 'GroupController@view');
        Route::any('delete/{id}', 'GroupController@delete');
        Route::any('publish/{id}', 'GroupController@isActive');

    });

    // // ## Company Route
    Route::group(['prefix' => 'company'], function () {

        Route::any('/', 'CompanyController@index');
        Route::any('add', 'CompanyController@add');
        Route::any('edit/{id}', 'CompanyController@edit');
        Route::get('view/{id}', 'CompanyController@view');
        Route::any('delete/{id}', 'CompanyController@delete');
        Route::any('publish/{id}', 'CompanyController@isActive');

    });

    // // ## Project Route
    Route::group(['prefix' => 'project'], function () {

        Route::any('/', 'ProjectController@index');
        Route::any('add', 'ProjectController@add');
        Route::any('edit/{id}', 'ProjectController@edit');
        Route::get('view/{id}', 'ProjectController@view');
        Route::any('delete/{id}', 'ProjectController@delete');
        Route::any('publish/{id}', 'ProjectController@isActive');

    });

    // // ## ProjectType Route
    Route::group(['prefix' => 'project_type'], function () {

        Route::any('/', 'ProjectTypeController@index');
        Route::any('add', 'ProjectTypeController@add');
        Route::any('edit/{id}', 'ProjectTypeController@edit');
        Route::get('view/{id}', 'ProjectTypeController@view');
        Route::any('delete/{id}', 'ProjectTypeController@delete');
        Route::any('publish/{id}', 'ProjectTypeController@isActive');

        // Route::get('ajaxProject', 'ProjectTypeController@ajaxProjectLoad');

    });

    // // ## Branch Route
    Route::group(['prefix' => 'branch'], function () {

        Route::any('/', 'BranchController@index')->name('branchDatatable');
        Route::any('add', 'BranchController@add');
        Route::any('edit/{id}', 'BranchController@edit');
        Route::get('view/{id}', 'BranchController@view');
        Route::any('delete/{id}', 'BranchController@delete');
        Route::any('publish/{id}', 'BranchController@isActive');
        Route::get('approve/{id}', 'BranchController@isApprove');

        // Route::get('ajaxProjectType', 'BranchController@ajaxProjectTypeLoad');

    });

    // // ## Division Route
    Route::group(['prefix' => 'division'], function () {

        Route::any('/', 'AddressController@divIndex');
        Route::any('add', 'AddressController@divAdd');
        Route::any('edit/{id}', 'AddressController@divEdit');
        Route::get('view/{id}', 'AddressController@divView');
        Route::any('delete/{id}', 'AddressController@divDelete');
        Route::any('publish/{id}', 'AddressController@divIsactive');

    });

    // // ## District Route
    Route::group(['prefix' => 'district'], function () {

        Route::any('/', 'AddressController@disIndex')->name('DistrictDatatable');
        Route::any('add', 'AddressController@disAdd');
        Route::any('edit/{id}', 'AddressController@disEdit');
        Route::get('view/{id}', 'AddressController@disView');
        Route::any('delete/{id}', 'AddressController@disDelete');
        Route::any('publish/{id}', 'AddressController@disIsactive');

    });

    // // ## Upzilla Route
    Route::group(['prefix' => 'upazila'], function () {

        Route::any('/', 'AddressController@upIndex')->name('UpazilaDatatable');
        Route::any('add', 'AddressController@upAdd');
        Route::any('edit/{id}', 'AddressController@upEdit');
        Route::get('view/{id}', 'AddressController@upView');
        Route::any('delete/{id}', 'AddressController@upDelete');
        Route::any('publish/{id}', 'AddressController@upIsactive');

    });

    // // ## Union Route
    Route::group(['prefix' => 'union'], function () {

        Route::any('/', 'AddressController@unionIndex')->name('unionDatatable');
        Route::any('add', 'AddressController@unionAdd');
        Route::any('edit/{id}', 'AddressController@unionEdit');
        Route::get('view/{id}', 'AddressController@unionView');
        Route::any('delete/{id}', 'AddressController@unionDelete');
        Route::any('publish/{id}', 'AddressController@unionIsactive');

    });

    // // ##  village Route
    Route::group(['prefix' => 'village'], function () {

        Route::any('/', 'AddressController@villageIndex')->name('VillageDatatable');
        Route::any('add', 'AddressController@villageAdd');
        Route::any('edit/{id}', 'AddressController@villageEdit');
        Route::get('view/{id}', 'AddressController@villageView');
        Route::any('delete/{id}', 'AddressController@villageDelete');
        Route::any('publish/{id}', 'AddressController@villageIsactive');

    });

    // // ## Area Route
    Route::group(['prefix' => 'area'], function () {

        Route::any('/', 'AreaController@index')->name('areaDatatable');
        Route::any('add', 'AreaController@add');
        Route::any('edit/{id}', 'AreaController@edit');
        Route::get('view/{id}', 'AreaController@view');
        Route::any('delete/{id}', 'AreaController@delete');
        Route::any('publish/{id}', 'AreaController@isActive');

        Route::get('ajaxAreaList', 'AreaController@ajaxAreaListLoad');

    });

    // // ##  Zone Route
    Route::group(['prefix' => 'zone'], function () {

        Route::any('/', 'ZoneController@index')->name('zoneDatatable');
        Route::any('add', 'ZoneController@add');
        Route::any('edit/{id}', 'ZoneController@edit');
        Route::get('view/{id}', 'ZoneController@view');
        Route::any('delete/{id}', 'ZoneController@delete');
        Route::any('publish/{id}', 'ZoneController@isActive');

        Route::get('ajaxZoneList', 'ZoneController@ajaxZoneListLoad');

    });

    // // ## Region Route
    Route::group(['prefix' => 'region'], function () {

        Route::any('/', 'RegionController@index');
        Route::any('add', 'RegionController@add');
        Route::any('edit/{id}', 'RegionController@edit');
        Route::get('view/{id}', 'RegionController@view');
        Route::any('delete/{id}', 'RegionController@delete');
        Route::any('publish/{id}', 'RegionController@isActive');

        Route::get('ajaxRegion', 'RegionController@ajaxRegionLoad');

    });

    // // ## Fiscal Year Route
    Route::group(['prefix' => 'fiscal_year'], function () {

        Route::any('/', 'FiscalYearController@index');
        Route::any('add', 'FiscalYearController@add');
        Route::any('edit/{id}', 'FiscalYearController@edit');
        Route::get('view/{id}', 'FiscalYearController@view');
        Route::any('delete/{id}', 'FiscalYearController@delete');
        Route::any('publish/{id}', 'FiscalYearController@isActive');

    });

    // // ## Govt Holiday Route
    Route::group(['prefix' => 'govtholiday'], function () {

        Route::any('/', 'GovtHolidayController@index');
        Route::any('add', 'GovtHolidayController@add');
        Route::any('edit/{id}', 'GovtHolidayController@edit');
        Route::get('view/{id}', 'GovtHolidayController@view');
        Route::any('delete/{id}', 'GovtHolidayController@delete');
        Route::any('publish/{id}', 'GovtHolidayController@isActive');

    });

    // // ## Company Holiday Route
    Route::group(['prefix' => 'compholiday'], function () {

        Route::any('/', 'CompHolidayController@index');
        Route::any('add', 'CompHolidayController@add');
        Route::any('edit/{id}', 'CompHolidayController@edit');
        Route::get('view/{id}', 'CompHolidayController@view');
        Route::any('delete/{id}', 'CompHolidayController@delete');
        Route::any('publish/{id}', 'CompHolidayController@isActive');
        Route::get('/CheckDayEnd', 'CompHolidayController@CheckDayEnd');

    });

    // // ## Special Holiday Route
    Route::group(['prefix' => 'specialholiday'], function () {

        Route::any('/', 'SpecialHolidayController@index')->name('specialholidayData');
        Route::any('add', 'SpecialHolidayController@add');
        Route::any('edit/{id}', 'SpecialHolidayController@edit');
        Route::get('view/{id}', 'SpecialHolidayController@view');
        Route::any('delete/{id}', 'SpecialHolidayController@delete');
        Route::any('publish/{id}', 'SpecialHolidayController@isActive');
        
        Route::get('/CheckDayEnd', 'SpecialHolidayController@CheckDayEnd');

    });
       // Terms and conditions Route
    Route::group(['prefix' => 'terms_conditions'], function () {

        Route::any('/', 'TermsConditionsController@index');
        Route::any('add', 'TermsConditionsController@add');
        Route::any('edit/{id}', 'TermsConditionsController@edit');
        Route::get('view/{id}', 'TermsConditionsController@view');
        Route::any('delete/{id}', 'TermsConditionsController@delete');
        Route::any('publish/{id}', 'TermsConditionsController@isActive');

    });
    // // ## System module Route
    Route::group(['prefix' => 'sys_module'], function () {

        Route::any('/', 'SysModuleController@index');
        Route::any('add', 'SysModuleController@add');
        Route::any('edit/{id}', 'SysModuleController@edit');
        // Route::get('view/{id}', 'SysModuleController@view');
        Route::any('delete/{id}', 'SysModuleController@delete');
        Route::any('publish/{id}', 'SysModuleController@isActive');
        Route::any('destroy/{id}', 'SysModuleController@destroy');

        // Route::get('active/{id}', 'SysModuleController@isActive');

    });

    // // ## system user Route
    Route::group(['prefix' => 'sys_user'], function () {

        Route::any('/', 'SysUserController@index');
        Route::any('add', 'SysUserController@add');
        Route::any('edit/{id}', 'SysUserController@edit');
        Route::get('view/{id}', 'SysUserController@view');
        Route::any('delete/{id}', 'SysUserController@delete');
        Route::any('publish/{id}', 'SysUserController@isActive');
        Route::any('destroy/{id}', 'SysUserController@destroy');

        Route::any('change_pass/{id}', 'SysUserController@changePassword');
    });

    // // ## System menus Route
    Route::group(['prefix' => 'sys_menu'], function () {

        Route::any('/', 'SysUserMenusController@index');
        Route::any('add', 'SysUserMenusController@add');
        Route::any('edit/{id}', 'SysUserMenusController@edit');
        Route::any('delete/{id}', 'SysUserMenusController@delete');
        Route::any('publish/{id}', 'SysUserMenusController@isActive');
        Route::any('destroy/{id}', 'SysUserMenusController@destroy');

    });

    // // ## system permission Route
    Route::group(['prefix' => 'sys_permission'], function () {

        Route::any('/{mid}', 'SysUserMenusController@indexPermission');
        Route::any('/{mid}/add', 'SysUserMenusController@addPermission');
        Route::any('/{mid}/edit/{id}', 'SysUserMenusController@editPermission');
        Route::any('/{mid}/delete/{id}', 'SysUserMenusController@deletePermission');
        Route::any('/{mid}/publish/{id}', 'SysUserMenusController@isActivePermission');
        Route::any('/{mid}/destroy/{id}', 'SysUserMenusController@destroyPermission');



    });

    // // ## System user role Route
    Route::group(['prefix' => 'sys_role'], function () {

        Route::any('/', 'SysUserRoleController@index');
        Route::any('add', 'SysUserRoleController@add');
        Route::any('edit/{id}', 'SysUserRoleController@edit');
        Route::any('delete/{id}', 'SysUserRoleController@delete');
        Route::any('publish/{id}', 'SysUserRoleController@isActive');
        Route::any('destroy/{id}', 'SysUserRoleController@destroy');

        // // ##    system permission assign
        Route::any('passign/{id}', 'SysUserRoleController@assignPermission');
    });

    // // ## Branch Db table route 
    Route::group(['prefix' => 'br_db'], function () {

        Route::any('/', 'BranchDBController@index');
        Route::any('add', 'BranchDBController@add');
        Route::any('edit/{id}', 'BranchDBController@edit');
        Route::any('delete/{id}', 'BranchDBController@delete');
        Route::any('publish/{id}', 'BranchDBController@isActive');

    });
     // // ## HO Db table route 
     Route::group(['prefix' => 'ho_db'], function () {

        Route::any('/', 'HODBController@index');
        Route::any('add', 'HODBController@add');
        Route::any('edit/{id}', 'HODBController@edit');
        Route::any('delete/{id}', 'HODBController@delete');
        Route::any('publish/{id}', 'HODBController@isActive');
    });

    // // ## HO Db table route 
    Route::group(['prefix' => 'ho_db_ignore'], function () {
        Route::any('/', 'HOIGController@index');
        Route::any('add', 'HOIGController@add');
        Route::any('edit/{id}', 'HOIGController@edit');
        Route::any('delete/{id}', 'HOIGController@delete');
        Route::any('publish/{id}', 'HOIGController@isActive');
    });

       // // ## Signature Setting
       Route::group(['prefix' => 'signature_set'], function () {
        Route::any('/', 'SignatureSettingsController@index');
        Route::get('/view/{wareaId}', 'SignatureSettingsController@view');
        Route::match(['get', 'post'], 'add', 'SignatureSettingsController@add');
        Route::match(['get', 'post'], '/edit/{id}', 'SignatureSettingsController@edit');
        Route::any('/publish/{id}', 'SignatureSettingsController@isActive');
        Route::any('/delete/{id}', 'SignatureSettingsController@delete');
    });


    //----------------------------- Employee------------------------------------>
    Route::group(['prefix' => 'employee'], function () {
        Route::any('/', 'EmployeeController@index')->name('employeeDatatable');
        Route::any('add', 'EmployeeController@add');
        Route::any('edit/{id}', 'EmployeeController@edit');
        Route::get('view/{id}', 'EmployeeController@view');
        Route::any('delete/{id}', 'EmployeeController@delete');
        Route::any('publish/{id}', 'EmployeeController@isActive');
        Route::any('destroy/{id}', 'EmployeeController@destroy');
    });

    // // ## Notice Route
    Route::group(['prefix' => 'notice'], function () {
        Route::any('/', 'NoticeController@index')->name('noticeDatatable');
        Route::any('add', 'NoticeController@add');
        Route::any('edit/{id}', 'NoticeController@edit');
        Route::get('view/{id}', 'NoticeController@view');
        Route::any('delete/{id}', 'NoticeController@delete');
        Route::any('publish/{id}', 'NoticeController@isActive');
        Route::any('destroy/{id}', 'NoticeController@destroy');
    });

    // // ## Department Route
    Route::group(['prefix' => 'department'], function () {

        Route::any('/', 'DepartmentController@index');
        Route::any('add', 'DepartmentController@add');
        Route::any('edit/{id}', 'DepartmentController@edit');
        Route::get('view/{id}', 'DepartmentController@view');
        Route::any('delete/{id}', 'DepartmentController@delete');
        Route::any('publish/{id}', 'DepartmentController@isActive');

    });

    // // ## Room Route
    Route::group(['prefix' => 'room'], function () {

        Route::any('/', 'RoomController@index');
        Route::any('add', 'RoomController@add');
        Route::any('edit/{id}', 'RoomController@edit');
        Route::get('view/{id}', 'RoomController@view');
        Route::any('delete/{id}', 'RoomController@delete');
        Route::any('publish/{id}', 'RoomController@isActive');

    });

    // // ## Designation Route
    Route::group(['prefix' => 'designation'], function () {

        Route::any('/', 'DesignationController@index');
        Route::any('add', 'DesignationController@add');
        Route::any('edit/{id}', 'DesignationController@edit');
        Route::get('view/{id}', 'DesignationController@view');
        Route::any('delete/{id}', 'DesignationController@delete');
        Route::any('publish/{id}', 'DesignationController@isActive');

    });


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // // // ## working with Supplier
    // Route::get('/supplier', 'SupplierController@index');
    // Route::post('/supplier/ajaxsupplierIndex', 'SupplierController@ajaxsupplierIndex')->name('supplierDatatable');
    // Route::get('/supplier/new', 'SupplierController@add');
    // Route::post('/supplier/storesupplier', 'SupplierController@add')->name('storesupplier');
    // Route::get('/supplier/edit/{id}', 'SupplierController@edit');
    // Route::post('/supplier/updatesupplier/{id}', 'SupplierController@edit');
    // Route::get('/supplier/view/{id}', 'SupplierController@view');
    // Route::get('/supplier/delete/{id}', 'SupplierController@delete');
    // Route::get('/supplier/publish/{id}', 'SupplierController@isactive');

    // // ## Routes
    Route::get('/routes', 'RouteOperationController@index');
    Route::post('/ajaxRoutesIndex', 'RouteOperationController@ajaxRoutesIndex');
    Route::get('/refreshRoutes', 'RouteOperationController@refreshRoutes');
});
