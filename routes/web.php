<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/laravel-filemanager', '\UniSharp\LaravelFilemanager\Controllers\LfmController@show');
    Route::post('/laravel-filemanager/upload', '\UniSharp\LaravelFilemanager\Controllers\UploadController@upload');
    // list all lfm routes here...
});


Route::get('/', 'HomeController@index');
Route::get('/dashboard', 'HomeController@getDashboardData');
Route::get('/dashboard/unit', 'HomeController@getUnitData');
Route::get('/dashboard/reminder', 'HomeController@getReminderData');

Auth::routes();

Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/home', 'HomeController@index');

Route::resource('users','UserController');
Route::get('/users_data/json', 'UserController@json');

Route::resource('regions','RegionController');
Route::get('/regions_data/json', 'RegionController@json');

Route::resource('units','UnitController');
Route::get('/units_data/json', 'UnitController@json');

Route::resource('suppliers','SupplierController');
Route::get('/suppliers_data/json', 'SupplierController@json');

Route::resource('registers','RegisterController');
Route::get('/registers_data/json', 'RegisterController@json');

Route::resource('vendings','VendingController');
Route::get('/vendings_data/json', 'VendingController@json');

Route::resource('netexts','NetExtController');
Route::get('/netexts_data/json', 'NetExtController@json');
Route::get('/netexts_data/cash_credit_purch', 'NetExtController@cashCreditPurch');

Route::resource('taxcodes','TaxCodeController');
Route::get('/taxcodes_data/json', 'TaxCodeController@json');
Route::get('/taxcodes_data/apply', 'TaxCodeController@applyTaxCodes');
Route::get('/taxcodes_data/net-ext-settings', 'TaxCodeController@netExtSettings');
Route::post('/taxcodes_data/net-ext-settings/save', 'TaxCodeController@saveNetExtSettings');

Route::get('sheets/unit/close-check', 'SheetController@checkUnitClose');

Route::get('sheets/get-tax-codes-by-currency','SheetController@getTaxCodesByCurrency');
Route::get('sheets/purchases/{purch_type}/{sheet_id?}','SheetController@purchase');
Route::post('sheets/purchases/{purch_type}','SheetController@purchase');
Route::post('sheets/purchases/{purch_type}/confirmation','SheetController@purchaseConfirmation');
Route::post('sheets/purchases/{purch_type}/post','SheetController@purchasePost');
Route::get('/unit_suppliers/json', 'SheetController@suppliersJson');
Route::get('/supplier_invoice_no_unique/json', 'SheetController@supplierInvoiceNoUnique');
Route::get('/unit_close_check/json', 'SheetController@unitCloseCheck');
Route::get('/supplier_currency/json', 'SheetController@supplierCurrency');
Route::get('/register_currency/json', 'SheetController@registerCurrency');
Route::get('/unit_currency/json', 'SheetController@unitCurrency');
Route::get('/machine_currency/json', 'SheetController@machineCurrency');
Route::get('/exchange_amount/json', 'SheetController@exchangeAmount');


Route::get('sheets/credit-sales/{id?}','SheetController@creditSales');
Route::post('sheets/credit-sales','SheetController@creditSales');
Route::post('sheets/credit-sales/confirmation','SheetController@creditSalesConfirmation');
Route::post('sheets/credit-sales/post','SheetController@creditSalesPost');

Route::get('sheets/cash-sales/{id?}','SheetController@cashSales');
Route::post('sheets/cash-sales','SheetController@cashSales');
Route::get('/reg_number_cash_purchases_credit_sales/json', 'SheetController@regNumberCashPurchasesCreditSales');
Route::post('sheets/cash-sales/confirmation','SheetController@cashSalesConfimation');
Route::post('sheets/cash-sales/post','SheetController@cashSalesPost');

