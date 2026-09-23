<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Company;
use App\Models\BankBranch;
use App\Models\BankDetails;


use App\Http\Controllers\bankbranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\Account_typeController;
use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ChartofAccountController;
use App\Http\Controllers\RepawningHistortyReportController;
use App\Http\Controllers\DailyCashReportController;
use App\Http\Controllers\CashInHandController;
use App\Http\Controllers\CustomerUpdatestatusController;
use App\Http\Controllers\stockNumberSearchController;
use App\Http\Controllers\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    $data = Company::all();
    return view('welcome')
    //Showing Customer Details in the Home page-23
    -> with("Company", $data);
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/get-customer-data', [App\Http\Controllers\HomeController::class, 'getCustomerData']);
Route::post('/feedback/store', [App\Http\Controllers\HomeController::class, 'FeedbackData'])->name('feedback.store');

Route::get('/view_users', [App\Http\Controllers\UserController::class, 'viewUsers'])->name('view_users');
Route::get('/delete_user/{id}', [App\Http\Controllers\UserController::class, 'deleteUsers'])->name('delete_user');
Route::get('/edit_user/{id}', [App\Http\Controllers\UserController::class, 'editUsers'])->name('edit_user');

// dashboard routes


Route::get('/users', [App\Http\Controllers\UserController::class, 'showUsers'])->name('users');
Route::get('/add_user', [App\Http\Controllers\UserController::class, 'showAddUser'])->name('add_user');
Route::post('/register_user', [App\Http\Controllers\UserController::class, 'AddUser'])->name('register_user');
Route::get('/roles', [App\Http\Controllers\UserController::class, 'showRoles'])->name('roles');

//delete user ajex
Route::post('/delete_user_ajax', [App\Http\Controllers\UserController::class, 'delete'])->name('delete_user_ajax');
//update_user_ajax
Route::post('/update_user_ajax', [App\Http\Controllers\UserController::class, 'update'])->name('update_user_ajax');
//search_user_ajax
Route::get('/search_user_ajax', [App\Http\Controllers\UserController::class, 'search'])->name('search_user_ajax');
// 2023.10.17
Route::get('/show_select_up_user_ajax', [App\Http\Controllers\UserController::class, 'getUser'])->name('show_select_up_user_ajax');
Route::get('/show_select_user_ajax', [App\Http\Controllers\UserController::class, 'getUser'])->name('show_select_user_ajax');


Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'showSuppliers'])->name('suppliers');
Route::get('/add_supplier', [App\Http\Controllers\SupplierController::class, 'showAddSuppliers'])->name('add_supplier');

Route::get('/pawning', [App\Http\Controllers\PawnController::class, 'showPawnReceipt'])->name('pawning');
Route::post('/storePawn', [App\Http\Controllers\PawnController::class, 'storePawn'])->name('storePawn');
Route::post('/storePawnSum', [App\Http\Controllers\PawnController::class, 'storePawnSum'])->name('storePawnSum');
//get customer ajax
Route::get('/get_customer_ajax', [App\Http\Controllers\PawnController::class, 'get'])->name('get_customer_ajax');
//search and get pawn ajax
Route::get('/search_pawn_ajax', [App\Http\Controllers\PawnController::class, 'search'])->name('search_pawn_ajax');
//get article history ajax in redeem form
Route::get('/view_article_details_ajax', [App\Http\Controllers\PawnController::class, 'getArticleDetails'])->name('view_article_details_ajax');
//get_customer_receipt_no_ajax
Route::get('/get_customer_receipt_no_ajax', [App\Http\Controllers\PawnController::class, 'getCustomerReceiptNo'])->name('get_customer_receipt_no_ajax');
//view_customer_history_details_ajax
Route::get('/view_customer_history_details_ajax', [App\Http\Controllers\PawnController::class, 'customerHistoryDetails'])->name('view_customer_history_details_ajax');
// print_receipt_ajax
Route::get('/print_receipt_ajax', [App\Http\Controllers\PawnController::class, 'printReceipt'])->name('print_receipt_ajax');
// show_select_category_item_ajax
Route::get('/show_select_category_item_ajax_select', [App\Http\Controllers\PawnController::class, 'selectCategory'])->name('show_select_category_item_ajax_select');
// delete pawn receipt
Route::get('/delete_pawn_receipt_ajax', [App\Http\Controllers\PawnController::class, 'deleteReceipt'])->name('delete_pawn_receipt_ajax');

