<?php

if (!function_exists('shop_setting')) {
    function shop_setting($key, $default = null) {
        return \App\Http\Controllers\Admin\ShopSettingsController::getSetting($key, $default);
    }
}