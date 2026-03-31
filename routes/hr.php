<?php

Route::group(['middleware' => ['auth'], 'prefix' => 'hr', 'namespace' => 'HR'], function () {


    // Controllers Within The "App\Http\Controllers\Admin" Namespace
    Route::group(['namespace' => 'Others'], function () {

        // get address
        Route::post('/getDistricts', 'CommonController@getDistricts');
        Route::post('/getUpazilas', 'CommonController@getUpazilas');
        Route::post('/getUnions', 'CommonController@getUnions');
        Route::post('/getVillages', 'CommonController@getVillages');
    });


    Route::get('/', 'DashboardController@index');


        // Controllers Within The "App\Http\Controllers\Admin" Namespace

        Route::get('/', 'DashboardController@index');


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
            Route::get('CheckDayEnd', 'CompHolidayController@CheckDayEnd');
        });

        // // ## Special Holiday Route
        Route::group(['prefix' => 'specialholiday'], function () {

            Route::any('/', 'SpecialHolidayController@index')->name('specialholidayDatatable');
            Route::any('add', 'SpecialHolidayController@add');
            Route::any('edit/{id}', 'SpecialHolidayController@edit');
            Route::get('view/{id}', 'SpecialHolidayController@view');
            Route::any('delete/{id}', 'SpecialHolidayController@delete');
            Route::any('publish/{id}', 'SpecialHolidayController@isActive');
            Route::get('CheckDayEnd', 'SpecialHolidayController@CheckDayEnd');
        });

        //----------------------------- Employee------------------------------------>
        Route::group(['prefix' => 'employee'], function () {
            Route::any('/', 'EmployeeController@index')->name('employeeDatatableHR');
            Route::any('add', 'EmployeeController@add');
            Route::any('edit/{id}', 'EmployeeController@edit');
            Route::get('view/{id}', 'EmployeeController@view');
            Route::any('delete/{id}', 'EmployeeController@delete');
            Route::any('publish/{id}', 'EmployeeController@isActive');
            Route::any('destroy/{id}', 'EmployeeController@destroy');
        });


        // // ## Bank Name route
        Route::group(['prefix' => 'bank'], function () {
            Route::any('/', 'BankController@index');
            Route::get('/view/{id}', 'BankController@view');
            Route::match(['get', 'post'], 'add', 'BankController@add');
            Route::match(['get', 'post'], '/edit/{id}', 'BankController@edit');
            Route::post('/delete', 'BankController@delete');
        });
        // // ## Bank Name route
        Route::group(['prefix' => 'bank_branch'], function () {
            Route::any('/', 'BankBranchController@index');
            Route::get('/view/{id}', 'BankBranchController@view');
            Route::match(['get', 'post'], 'add', 'BankBranchController@add');
            Route::match(['get', 'post'], '/edit/{id}', 'BankBranchController@edit');
            Route::post('/delete', 'BankBranchController@delete');
        });

        //----------------------------- Govt Holoday------------------------------------>
        // Route::get('/govtholiday', 'GovtHolidayController@index');
        // Route::get('/govtholiday/add', 'GovtHolidayController@add');
        // Route::post('/govtholiday/storegholiday', 'GovtHolidayController@add')->name('storegholiday');
        // Route::get('/govtholiday/edit/{id}', 'GovtHolidayController@edit');
        // Route::post('/govtholiday/updategholiday/{id}', 'GovtHolidayController@edit');
        // Route::get('/govtholiday/view/{id}', 'GovtHolidayController@view');
        // Route::get('/govtholiday/delete/{id}', 'GovtHolidayController@delete');
        // Route::get('/govtholiday/destroy/{id}', 'GovtHolidayController@destroy');
        // Route::get('/govtholiday/publish/{id}', 'GovtHolidayController@isactive');
        //----------------------------- Govt Holoday End------------------------------------>

        //----------------------------- Company Holoday------------------------------------>
        // Route::get('/compholiday', 'CompHolidayController@index');
        // Route::get('/compholiday/add', 'CompHolidayController@add');
        // Route::post('/compholiday/storecompholiday', 'CompHolidayController@add')->name('storecompholiday');
        // Route::get('/compholiday/edit/{id}', 'CompHolidayController@edit');
        // Route::post('/compholiday/updatecompholiday/{id}', 'CompHolidayController@edit');
        // Route::get('/compholiday/view/{id}', 'CompHolidayController@view');
        // Route::get('/compholiday/delete/{id}', 'CompHolidayController@delete');
        // Route::get('/compholiday/destroy/{id}', 'CompHolidayController@destroy');
        // Route::get('/compholiday/publish/{id}', 'CompHolidayController@isactive');
        //----------------------------- Company Holoday End------------------------------------>

        //----------------------------- Special Holoday------------------------------------>
        // Route::get('/specialholiday', 'SpecialHolidayController@index');
        // Route::any('/specialholiday', 'SpecialHolidayController@index')->name('specialholidayDatatable');
        // Route::get('/specialholiday/add', 'SpecialHolidayController@add');
        // Route::post('/specialholiday/storespholiday', 'SpecialHolidayController@add')->name('storespholiday');
        // Route::get('/specialholiday/edit/{id}', 'SpecialHolidayController@edit');
        // Route::post('/specialholiday/updatespholiday/{id}', 'SpecialHolidayController@edit');
        // Route::get('/specialholiday/view/{id}', 'SpecialHolidayController@view');
        // Route::get('/specialholiday/delete/{id}', 'SpecialHolidayController@delete');
        // Route::get('/specialholiday/destroy/{id}', 'SpecialHolidayController@destroy');
        // Route::get('/specialholiday/publish/{id}', 'SpecialHolidayController@isactive');
        //----------------------------- Special Holoday End------------------------------------>
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

        // Employee Transfer routes
        Route::group(['prefix' => 'employeeTransfer'], function () {
            Route::any('/', 'EmployeeTransferController@index');
            Route::any('add', 'EmployeeTransferController@add');
            Route::any('edit/{id}', 'EmployeeTransferController@edit');
            Route::get('view/{id}', 'EmployeeTransferController@view');
            Route::any('delete/{id}', 'EmployeeTransferController@delete');
            Route::any('approve', 'EmployeeTransferController@approve');
            Route::any('getData', 'EmployeeTransferController@getData');
        });
});