Route::get('/Pawn_total_Value_Check', [App\Http\Controllers\PawnController::class, 'ValueCheck'])->name('Pawn_total_Value_Check');


//deleted pawn controller
Route::get('/deleted_pawn_report', [App\Http\Controllers\DeletePawnReportController::class,'index'])->name('deleted_pawn_report');



Route::get('/pawning_opening', [App\Http\Controllers\OpeningPawnController::class, 'index'])->name('pawning_opening');
// store Opening PawnSum
Route::post('/storeOpeningPawnSum', [App\Http\Controllers\OpeningPawnController::class, 'storePawnSum'])->name('storeOpeningPawnSum');
// print_opening_receipt_ajax
Route::get('/print_opening_receipt_ajax', [App\Http\Controllers\OpeningPawnController::class, 'printReceipt'])->name('print_opening_receipt_ajax');
//get_customer_opening_receipt_no_ajax
Route::get('/get_customer_opening_receipt_no_ajax', [App\Http\Controllers\OpeningPawnController::class, 'getCustomerReceiptNo'])->name('get_customer_opening_receipt_no_ajax');
//search and get opening pawn ajax
Route::get('/search_opening_pawn_ajax', [App\Http\Controllers\OpeningPawnController::class, 'search'])->name('search_opening_pawn_ajax');
// delete opening pawn receipt
Route::get('/delete_opening_pawn_receipt_ajax', [App\Http\Controllers\OpeningPawnController::class, 'deleteReceipt'])->name('delete_opening_pawn_receipt_ajax');
//edit_opening_receipt_ajax
Route::get('/edit_opening_receipt_ajax', [App\Http\Controllers\OpeningPawnController::class, 'edit'])->name('edit_opening_receipt_ajax');




// Opening Pawning Report
Route::get('/openingPawningReport', [App\Http\Controllers\OpeningpawningReportController::class, 'index'])->name('openingPawningReport');
Route::get('/openingpawingarticlereport', [App\Http\Controllers\OpeningpawningArticleReportController::class, 'index'])->name('openingpawingarticlereport');


Route::get('/pawning_make_payment', [App\Http\Controllers\MakePaymentController::class, 'index'])->name('pawning_make_payment');
// repawning
Route::get('/repawning', [App\Http\Controllers\RepawningController::class, 'index'])->name('repawning');




Route::get('/purchases_addPurchases', [App\Http\Controllers\PurchasesController::class, 'viewAddPurchases'])->name('purchases_addPurchases');
Route::get('/purchases_create', [App\Http\Controllers\PurchasesController::class, 'createPurchases'])->name('purchases_create');


Route::get('/stock_OP', [App\Http\Controllers\TransactionsController::class, 'indexOP'])->name('stock_OP');
Route::get('/stock_adjustment', [App\Http\Controllers\TransactionsController::class, 'indexStockAdjustment'])->name('stock_adjustment');
Route::get('/stock_damage', [App\Http\Controllers\TransactionsController::class, 'indexStockDamage'])->name('stock_damage');


Route::get('/transactions_GRN', [App\Http\Controllers\TransactionsController::class, 'indexGRN'])->name('transactions_GRN');


Route::get('/products_add', [App\Http\Controllers\ProductsController::class, 'indexAddProducts'])->name('products_add');

Route::get('/sales_add', [App\Http\Controllers\SalesController::class, 'indexAddSales'])->name('sales_add');


