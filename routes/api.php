<?php

Route::group(['namespace' => 'Api'], function () {
    //User Routes
    Route::post('user-sign-up', 'AppUserController@signUp');
    Route::post('user-verify-code', 'AppUserController@verifyCode');
    Route::post('user-login', 'AppUserController@login');
    Route::post('user-password-reset', 'AppUserController@resetPassword');
    Route::get('user-get', 'AppUserController@getUserData');
    Route::post('user-update-profile', 'AppUserController@updateProfile');
    Route::post('user-change-language', 'AppUserController@changeLanguage');
    Route::post('user-change-password', 'AppUserController@changePassword');
    Route::post('check-email', 'AppUserController@CheckEmailExist');
    Route::post('check-business', 'AppUserController@CheckBusinessExist');
    Route::post('forgot-password', 'AppUserController@forgotPassword');
    Route::post('resend-code', 'AppUserController@resendCode');
    Route::post('logout', 'AppUserController@signOut');
    Route::get('get-user', 'AppUserController@GetUserId');
    Route::get('get-user-info', 'AppUserController@GetUserInfo');

    Route::get('recorded-by', 'AppUserController@getUserRecordedBy');

    Route::post('create-user', 'AppUserController@createUser');
    Route::get('user-listing', 'AppUserController@UserListing');
    Route::get('user-disabled', 'AppUserController@UserAction');
    Route::delete('user-deleted', 'AppUserController@deleteUser');

    //Inventory Routes
    Route::post('inventories-add', 'InventoryController@store');
    Route::get('inventories-listing', 'InventoryController@inventory');
    Route::get('inventories-get', 'InventoryController@getInventory');
    Route::post('inventories-update', 'InventoryController@update');
    Route::delete('inventories-delete', 'InventoryController@delete');

    // Route::get('inventories-search', 'InventoryController@search');
    // Route::post('inventories-import', 'InventoryController@import');

    //Bank Reconciliation Routes
    Route::post('bank-reconciles-add', 'BankReconciliation@store');
    Route::get('bank-reconciles-listing', 'BankReconciliation@listing');
    Route::get('bank-reconciles-get', 'BankReconciliation@ReconciliationData');
    Route::post('bank-reconciles-update', 'BankReconciliation@update');
    Route::delete('bank-reconciles-delete', 'BankReconciliation@delete');
    Route::post('bank-reconciles-import', 'BankReconciliation@import');
    Route::get('get-cash-in-out', 'BankReconciliation@GetCashInOut');


    //User Subscription
    Route::post('subscriptions-add', 'UserSubscription@store');
    Route::get('user_subscriptions-get', 'UserSubscription@get');
    Route::get('check-user-expiry', 'UserSubscription@getUsersExpiry');
    Route::post('subscribe-user', 'SubscribedUsersController@subscribeUser');
    Route::post('reset_subscription', 'SubscribedUsersController@resetSubscription');

    //Subscriptions
    Route::get('subscriptions-get', 'SubscriptionController@AllSubscriptions');
    Route::post('cancel_subscription', 'SubscriptionController@CancelSubscription');
    // Route::get('cancel_subscription_get', 'SubscriptionController@CancelSubscriptionGet');

    //Owner Account Routes
    Route::post('owner-accounts-store', 'OwnerAccountController@store');
    Route::post('owner-accounts-update', 'OwnerAccountController@update');
    Route::get('owner-accounts-get', 'OwnerAccountController@get');
    Route::delete('owner-accounts-delete', 'OwnerAccountController@delete');
    Route::get('owner-accounts-listing', 'OwnerAccountController@listing');
    Route::post('owner-accounts-import', 'OwnerAccountController@import');
    Route::get('owner-account-schedule', 'OwnerAccountController@getSchedule');
    Route::get('get-owner-name', 'OwnerAccountController@OwnerName');



    //Sales Routes
    Route::post('sales-store', 'SaleController@store');
    Route::post('sales-update', 'SaleController@update');
    Route::get('sales-get', 'SaleController@get');
    Route::delete('sales-delete', 'SaleController@delete');
    Route::get('sales-listing', 'SaleController@listing');
    Route::get('sales-search', 'SaleController@search');
    Route::post('sales-import', 'SaleController@import');
    Route::get('account-receivable', 'SaleController@accountReceivable');
    Route::get('sale-customers', 'SaleController@saleCustomers');
    Route::get('account-receivable-schedule', 'SaleController@getSchedule');

    //Expense Routes
    Route::post('expenses-store', 'ExpenseController@store');
    Route::post('expenses-update', 'ExpenseController@update');
    Route::get('expenses-get', 'ExpenseController@get');
    Route::delete('expenses-delete', 'ExpenseController@delete');
    Route::get('expenses-listing', 'ExpenseController@listing');
    Route::get('expenses-sub-category-listing', 'ExpenseController@subCategoryListing');
    Route::get('fixed-category-listing', 'ExpenseController@fixedCategoryListing');
    Route::get('expenses-search', 'ExpenseController@search');
    Route::post('expenses-import', 'ExpenseController@import');
    Route::get('expenses-categories', 'ExpenseController@Categories');
    Route::post('add-expense-category','ExpenseController@addSubCategory');
    Route::delete('delete-expense-category','ExpenseController@deleteSubCategory');
    Route::get('listing-expense-category','ExpenseController@CategoryListing');


    Route::get('account-payable', 'AccountPayableController@listing');
    Route::get('account-payable-schedule', 'AccountPayableController@getSchedule');
    Route::get('get-vendor-name', 'AccountPayableController@getVendorName');

    Route::get('sys-gen-id', 'IdentityGeneratorController@getId');


    //Purchase Routes
    Route::post('capital-expenditure-store', 'PurchaseController@store');
    Route::post('capital-expenditure-update', 'PurchaseController@update');
    Route::get('capital-expenditure-get', 'PurchaseController@get');
    Route::delete('capital-expenditure-delete', 'PurchaseController@delete');
    Route::get('capital-expenditure-listing', 'PurchaseController@listing');
    Route::get('capital-expenditure-search', 'PurchaseController@search');
    Route::post('capital-expenditure-import', 'PurchaseController@import');

    //Status Routes
    Route::get('get-statuses', 'StatusController@get');

    //Type Routes
    Route::get('get-types', 'TypeController@get');

    //Activity Logs Routes
    Route::get('activity_log-listing', 'ActivityLogController@listing');

    Route::get('get-roles', 'UserRoleController@get');
    //Pending Routes
    Route::get('pending-listing', 'PendingController@listing');
    Route::post('pending', 'PendingController@store');
    Route::get('get-pending', 'PendingController@getPending');
    Route::delete('delete-pending', 'PendingController@deletePending');
    Route::post('update-pending', 'PendingController@update');

    //Industries
    Route::get('industries', 'IndustryController@listing');


    //subcategories
    Route::post('store-subcategory','SubCategoryController@addSubCategory');
    Route::delete('delete-subcategory','SubCategoryController@deleteSubCategory');
    Route::get('sub-cateogry-listing','SubCategoryController@subCategoryListing');

    //Aging Report
    Route::get('aging-report','AgingReportController@agingReport');
    Route::get('aging-report-payable','AgingReportController@agingReportPayable');
    //Countries
    Route::get('countries','CountryController@Country');

    //GRAPH VALUES / OVERVIEW API

    Route::get('overview-cash-in-out','OverviewController@CashInOut');
    Route::get('overview-sale-overview','OverviewController@SaleOverview');
    Route::get('overview-income-statement','OverviewController@CashBasisIncomeStatement');
    Route::get('dashboard-overview','OverviewController@DashboardOverview');

    // Route::get('dashboard-overview-demo','OverviewController@DashboardOverviewDemo');

    Route::post('customers-and-vendors', 'CustomerController@store');
    Route::get('listing-customers-and-vendors', 'CustomerController@list');
    Route::delete('delete-customers-and-vendors', 'CustomerController@delete');

    // Add Transaction
    Route::post('add-transaction', 'TransactionController@store');
    //Update Transaction
    Route::post('update-transaction', 'TransactionController@UpdateTransaction');
    Route::delete('delete-transaction', 'TransactionController@deleteTransaction');

    // Stock Count Transaction

    Route::post('add-stock-transaction', 'StockTransactionController@store');
    Route::post('update-stock-transaction', 'StockTransactionController@update');
    Route::delete('delete-stock-transaction', 'StockTransactionController@delete');
    Route::get('listing-stock-transaction', 'StockTransactionController@list');
    // Stock Logs

    Route::get('stock-logs', 'StockLogController@list');
    //Google Cloud OCR
    Route::post('google-ocr', 'OcrController@annotateImage');
    Route::get('child-sys-gen-id', 'IdentityGeneratorController@getChildId');
    Route::get('get-help-videos', 'HelpController@getHelpVideos');

    //FAQs
    Route::get('get-faqs', 'FaqController@list');
    Route::get('get-page', 'PageController@list');

    //Payment
    Route::post('charge', 'PaymentController@charge');
    Route::get('all_cards', 'PaymentController@getCardListing');
    Route::post('add_cards', 'PaymentController@addCard');
    Route::post('default_card', 'PaymentController@defaultCard');
    Route::delete('delete_cards', 'PaymentController@deleteCard');
    Route::get('payment_history', 'PaymentController@paymentHistory');
    Route::get('redirect_url', 'PaymentController@redirectUrl');
    Route::get('payment_url', 'PaymentController@paymentUrl');
});

//Recurring Payment
