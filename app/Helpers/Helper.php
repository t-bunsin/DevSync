<?php

if (!function_exists('setActive')) {
    function setActive($routes, $output = 'active') {
        foreach ((array) $routes as $route) {
            if (request()->is($route)) {
                return $output;
            }
        }
        return '';
    }
}