//Add Customer Route
Route::post('/addCustomer', [App\Http\Controllers\CustomerController::class, 'addCustomer'])->name('addCustomer');
//Edit View Customer Route
Route::get('/master_edit_customers/{id}', [App\Http\Controllers\CustomerController::class, 'indexEdit'])->name('master_edit_customers');
//Show Customers view
Route::get('/master_customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('master_customers');
Route::get('/customer-next-code', [App\Http\Controllers\CustomerController::class, 'nextCode'])->middleware('auth')->name('customer.next-code');
//Get Customer by Code
Route::post('/getCustomer', [App\Http\Controllers\CustomerController::class, 'getByID'])->name('getCustomer');
//Update Customer by Code
Route::post('/update_customer/{id}', [App\Http\Controllers\CustomerController::class, 'updateCustomer'])->name('update_customer');
//delte customer
Route::get('/delete_customer/{id}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('delete_customer');

//Add customer ajex
Route::post('/add_customer_ajax', [App\Http\Controllers\CustomerController::class, 'create'])->name('add_customer_ajax');
//delete customer ajex
Route::post('/delete_customer_ajax', [App\Http\Controllers\CustomerController::class, 'delete'])->name('delete_customer_ajax');
//update customer ajex
Route::post('/update_customer_ajax', [App\Http\Controllers\CustomerController::class, 'update'])->name('update_customer_ajax');
//pagination branch ajax
Route::get('/customer_pagination', [App\Http\Controllers\CustomerController::class, 'pagination']);
//search customer ajax
Route::get('/search_customer_ajax', [App\Http\Controllers\CustomerController::class, 'search'])->name('search_customer_ajax');





//search Customer by Code

// Route::post('/searchCustomer', [App\Http\Controllers\CustomerController::class, 'search'])->name('searchCustomer');
Route::post('/action', [App\Http\Controllers\CustomerController::class, 'action'])->name('action');
//Show receipts View
Route::get('/master_receipt', [App\Http\Controllers\ReceiptController ::class, 'index'])->name('master_receipt');
Route::get('/receipt-type-history', [App\Http\Controllers\ReceiptController::class, 'history'])->middleware('auth')->name('receipt_type_history');
Route::get('/blocked-receipts', [App\Http\Controllers\BlockedReceiptController::class, 'index'])->middleware('auth')->name('blocked.receipts.index');
Route::post('/blocked-receipts', [App\Http\Controllers\BlockedReceiptController::class, 'update'])->middleware('auth')->name('blocked.receipts.update');
//Add receipts Route
Route::post('/add_receipt', [App\Http\Controllers\ReceiptController::class, 'add_receipt'])->name('add_receipt');
//delete receipts
Route::get('/delete_receipt/{id}', [App\Http\Controllers\ReceiptController::class, 'destroy'])->name('delete_receipt');



//Add receipts ajax
Route::post('/add_receipt_ajax', [App\Http\Controllers\ReceiptController::class, 'create'])->name('add_receipt_ajax');
//delete receipts ajax
Route::post('/delete_receipt_ajax', [App\Http\Controllers\ReceiptController::class, 'delete'])->name('delete_receipt_ajax');
//update receipt ajax
Route::post('/update_receipt_ajax', [App\Http\Controllers\ReceiptController::class, 'update'])->name('update_receipt_ajax');




//index for redeem
Route::get('/pawning_redeem', [App\Http\Controllers\RedeemController::class, 'index'])->name('pawning_redeem');
// search_receipt_ajax
Route::get('/search_receipt_ajax', [App\Http\Controllers\RedeemController::class, 'search'])->name('search_receipt_ajax');
// store redeem
Route::post('/store_redeem', [App\Http\Controllers\RedeemController::class, 'store'])->name('store_redeem');
// search_invoice_ajax
Route::get('/search_invoice_ajax', [App\Http\Controllers\RedeemController::class, 'searchInvoice'])->name('search_invoice_ajax');
// search_ticket_ajax
Route::get('/search_ticket_ajax', [App\Http\Controllers\RedeemController::class, 'searchTicket'])->name('search_ticket_ajax');


// forfeit receipt index
Route::get('/pawning_forfeit_receipt', [App\Http\Controllers\ForfeitReceiptController ::class, 'index'])->name('pawning_forfeit_receipt');
// search_receipt_no_for_forfeit_ajax
Route::get('/search_receipt_for_forfeit_ajax', [App\Http\Controllers\ForfeitReceiptController::class, 'search'])->name('search_receipt_for_forfeit_ajax');
// search_invoice_no_for_forfeit_ajax
Route::get('/search_invoice_for_forfeit_ajax', [App\Http\Controllers\ForfeitReceiptController::class, 'searchInvoice'])->name('search_invoice_for_forfeit_ajax');
// store_forfeit
Route::post('/store_forfeit', [App\Http\Controllers\ForfeitReceiptController::class, 'store'])->name('store_forfeit');
//forfeit_report_index
Route::get('/forfeit_report', [App\Http\Controllers\ForfeitReportController::class, 'index'])->name('forfeit_report');



//show branch details
Route::get('/Company_branchdetails', [App\Http\Controllers\branchdetailsController ::class, 'index'])->name('branchdetails');
//add branch
Route::post('/add_branchdetails', [App\Http\Controllers\branchdetailsController::class, 'add_branch'])->name('add_branch');
//delete branch
Route::get('/delete_branch/{id}', [App\Http\Controllers\branchdetailsController::class, 'destroy'])->name('delete_branch');


//add branch ajax
Route::post('/addBranchdetails', [App\Http\Controllers\branchdetailsController::class, 'create'])->name('add_branch_ajax');
//update branch ajax
Route::post('/updateBranchdetails', [App\Http\Controllers\branchdetailsController::class, 'update'])->name('update_branch_ajax');
//delete branch ajax
Route::post('/deleteBranchdetails', [App\Http\Controllers\branchdetailsController::class, 'delete'])->name('delete_branch_ajax');
//search branch ajax
Route::get('/searchBranchdetails', [App\Http\Controllers\branchdetailsController::class, 'search'])->name('search_branch_ajax');
//pagination branch ajax
Route::get('/branch_pagination', [App\Http\Controllers\branchdetailsController::class, 'pagination']);


//show karatage
Route::get('master_karatage', [App\Http\Controllers\KaratagerateController::class, 'index'])->name('karatage');
//add karatage
Route::post('/add_karatage', [App\Http\Controllers\KaratagerateController::class, 'add_karatage'])->name('add_karatage');
//delete karatage
Route::get('/delete_karatage/{id}', [App\Http\Controllers\KaratagerateController::class, 'destroy'])->name('delete_karatage');

//add karatage ajax
Route::post('/add_karatage_ajax', [App\Http\Controllers\KaratagerateController::class, 'create'])->name('add_karatage_ajax');
//delete karatage ajax
Route::post('/delete_karatage_ajax', [App\Http\Controllers\KaratagerateController::class, 'delete'])->name('delete_karatage_ajax');
//update karatage ajax
Route::post('/update_karatage_ajax', [App\Http\Controllers\KaratagerateController::class, 'update'])->name('update_karatage_ajax');




//show item condition
Route::get('master_item_condition', [App\Http\Controllers\ItemConditionController ::class, 'index'])->name('master_item_condition');
//add item condition
Route::post('/add_itemcondition', [App\Http\Controllers\ItemConditionController::class, 'add_itemcondition'])->name('add_itemcondition');
//delete branch
Route::get('/delete_itemcondition/{id}', [App\Http\Controllers\ItemConditionController::class, 'destroy'])->name('delete_itemcondition');

//add item condition ajax
Route::post('/add_itemcondition_ajax', [App\Http\Controllers\ItemConditionController::class, 'create'])->name('add_itemcondition_ajax');
//update item condition ajax
Route::post('/update_condition_ajax', [App\Http\Controllers\ItemConditionController::class, 'update'])->name('update_condition_ajax');
//delete branch
Route::post('/delete_condition_ajax', [App\Http\Controllers\ItemConditionController::class, 'delete'])->name('delete_condition_ajax');



// Category routes
Route::get('master_category', [App\Http\Controllers\CategoryController::class, 'index'])->name('master_category');
Route::post('addCategory', [App\Http\Controllers\CategoryController::class, 'addCategory']);
Route::post('editCategory', [App\Http\Controllers\CategoryController::class, 'editCategory']);
Route::post('deleteCategory', [App\Http\Controllers\CategoryController::class, 'deleteCategory']);

// add category ajax
Route::post('add_category_ajax', [App\Http\Controllers\CategoryController::class, 'create'])->name('add_category_ajax');
// update category ajax
Route::post('update_category_ajax', [App\Http\Controllers\CategoryController::class, 'update'])->name('update_category_ajax');
// delete category ajax
Route::post('delete_category_ajax', [App\Http\Controllers\CategoryController::class, 'delete'])->name('delete_category_ajax');


Route::get('/search', [App\Http\Controllers\SearchController::class,'search'])->name('search');
Route::get('/redeem', [App\Http\Controllers\RedeemReportController::class,'redeem'])->name('redeem');
Route::get('/summary', [App\Http\Controllers\SummaryController::class,'summary'])->name('summary');
Route::get('/Cancel_Pawning', [App\Http\Controllers\Cancel_PawningController::class,'Cancel'])->name('Cancel_Pawning');
Route::get('/Advance_Payment', [App\Http\Controllers\Advance_PaymentController::class,'advance'])->name('Advance_Payment');
Route::get('/Advance_Payment_Balance', [App\Http\Controllers\Advance_Payment_BalanceController::class,'balance'])->name('Advance_Payment_Balance');

// 2023.08.21
// Company
Route::get('/Company', [App\Http\Controllers\CompanyController::class, 'index'])->name('Company');
Route::post('Companystore', [CompanyController::class, 'Companystore']);
Route::post('Companyedit', [CompanyController::class, 'Companyedit']);
Route::post('Companydelete', [CompanyController::class, 'Companydestroy']);

//Bank
Route::get('BankDeltails', [BankController::class, 'index'])->name('BankDeltails');
Route::post('store', [BankController::class, 'store']);
Route::post('edit', [BankController::class, 'edit']);
Route::post('delete', [BankController::class, 'destroy']);
Route::post('show', [BankController::class, 'show']);

//bankbranch
Route::get('Bank_Branch', [bankbranchController::class, 'index'])->name('Bank_Branch');
Route::post('mainstore', [bankbranchController::class, 'mainstore']);
Route::post('mainedit', [bankbranchController::class, 'mainedit']);
Route::post('maindelete', [bankbranchController::class, 'maindestroy']);



//Accont Category
Route::get('account_category', [AccountCategoryController::class, 'index'])->name('account_category');
Route::post('/add_Account_Category_ajax', [App\Http\Controllers\AccountCategoryController::class, 'createCateAccount'])->name('add_Account_Category_ajax');
Route::post('/update_Account_Category_ajax', [App\Http\Controllers\AccountCategoryController::class, 'updateCateAccount'])->name('update_Account_Category_ajax');
Route::post('/delete_Account_Category_ajax', [App\Http\Controllers\AccountCategoryController::class, 'deleteCateAccount'])->name('delete_Account_Category_ajax');

//Account Type
Route::get('account_type', [Account_typeController::class, 'index'])->name('account_type');
Route::post('add_Account_Type_ajax', [Account_typeController::class, 'createAccountype'])->name('add_Account_Type_ajax');
Route::post('update_Account_Type_ajax', [Account_typeController::class, 'updateAccountype'])->name('update_Account_Type_ajax');
Route::post('delete_Account_Type_ajax', [Account_typeController::class, 'deleteAccountype'])->name('delete_Account_Type_ajax');

// Chart Of Account
Route::get('chartofaccount', [ChartofAccountController::class, 'index'])->name('chartofaccount');
Route::post('ChartofAccount_store', [ChartofAccountController::class, 'ChartofAccountStore'])->name('ChartofAccount_store');
Route::post('ChartofAccount_edit', [ChartofAccountController::class, 'ChartofAccountEdit'])->name('ChartofAccount_edit');
Route::post('ChartofAccount_delete', [ChartofAccountController::class, 'ChartofAccountDelete'])->name('ChartofAccount_delete');

//stock report
Route::get('/journalEntry', [App\Http\Controllers\JournalEntryController::class, 'index'])->name('journalEntry');
Route::get('/sampleTestpage', [App\Http\Controllers\JournalEntryController::class, 'createjournalEntry'])->name('sampleTestpage');
Route::post('/check-code', [App\Http\Controllers\JournalEntryController::class, 'checkCode'])->name('check.code');


// PaymentVoucher
Route::get('/PaymentVoucher', [App\Http\Controllers\PaymentVoucherController::class, 'index'])->name('PaymentVoucher');
Route::post('/addPaymentVoucher', [App\Http\Controllers\PaymentVoucherController::class,'addPaymentVoucher'])->name('addPaymentVoucher');
Route::post('/UpdatePaymentVoucher', [App\Http\Controllers\PaymentVoucherController::class,'UpdatePaymentVoucher'])->name('UpdatePaymentVoucher');
Route::post('/DeletePaymentVoucher', [App\Http\Controllers\PaymentVoucherController::class,'DeletePaymentVoucher'])->name('DeletePaymentVoucher');
Route::get('/show_voucher_ajax',  [App\Http\Controllers\PaymentVoucherController::class, 'GetVoucher'])->name('show_voucher_ajax');
Route::get('/show_dr_voucher_ajax',  [App\Http\Controllers\PaymentVoucherController::class, 'GetDRVoucher'])->name('show_dr_voucher_ajax');

// PettyCash
Route::get('/PettyCash', [App\Http\Controllers\PettyCashController::class, 'index'])->name('PettyCash');


//daily report
Route::get('/daily_report', [App\Http\Controllers\DailyreportController::class,'index'])->name('daily_report');
Route::post('/save-data', [App\Http\Controllers\DailyreportController::class,'saveData'])->name('saveData');
//add_cash_in_hand
Route::post('/add_cash_in_hand', [App\Http\Controllers\DailyreportController::class, 'create'])->name('add_cash_in_hand');

// AddExpense
Route::get('/vouchers_add_expense', [App\Http\Controllers\ExpenseController::class, 'index'])->name('vouchers_add_expense');
Route::post('/add_expense', [App\Http\Controllers\ExpenseController::class, 'create'])->name('add_expense');

// pawing article report
Route::get('/pawingarticlereport', [App\Http\Controllers\PawingArticleReportController::class, 'index'])->name('pawingarticlereport');



Route::get('/gentralreceipt', [App\Http\Controllers\GentralReceiptController::class, 'index'])->name('gentralreceipt');
Route::post('/addGentralReceipt', [App\Http\Controllers\GentralReceiptController::class,'addGentralReceipt'])->name('addGentralReceipt');
Route::post('/UpdateGentralReceipt', [App\Http\Controllers\GentralReceiptController::class,'UpdateGentralReceipt'])->name('UpdateGentralReceipt');
Route::post('/DeleteGentralReceipt', [App\Http\Controllers\GentralReceiptController::class,'DeleteGentralReceipt'])->name('DeleteGentralReceipt');


// Stockreport
Route::get('/Stockreport', [App\Http\Controllers\StockreportController::class,'search'])->name('Stockreport');


Route::get('/search_repawning_receipt_ajax', [App\Http\Controllers\RepawningController::class, 'search'])->name('search_repawning_receipt_ajax');
Route::get('/search_repawning_ticket_ajax', [App\Http\Controllers\RepawningController::class, 'searchTicket'])->name('search_repawning_ticket_ajax');
Route::get('/search_repawning_invoice_ajax', [App\Http\Controllers\RepawningController::class, 'searchInvoice'])->name('search_repawning_invoice_ajax');

Route::get('/StockCheckreport', [App\Http\Controllers\StockDetailsCheckController::class, 'StockCheck'])->name('StockCheckreport');


Route::post('/update-checkbox', [App\Http\Controllers\StockDetailsCheckController::class, 'updateCheckbox']);

Route::post('/reset-checkbox-stock', [App\Http\Controllers\StockDetailsCheckController::class, 'resetCheckboxStock']);


// pawning_late_letters
Route::post('/late_redeem_list', [App\Http\Controllers\RedeemLateLettersController::class,'LateRedeem'])->name('late_redeem_list');

Route::get('/pawningPartPayment', [App\Http\Controllers\PawningPartPaymentController::class, 'index'])->name('pawningPartPayment');

Route::get('/search_part_payment_receipt_ajax', [App\Http\Controllers\PawningPartPaymentController::class, 'PartpaymentSearch'])->name('search_part_payment_receipt_ajax');
Route::get('/search_part_payment_ticket_ajax', [App\Http\Controllers\PawningPartPaymentController::class, 'PartpaymentTicketSearch'])->middleware('auth')->name('search_part_payment_ticket_ajax');
Route::get('/search_part_payment_invoice_ajax', [App\Http\Controllers\PawningPartPaymentController::class, 'PartpaymentInvoiceSearch'])->middleware('auth')->name('search_part_payment_invoice_ajax');

Route::post('/Store_part_payment', [App\Http\Controllers\PawningPartPaymentController::class, 'AddPartPayment'])->name('Store_part_payment');

Route::post('/Pawn_total_Value_Check_interest', [App\Http\Controllers\PawnController::class, 'InterestSave'])->name('Pawn_total_Value_Check_interest');



Route::get('/view_dynamicCusDetailsView_details_ajax', [App\Http\Controllers\PawnController::class, 'getCustomerDetails'])->name('view_dynamicCusDetailsView_details_ajax');



Route::get('/forfeitReceipt_List', [App\Http\Controllers\ForfeitReceiptController::class, 'ForfeitReceiptList'])->name('forfeitReceipt_List');


Route::get('/view_dynamicCusDetailsView_details_ajax', [App\Http\Controllers\PawnController::class, 'getCustomerDetails'])->name('view_dynamicCusDetailsView_details_ajax');


Route::post('/Store_RepawningSum', [App\Http\Controllers\RepawningController::class, 'StoreRepawningSum'])->name('Store_RepawningSum');



Route::get('/lateRedeemLetterList', [App\Http\Controllers\RedeemLateLettersController::class, 'LateRedeemLetterList'])->name('lateRedeemLetterList');


Route::get('/get-receipt-details', [App\Http\Controllers\ForfeitReceiptController::class, 'getReceiptDetails']);


Route::get('/forfeit_article_receipt', [App\Http\Controllers\ForfeitReportController::class, 'ForfeitArticleList'])->middleware('auth')->name('forfeit_article_receipt');

Route::post('/items/save-from-forfeit', [App\Http\Controllers\ForfeitReportController::class, 'storeFromForfeit'])->middleware('auth')->name('items.storeFromForfeit');
Route::get('/forfeit-articles/transfer-print', [App\Http\Controllers\ForfeitReportController::class, 'transferPrint'])->middleware('auth')->name('forfeit.articles.transfer.print');



Route::get('/createItem', [App\Http\Controllers\ItemCreateController::class, 'index'])->name('createItem');
Route::resource('forfeit-articles', App\Http\Controllers\ItemCreateController::class)->only(['show', 'update', 'destroy']);



Route::get('/salesInvoice', [App\Http\Controllers\InvoiceController::class, 'index'])->name('salesInvoice');
Route::post('/add_invoice', [App\Http\Controllers\InvoiceController::class, 'createInvoice'])->name('add_invoice');

// show_select_category_item_ajax
Route::get('/show_select_category_item_ajax', [App\Http\Controllers\InvoiceController::class, 'setItemsCode'])->name('show_select_category_item_ajax');
// show_select_item_description_ajax
Route::get('/show_select_item_description_ajax', [App\Http\Controllers\InvoiceController::class, 'setItemDescription'])->name('show_select_item_description_ajax');



Route::get('/salesInvoiceReport', [App\Http\Controllers\SalesController::class, 'index'])->name('salesInvoiceReport');




// Item setup
Route::get('/master_item_setup', [App\Http\Controllers\ItemSetupController::class, 'index'])->name('master_item_setup');
//generate item code
Route::get('/generate_item_code', [App\Http\Controllers\ItemSetupController::class, 'generateCode'])->name('generate_item_code');
//add item ajax
Route::post('/add_item_ajax', [App\Http\Controllers\ItemSetupController::class, 'create'])->name('add_item_ajax');
//delete item ajax
Route::post('/delete_item_ajax', [App\Http\Controllers\ItemSetupController::class, 'delete'])->name('delete_item_ajax');
//update item ajax
Route::post('/update_item_ajax', [App\Http\Controllers\ItemSetupController::class, 'update'])->name('update_item_ajax');


Route::get('/search-customer-autocomplete', [App\Http\Controllers\PawnController::class, 'searchCustomerAutocomplete'])->name('search_customer_autocomplete');


Route::get('/repawning-history', [RepawningHistortyReportController::class, 'index'])
    ->name('repawningHistoryReport');

Route::get('/partpayment-history', [RepawningHistortyReportController::class, 'create'])
    ->name('PartpaymentHistortyReport');
    
    
    
Route::post('ApprovalGentralReceipt', [App\Http\Controllers\GentralReceiptController::class, 'ApprovalGentralReceipt']);


Route::post('ApprovalPaymentVoucher', [App\Http\Controllers\PaymentVoucherController::class, 'ApprovalPaymentVoucher']);


Route::middleware(['auth'])->group(function () {

    // Daily Cash Account Report Routes
    Route::get('/daily-cash-report', [DailyCashReportController::class, 'index'])
        ->name('daily-cash-report.index');

    Route::get('/daily-cash-report/generate', [DailyCashReportController::class, 'generate'])
        ->name('daily-cash-report.generate');

    Route::get('/daily-cash-report/export', [DailyCashReportController::class, 'exportExcel'])
        ->name('daily-cash-report.export');

    // Daily Balance Management Routes
    Route::post('/daily-balance/close', [DailyCashReportController::class, 'closeDailyBalance'])
        ->name('daily-balance.close');

    Route::get('/daily-balance/get', [DailyCashReportController::class, 'getDailyBalance'])
        ->name('daily-balance.get');

    Route::post('/daily-balance/recalculate', [DailyCashReportController::class, 'recalculateBalances'])
        ->name('daily-balance.recalculate');
});



Route::get('customerUpdatestatus', [CustomerUpdatestatusController::class, 'customerUpdatestatus'])->name('customerUpdatestatus');
// Add these routes to your routes/web.php file

// Customer Status Management Routes
Route::get('/customer-update-status', [CustomerUpdatestatusController::class, 'customerUpdatestatus'])
    ->name('customer-update-status.view');

Route::put('/customer-update-status/{id}', [CustomerUpdatestatusController::class, 'updateStatus'])
    ->name('customerUpdatestatus.update');
    
    
    Route::get('/search.stock', [StockNumberSearchController::class, 'index']) ->name('search.stock');

Route::get('/stock-search', [StockNumberSearchController::class, 'index'])->name('stock.index');
Route::post('/stock-search', [StockNumberSearchController::class, 'SearchReceipt'])->name('stock.search');




Route::middleware(['auth'])->group(function () {
    // View profile page
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

    // Update own profile
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');

    // Admin routes
    Route::post('/profile/admin/store', [ProfileController::class, 'adminStore'])->name('profile.admin.store');
    Route::put('/profile/admin/update/{id}', [ProfileController::class, 'adminUpdate'])->name('profile.admin.update');
    Route::delete('/profile/admin/delete/{id}', [ProfileController::class, 'adminDelete'])->name('profile.admin.delete');
});


Route::get('/lateRedeemLetterList',        [App\Http\Controllers\RedeemLateLettersController::class, 'LateRedeemLetterList'])->middleware('auth')->name('lateRedeemLetterList');
Route::get('/pawning_late_letters',         [App\Http\Controllers\RedeemLateLettersController::class, 'index'])->middleware('auth')->name('pawning_late_letters');
Route::get('/print_late_redeem_list',       [App\Http\Controllers\RedeemLateLettersController::class, 'printIndex'])->middleware('auth')->name('print_late_redeem_list');
Route::post('/print_late_redeem_letter_ajax',[App\Http\Controllers\RedeemLateLettersController::class, 'print'])->middleware('auth')->name('print_late_redeem_letter_ajax');
Route::post('/late_redeem_list',            [App\Http\Controllers\RedeemLateLettersController::class, 'LateRedeem'])->middleware('auth')->name('late_redeem_list');
Route::get('/print-bulk-letters',      [App\Http\Controllers\RedeemLateLettersController::class, 'printBulkLettersView'])->middleware('auth')->name('print_bulk_letters_view');

// ✅ NEW ROUTE — add this line
Route::get('/late-redeem-letter-list', [App\Http\Controllers\RedeemLateLettersController::class, 'LateRedeemLetterList'])->middleware('auth')->name('late.redeem.letter.list');


Route::get('/print_letter_view',[App\Http\Controllers\RedeemLateLettersController::class, 'printLetterView'])->middleware('auth')->name('print_letter_view');






Route::get('/oldRedeemReceipt', [App\Http\Controllers\OldsystemRedeemController::class, 'index'])->name('oldRedeemReceipt');
// search_receipt_ajax
Route::get('/search_old_receipt_ajax', [App\Http\Controllers\OldsystemRedeemController::class, 'searchOld'])->name('search_old_receipt_ajax');

Route::get('/view_article_details_ajax_old', [App\Http\Controllers\OldsystemRedeemController::class, 'getArticleDetailsold'])->name('view_article_details_ajax_old');

Route::post('/store_redeem_old', [App\Http\Controllers\OldsystemRedeemController::class, 'StoreOld'])->name('store_redeem_old');

Route::get('/old_search_results', [App\Http\Controllers\OldsystemRedeemController::class, 'OldPawnReport'])->name('old_search_results');


Route::get('/view_dynamicCusDetailsView_details_ajax_partpayment', [App\Http\Controllers\PawningPartPaymentController::class, 'PartpaymentHistory'])->name('view_dynamicCusDetailsView_details_ajax_partpayment');

Route::middleware(['auth'])->group(function () {
    Route::get('/forfeit-reminders', [App\Http\Controllers\ForfeitReminderController::class, 'index'])
        ->name('forfeit.reminders.index');
    Route::post('/forfeit-reminders/promise', [App\Http\Controllers\ForfeitReminderController::class, 'storePromise'])
        ->name('forfeit.reminders.promise');
    Route::post('/forfeit-reminders/queue', [App\Http\Controllers\ForfeitReminderController::class, 'queue'])
        ->name('forfeit.reminders.queue');

    Route::get('/receipt-search', [App\Http\Controllers\ReceiptSearchController::class, 'index'])
        ->name('receipt.search');
    Route::get('/receipt-search/print', [App\Http\Controllers\ReceiptSearchController::class, 'print'])
        ->name('receipt.search.print');

    Route::post('/arrears-letters/issue', [App\Http\Controllers\RedeemLateLettersController::class, 'issue'])
        ->name('arrears.letters.issue');
    Route::post('/arrears-letters/issue-bulk', [App\Http\Controllers\RedeemLateLettersController::class, 'issueBulk'])
        ->name('arrears.letters.issue-bulk');
});
