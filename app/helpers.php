<?php

use Illuminate\Support\Facades\Http;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DomCrawler\Crawler;

if (!function_exists('defaultToNull')) {
    function defaultToNull($value)
    {
        return empty($value) ? null : $value;
    }
}

if (!function_exists('filterXPath')) {
    function filterXPath(string $cssSelector, Crawler $crawler): Crawler
    {
        return $crawler->filterXPath(toXPath($cssSelector));
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

if (!function_exists('crawlUrl')) {
    function crawlUrl(string $url): Crawler
    {
        return new Crawler(Http::get($url)->body());
    }
}
