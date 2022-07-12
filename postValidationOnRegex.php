<?php

function postValidationOnRegex($text) {
    $tags = [
        'a',
        'code',
        'i',
        'strike',
        'strong',
    ];

    $attrs_per_tags = [
        'a' => [
            'href',
            'title',
        ],
    ];

    preg_match_all('/<([^>]*)>/', $text, $matches);
    $list_of_tags = $matches[1];
    $stack_of_tags = [];

    foreach ($list_of_tags as $tag) {
        if ($tag[0] === '/') {
            if (substr($tag, 1) !== array_pop($stack_of_tags)) {
                return false;
            }
        } else {
            $tag = preg_replace('/="([^"]*)"/' , '', $tag);
            preg_match('/^[a-z]+/', $tag, $matches);
            $tag_name = $matches[0];
            $tag = preg_replace('/^[a-z]*/', '', $tag);
            if (array_key_exists($tag_name, $attrs_per_tags)) {
                foreach ($attrs_per_tags[$tag_name] as $attr) {
                    preg_match_all("/\s\b$attr\b/", $tag, $matches);
                    if (count($matches[0]) > 1) {
                        return false;
                    }
                    $tag = preg_replace("/\s\b$attr\b/", '', $tag);
                }
            }
            if ($tag !== '') {
                return false;
            }
            if (!in_array($tag_name, $tags)) {
                return false;
            }
            $stack_of_tags[] = $tag_name;
        }
    }

    return !(bool)$stack_of_tags;
}

var_dump(postValidationOnRegex('<code><i><tr></code>') === false);
var_dump(postValidationOnRegex('<a href="something" title="something more">txt</a>') === true);
var_dump(postValidationOnRegex('<a nohref="something" title="something more">txt</a>') === false);
var_dump(postValidationOnRegex('123<i>123<strong>123</strong></i>123') === true);
var_dump(postValidationOnRegex('<i>123<strong>123</strong>') === false);
var_dump(postValidationOnRegex('<i>123<strong>123</i>') === false);
var_dump(postValidationOnRegex('<i> test</i> text <code> ') === false);
var_dump(postValidationOnRegex('<ii></ii>') === false);
var_dump(postValidationOnRegex('<code>test</code>lala<i>New</i>strong man<b> next</b> test wrong tags') === false);
var_dump(postValidationOnRegex('Text <code><i><strong>example</i></strong></code>') === false);
var_dump(postValidationOnRegex('<a title="something" title="something more">txt</a>') === false);
