<?php

use Symfony\Component\CssSelector\CssSelectorConverter;

if (!function_exists('defaultToNull')) {
    function defaultToNull($value)
    {
        return empty($value) ? null : $value;
    }
}

if (!function_exists('toXPath')) {
    function toXPath(string $cssSelector): string
    {
        return (new CssSelectorConverter())->toXPath($cssSelector);
    }
}

if (!function_exists('getNodeText')) {
    function getNodeText($node)
    {
        return $node->count() === 0 ? null : defaultToNull($node->text());
    }
}