Route::get('sheets/vending-sales/{id?}','SheetController@vendingSales');
Route::get('/machine_names/json', 'SheetController@machineNameJson');
Route::get('/closing_reading/json', 'SheetController@closingReadingJson');
Route::post('sheets/vending-sales','SheetController@vendingSales');
Route::post('sheets/vending-sales/confirmation','SheetController@vendingSalesConfirmation');
Route::post('sheets/vending-sales/post','SheetController@vendingSalesPost');

Route::get('sheets/phased-budget','SheetController@phasedBudget');
Route::post('sheets/phased-budget','SheetController@phasedBudget');
Route::get('/change_log/json', 'SheetController@changeLogJson');
Route::post('sheets/phased-budget/confirmation','SheetController@phasedBudgetConfimation');
Route::post('sheets/phased-budget/post','SheetController@phasedBudgetPost');
Route::post('sheets/phased-budget/toggle-row-visibility','SheetController@toggleRowVisibility');


Route::get('sheets/labour-hours/{sheet_id?}','SheetController@labourHours');
Route::post('sheets/labour-hours','SheetController@labourHours');
Route::get('/labour_hours_remaining/json', 'SheetController@labourHoursRemainingJson');
Route::post('sheets/labour-hours/confirmation','SheetController@labourHoursConfimation');
Route::post('sheets/labour-hours/post','SheetController@labourHoursPost');

Route::get('sheets/stock-control','SheetController@stockControl');
Route::post('sheets/stock-control','SheetController@stockControl');
Route::get('/previous_stock/json', 'SheetController@previousStockJson');
Route::post('sheets/stock-control/confirmation','SheetController@stockControlConfimation');
Route::post('sheets/stock-control/post','SheetController@stockControlPost');

Route::get('sheets/problem-report/{id?}','SheetController@problemReport');
Route::post('sheets/problem-report','SheetController@problemReport');
Route::post('sheets/problem-report/confirmation','SheetController@problemReportConfimation');
Route::post('sheets/problem-report/post','SheetController@problemReportPost');
Route::get('/sheets/filesjson', 'SheetController@problemReportJson');
Route::get('/sheets/problem-report/filespost', 'SheetController@fileProblemReportPost');
Route::get('/sheets/ravindra', 'SheetController@fileRavindra');

Route::get('sheets/operations-scorecard','SheetController@operationsScorecard');
Route::post('sheets/operations-scorecard','SheetController@operationsScorecard');
Route::post('sheets/operations-scorecard/confirmation','SheetController@operationsScorecardConfirmation');
Route::post('sheets/operations-scorecard/post','SheetController@operationsScorecardPost');
Route::get('sheets/operations-scorecard/edit/{opsScorecardId}','SheetController@operationsScorecardEdit');
Route::post('sheets/operations-scorecard/save','SheetController@operationsScorecardSave');

Route::get('sheets/customer-feedback','SheetController@customerFeedback');
Route::post('sheets/customer-feedback','SheetController@customerFeedback');
Route::post('sheets/customer-feedback/confirmation','SheetController@customerFeedbackConfirmation');
Route::post('sheets/customer-feedback/post','SheetController@customerFeedbackPost');

Route::get('sheets/lodgements/{id?}','SheetController@lodgements');
Route::post('sheets/lodgements','SheetController@lodgements');
Route::post('sheets/lodgements/confirmation','SheetController@lodgementsConfirmation');
Route::post('sheets/lodgements/post','SheetController@lodgementsPost');
Route::get('/lodgement_sales/json','SheetController@lodgementSales');


Route::get('sheets/scorecard/info','SheetController@scorecardInfo');
Route::get('sheets/unit/info','SheetController@unitInfo');

Route::post('/report/column_visibility', 'ReportController@columnVisibility');
Route::post('/report/column_visibility/toggle', 'ReportController@toggleColumnVisibility');

Route::get('reports/labour-hours','ReportController@labourHours');
Route::post('reports/labour-hours/grid','ReportController@labourHoursGrid');
Route::get('/labour-hours/json', 'ReportController@labourHoursGridJson');
Route::get('/labour-hours/delete/{id}', 'ReportController@deleteRecord');

