<?php

namespace App\Helpers;

use App\Models\User;

if (!function_exists('has_permission')) {
    function has_permission($permission)
    {
        if (auth()->check()) {
            return auth()->user()->hasPermission($permission);
        }
        return false;
    }
}
