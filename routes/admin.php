<?php

//  Route::get('dump', function () {
//             dd($_SERVER);
//         // return view('auth.login');
//     });
//accessible for non authenticated users
Route::group(['middleware' => 'guest'], function () {

    Route::get('login', function () {
        return view('auth.login');
    })->name('login')->middleware('throttle:10,1');

       


    Route::get('forgot_password', function () {
        return view('auth.forgot_password');
    })->name('forgot_password');

    Route::post('recovery_mail', 'Panel\AdminController@password_resetmail')->name('password.email');

    Route::get('reset_password/{token}', 'Panel\AdminController@ShowPasswordResetForm')->name('password_reset');

    Route::post('reset_password', 'Panel\AdminController@update_password')->name('password.update');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', config('filesystems.PANEL_CONTROLLER_PATH') . 'PanelController@index')->name('home');
    Route::get('get-user-info', 'AppUserController@GetUserInfo');
    Route::get('change_password', config('filesystems.PANEL_CONTROLLER_PATH') . 'AdminController@changePassword')->name("change_password");
    // Route::get('get-videos', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@show');
    // Route::get('videos-add', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@add');
    // Route::post('videos-save', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@save')->name('videos.save');
    // Route::get('videos-ajaxListing', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@ajaxListing')->name('videos.ajaxListing');
    // Route::get('videos-edit', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@edit')->name('videos.edit');
    // Route::post('videos-post/{id}', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@update')->name('videos.update');
    // Route::get('videos-delete/{id}', config('filesystems.PANEL_CONTROLLER_PATH') . 'VideosController@delete')->name('videos.delete');
    // Route::get('get-pages', config('filesystems.PANEL_CONTROLLER_PATH') . 'PagesController@show');
    // Route::get('pages-ajaxListing', config('filesystems.PANEL_CONTROLLER_PATH') . 'PagesController@ajaxListing')->name('pages.ajaxListing');
    // Route::get('pages-edit', config('filesystems.PANEL_CONTROLLER_PATH') . 'PagesController@edit')->name('pages.edit');
    // Route::post('pages-update/{id}', config('filesystems.PANEL_CONTROLLER_PATH') . 'PagesController@update')->name('pages.update');
    // Route::get('get-faqs', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@show');
    // Route::get('faqs-add', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@add');
    // Route::post('faqs-save', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@save')->name('faqs.save');
    // Route::get('faqs-ajaxListing', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@ajaxListing')->name('faqs.ajaxListing');
    // Route::get('faqs-edit', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@edit')->name('faqs.edit');
    // Route::post('faqs-post/{id}', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@update')->name('faqs.update');
    // Route::get('faqs-delete/{id}', config('filesystems.PANEL_CONTROLLER_PATH') . 'FaqsController@delete')->name('faqs.delete');
    $crud_modules = [
            ['module_name' => 'videos', 'controller_name' => 'Videos',
                'additional_routes' => [
                    [
                        'route_name' => 'save',
                        'method' => 'save',
                        'url' => 'save',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'ajaxListing',
                        'method' => 'ajaxListing',
                        'url' => 'ajaxListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'update',
                        'method' => 'update',
                        'url' => 'videos-post/{id}',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'delete',
                        'method' => 'delete',
                        'url' => 'videos-delete/{id}',
                        'http_method' => 'get'
                    ],
            ]],
            ['module_name' => 'pages', 'controller_name' => 'Pages',
                'additional_routes' => [
                    [
                        'route_name' => 'save',
                        'method' => 'save',
                        'url' => 'save',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxListing',
                        'method' => 'ajaxListing',
                        'url' => 'ajaxListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'update',
                        'method' => 'update',
                        'url' => 'videos-post/{id}',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'delete',
                        'method' => 'delete',
                        'url' => 'videos-delete/{id}',
                        'http_method' => 'get'
                    ],
            ]],
            ['module_name' => 'faqs', 'controller_name' => 'Faqs',
                'additional_routes' => [
                    [
                        'route_name' => 'save',
                        'method' => 'save',
                        'url' => 'save',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'ajaxListing',
                        'method' => 'ajaxListing',
                        'url' => 'ajaxListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'update',
                        'method' => 'update',
                        'url' => 'videos-post/{id}',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'delete',
                        'method' => 'delete',
                        'url' => 'videos-delete/{id}',
                        'http_method' => 'get'
                    ],
            ]],
            ['module_name' => 'child_user', 'controller_name' => 'ChildUser',
                'additional_routes' => [
                    [
                        'route_name' => 'users',
                        'method' => 'users',
                        'url' => 'users',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxUserListing',
                        'method' => 'ajaxUserListing',
                        'url' => 'ajaxUserListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'child_user',
                        'method' => 'childUser',
                        'url' => 'child_user/{id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'cancel_user',
                        'method' => 'cancelUser',
                        'url' => 'cancel_user/{id}/{subscription_id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxChildListing',
                        'method' => 'ajaxChildListing',
                        'url' => 'ajaxChildListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'add_child',
                        'method' => 'add_child',
                        'url' => 'add_child/{id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'create_user',
                        'method' => 'createUser',
                        'url' => 'create_user',
                        'http_method' => 'post'
                    ]
                ]
            ],
            ['module_name' => 'app_users', 'controller_name' => 'AppUser',
                'additional_routes' => [
                    [
                        'route_name' => 'users',
                        'method' => 'users',
                        'url' => 'users',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxUserListing',
                        'method' => 'ajaxUserListing',
                        'url' => 'ajaxUserListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'child_user',
                        'method' => 'childUser',
                        'url' => 'child_user/{id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'cancel_user',
                        'method' => 'cancelUser',
                        'url' => 'cancel_user/{id}/{subscription_id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxChildListing',
                        'method' => 'ajaxChildListing',
                        'url' => 'ajaxChildListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'add_child',
                        'method' => 'add_child',
                        'url' => 'add_child/{id}',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'create_user',
                        'method' => 'createUser',
                        'url' => 'create_user',
                        'http_method' => 'post'
                    ],
                    [
                        'route_name' => 'reset_package',
                        'method' => 'resetPackage',
                        'url' => 'reset_package/{id}',
                        'http_method' => 'get'
                    ]
                ]
            ],
            ['module_name' => 'roles', 'controller_name' => 'Role'],
            ['module_name' => 'industries', 'controller_name' => 'Industry'],
            ['module_name' => 'cms', 'controller_name' => 'Cms',
                'additional_routes' => [
                    [
                        'route_name' => 'videos',
                        'method' => 'videos',
                        'url' => 'videos',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxUserListing',
                        'method' => 'ajaxUserListing',
                        'url' => 'ajaxUserListing',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'ajaxListingVideos',
                        'method' => 'ajaxListingVideos',
                        'url' => 'ajaxListingVideos',
                        'http_method' => 'get'
                    ],
                ]
            ],
            ['module_name' => 'user_role', 'controller_name' => 'UserRole',
                 'additional_routes' => [
                    [
                        'route_name' => 'ajaxChildListing',
                        'method' => 'ajaxChildListing',
                        'url' => 'ajaxChildListing',
                        'http_method' => 'get'
                    ],
                  ]
            ],
            ['module_name' => 'dashboard', 'controller_name' => 'Dashboard',
                'additional_routes' => [
                    [
                        'route_name' => 'new_subscriptions',
                        'method' => 'newSubscriptions',
                        'url' => 'new_subscriptions',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'consumer_retention_value',
                        'method' => 'consumerRetentionValue',
                        'url' => 'consumer_retention_value',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'subscriptions_renewal',
                        'method' => 'subscriptionsRenewal',
                        'url' => 'subscriptions_renewal',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'subscriptions_sold',
                        'method' => 'subscriptionsSold',
                        'url' => 'subscriptions_sold',
                        'http_method' => 'get'
                    ]
                ]
            ],

            ['module_name' => 'subscriptions', 'controller_name' => 'Subscription'],
            ['module_name' => 'admin', 'controller_name' => 'Admin',
                  'additional_routes' => [
                        [
                            'route_name' => 'change',
                            'method' => 'change',
                            'url' => 'change',
                            'http_method' => 'post'
                        ],
                   ]
            ],
            ['module_name' => 'revenue', 'controller_name' => 'Revenue',
                'additional_routes' => [
                    [
                        'route_name' => 'per_user',
                        'method' => 'perUser',
                        'url' => 'per_user',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'per_subscription',
                        'method' => 'perSubscription',
                        'url' => 'per_subscription',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'abandoned',
                        'method' => 'Abandoned',
                        'url' => 'abandoned',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'per_user_chart',
                        'method' => 'PerUserChart',
                        'url' => 'per_user_chart',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'subscription_chart',
                        'method' => 'SubscriptionChart',
                        'url' => 'subscription_chart',
                        'http_method' => 'get'
                    ],
                    [
                        'route_name' => 'abandoned_chart',
                        'method' => 'AbandonedChart',
                        'url' => 'abandoned_chart',
                        'http_method' => 'get'
                    ]
                ]],
            ['module_name' => 'reports', 'controller_name' => 'Report',
            'additional_routes' => [
                [
                    'route_name' => 'new_subscription',
                    'method' => 'newSubscription',
                    'url' => 'new_subscription',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'consumer_retention',
                    'method' => 'consumerRetention',
                    'url' => 'consumer_retention',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'subscriptions_due_for_renewal',
                    'method' => 'subcriptionDueforRenewal',
                    'url' => 'subscriptions_due_for_renewal',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'subscriptions_sold',
                    'method' => 'subscriptionsSold',
                    'url' => 'subscriptions_sold',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'per_user',
                    'method' => 'perUser',
                    'url' => 'per_user',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'per_subscription',
                    'method' => 'perSubscription',
                    'url' => 'per_subscription',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'abandoned',
                    'method' => 'Abandoned',
                    'url' => 'abandoned',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'new_subscrption_ajax',
                    'method' => 'newSubscriptions',
                    'url' => 'new_subscrption_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'consumer_retention_value_ajax',
                    'method' => 'consumerRetentionValue',
                    'url' => 'consumer_retention_value_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'subscriptions_renewal_ajax',
                    'method' => 'subscriptionsRenewal',
                    'url' => 'subscriptions_renewal_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'subscriptions_sold_ajax',
                    'method' => 'subscriptionsSoldData',
                    'url' => 'subscriptions_sold_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'per_user_ajax',
                    'method' => 'perUserData',
                    'url' => 'per_user_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'per_subscription_ajax',
                    'method' => 'perSubscriptionData',
                    'url' => 'per_subscription_ajax',
                    'http_method' => 'get'
                ],
                [
                    'route_name' => 'abandoned_ajax',
                    'method' => 'AbandonedData',
                    'url' => 'abandoned_ajax',
                    'http_method' => 'get'
                ]
            ]],
        ];
    makeRoute($crud_modules);
});