Route::get('reports/purchases','ReportController@purchases');
Route::post('reports/purchases/grid','ReportController@purchasesGrid');
Route::get('reports/purchases/grid/{sheet_id}/{unit_id}/{from_date}/{to_date}','ReportController@purchasesGrid');
Route::get('/purchases/json', 'ReportController@purchasesGridJson');
Route::get('/purchases/delete/{id}', 'ReportController@deletePurchasesRecord');
Route::get('/purchases/sheetdelete/{id}', 'ReportController@deletePurchasesSheetRecord');

Route::get('reports/sales-summary','ReportController@salesSummary');
Route::post('reports/sales-summary/grid','ReportController@salesSummaryGrid');
Route::get('/sales-summary/json', 'ReportController@salesSummaryGridJson');

Route::get('reports/cash-sales','ReportController@cashSales');
Route::post('reports/cash-sales/grid','ReportController@cashSalesGrid');
Route::get('/cash-sales/json', 'ReportController@cashSalesGridJson');

Route::get('/cash-sales/delete/{id}', 'ReportController@deleteCashSalesRecord');

Route::get('reports/credit-sales','ReportController@creditSales');
Route::post('reports/credit-sales/grid','ReportController@creditSalesGrid');
Route::get('reports/credit-sales/grid/{sheet_id}/{unit_id}/{from_date}/{to_date}/{all_records}/{visible}','ReportController@creditSalesGrid');
Route::get('/credit-sales/json', 'ReportController@creditSalesGridJson');
Route::get('/credit-sales/delete/{id}', 'ReportController@deletecreditSalesRecord');

Route::get('reports/vending-sales','ReportController@vendingSales');
Route::post('reports/vending-sales/grid','ReportController@vendingSalesGrid');
Route::get('reports/vending-sales/grid/{sheet_id}/{unit_id}/{from_date}/{to_date}/{all_records}/{visible}','ReportController@vendingSalesGrid');
Route::get('/vending-sales/json', 'ReportController@vendingSalesGridJson');
Route::get('/vending-machines/json', 'ReportController@vendingMachinesJson');
Route::get('/vending-sales/delete/{id}', 'ReportController@deletevendingSalesRecord');

Route::get('reports/stock-control','ReportController@stockControl');
Route::post('reports/stock-control/grid','ReportController@stockControlGrid');
Route::get('/stock-control/json', 'ReportController@stockControlGridJson');
Route::get('/stock-control/delete/{id}', 'ReportController@deletestockControlRecord');

Route::get('reports/problem-report','ReportController@problemReport');
Route::post('reports/problem-report/grid','ReportController@problemReportGrid');
Route::get('/problem-report/json', 'ReportController@problemReportGridJson');
Route::get('/problem-report/delete/{id}', 'ReportController@deleteproblemReportRecord');

Route::get('reports/purchases-summary','ReportController@purchasesSummary');
Route::post('reports/purchases-summary/grid','ReportController@purchasesSummaryGrid');
Route::post('reports/purchases-summary/export-to-csv','ReportController@exportToCSV');

Route::get('reports/unit-trading-account','ReportController@unitTradingAccount');
Route::post('reports/unit-trading-account','ReportController@postUnitTradingAccount');

Route::get('reports/unit-trading-account-stock','ReportController@unitTradingAccountStock');
Route::post('reports/unit-trading-account-stock','ReportController@postUnitTradingAccountStock');

Route::get('reports/client-feedback','ReportController@clientFeedback');
Route::post('reports/client-feedback/grid','ReportController@clientFeedbackGrid');
Route::get('reports/client-feedback/json','ReportController@clientFeedbackGridJson');

Route::get('reports/operations-scorecard','ReportController@operationsScorecard');
Route::post('reports/operations-scorecard/grid','ReportController@operationsScorecardGrid');

