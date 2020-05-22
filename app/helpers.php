<?php

if (!function_exists('defaultToNull')) {
    function defaultToNull($value)
    {
        return empty($value) ? null : $value;
    }
}
