<?php
/**
 * Created by PhpStorm.
 * User: Nyi Nyi Lwin
 * Date: 9/21/18
 * Time: 11:27
 */

return [
    'resources' => [
        \App\User::class,
        \App\Models\BankAccount::class,
        \App\Models\Lease::class,
        \App\Models\Property::class,
        \App\Models\Tenant::class,
    ],
    'limit' => 10
];