Route::get('reports/lodgements','ReportController@lodgements');
Route::post('reports/lodgements/grid','ReportController@lodgementsGrid');
Route::get('/lodgements/json', 'ReportController@lodgementsGridJson');
Route::get('/lodgements/delete/{id}', 'ReportController@deleteLodgementsRecord');

Route::get('accounts/statement-check','AccountsController@statementCheck');
Route::post('accounts/statement-check','AccountsController@statementCheck');
Route::get('/supplier/json', 'AccountsController@supplierJson');

Route::get('accounts/bsi-report','AccountsController@bsiReport');
Route::post('accounts/bsi-report','AccountsController@bsiReport');
Route::get('accounts/bsi-report-grid','AccountsController@bsiReportGrid');
Route::get('/bsi-report/json', 'AccountsController@bsiReportGridJson');

Route::get('accounts/sage-confirm','AccountsController@sageConfirm');
Route::post('accounts/sage-confirm','AccountsController@sageConfirm');
Route::get('accounts/sage-confirm-grid','AccountsController@sageConfirmGrid');
Route::get('/sage-confirm/json', 'AccountsController@sageConfirmGridJson');

Route::get('accounts/unit-month-end-closing','AccountsController@unitMonthEndClosing');
Route::post('accounts/unit-month-end-closing','AccountsController@unitMonthEndClosing');
Route::get('/purchases-sales-unit-close/json', 'AccountsController@purchasesSalesUnitCloseJson');
Route::post('accounts/unit-month-end-closing/confirmation','AccountsController@unitMonthEndClosingConfimation');
Route::post('accounts/unit-month-end-closing/post','AccountsController@unitMonthEndClosingPost');

Route::get('files/{dir_id?}','FileController@index');
Route::post('files/create-dir/{dir_id?}','FileController@createDir');
Route::post('files/upload-file/','FileController@uploadFile');
Route::get('files/delete-file/{dir_id}/{file_id}','FileController@deleteFile');
Route::get('files/delete-directory/{dir_id}/{dir_to_del_id}','FileController@deleteDirectory');
Route::get('files/download-directory/{dir_id}/{dir_to_download_id}','FileController@downloadDirectory');
Route::post('files/open-download-file', 'FileController@openDownloadFile');
Route::post('files/move-file', 'FileController@moveFile');
Route::post('files/move-directory', 'FileController@moveDirectory');
Route::post('files/permissions-ajax', 'FileController@permissionsAjax');
Route::post('files/set-file-permission', 'FileController@setFilePermission');
Route::post('files/set-directory-permission', 'FileController@setDirectoryPermission');
Route::post('files/add-user-group', 'FileController@addUserGroup');
Route::post('files/add-file-user-group', 'FileController@addFileUserGroup');

Route::get('/events', 'EventController@index');
Route::get('/events/json', 'EventController@json');

/* ==================== Currency ==================== */

Route::resource('currencies','CurrencyController');
Route::post('/currencies/default', 'CurrencyController@setDefault');
Route::get('/currencies_data/json', 'CurrencyController@json');
Route::get('/currency_data/json', 'CurrencyController@find');

/* ==================== Exchange rate ==================== */

Route::resource('exchange-rates','ExchangeRateController');
Route::get('/exchange_rate_data/json', 'ExchangeRateController@json');

Route::get('/profile-settings', 'ProfileSettingsController@index');
Route::get('/profile-settings/user-menu/', 'ProfileSettingsController@getMenuLinks');
Route::post('/profile-settings/user-menu/add', 'ProfileSettingsController@addLink');
Route::post('/profile-settings/user-menu/delete', 'ProfileSettingsController@deleteLink');
Route::post('/profile-settings/user-menu/change-position', 'ProfileSettingsController@changeLinkPosition');
Route::post('/profile-settings/toggle-sidebar', 'ProfileSettingsController@toggleSidebar');