function makeRoute($crud_modules)
{
// dd($crud_modules);
    foreach ($crud_modules as $module) {


        $controller = config('filesystems.PANEL_CONTROLLER_PATH') . $module['controller_name'] . 'Controller';

        Route::get($module['module_name'], $controller . '@show')->name($module['module_name'] . '.show');

        Route::get($module['module_name'] . '-add', $controller . '@add')->name($module['module_name'] . '.add');

        Route::post($module['module_name'] . '-add', $controller . '@store')->name($module['module_name'] . '.add');

        Route::get($module['module_name'] . '-edit/{id}', $controller . '@edit')->name($module['module_name'] . '.edit');

        Route::post($module['module_name'] . '-edit/{id}', $controller . '@update')->name($module['module_name'] . '.edit');

        Route::get($module['module_name'] . '-delete/{id}', $controller . '@delete')->name($module['module_name'] . '.delete');

        Route::get($module['module_name'] . '-view/{id}', $controller . '@view')->name($module['module_name'] . '.view');

        Route::get($module['module_name'] . '-list', $controller . '@ajaxListing')->name($module['module_name'] . '.ajaxListing');

        if (!empty($module['additional_routes'])) {
            foreach ($module['additional_routes'] as $additional_route) {
                Route::match([$additional_route['http_method']],
                    $module['module_name'] . '-' . $additional_route['url'], $controller . '@' . $additional_route['method'])
                    ->name($module['module_name'] . '.' . $additional_route['route_name']);
            }
        }
    }
}
