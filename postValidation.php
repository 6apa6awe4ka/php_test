<?php

/**
 * Необходимо переосмыслить и переписать, пока так.
 */
function postValidation(string $text): bool {
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

    $trig = 0;
    $tag = '';
    $tags_stack = [];
    $attr = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $l = $text[$i];

        if ($trig === 0) {
            if ($l === '<') {
                $trig = 1;
                $tag = '';
                continue;
            }
        }
        if ($trig === 1) {
            if ($l === '/') {
                $trig = 2;
                continue;
            }
            if ($l === '>') {
                if ($tag === 'code') {
                    $trig = 3;
                    $tag = '';
                    continue;
                }
 /**
 * <<<mark1
 */
                if (!in_array($tag, $tags)) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 0;
                $tags_stack[] = $tag;
                continue;
/**
 * >>>mark1
 */
            }
            if ($l === ' ') {
                $trig = 11;
                continue;
            }
        }
        if ($trig === 2) {
            if ($l === '>') {
                $open_tag = array_pop($tags_stack);
                if ($open_tag !== $tag) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 0;
                continue;
            }
        }
        if ($trig === 3) {
            if ($l === '/' && $text[$i - 1] === '<') {
                $trig = 4;
                continue;
            }
        }
        if ($trig === 4) {
            if ($l === '>') {
                if ($tag === 'code') {
                    $trig = 0;
                    continue;
                }
            }
            $trig = 3;
            $tag = '';
            continue;
        }
/**
 * Атрибуты
 */
        if ($trig === 11) {
            if ($l === '=') {
                $attrs = $attrs_per_tags[$tag];
                if (!in_array($attr, $attrs)) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 12;
                continue;
            }
            $attr .= $l;
            continue;
        }
        if ($trig === 12) {
            if ($l !== '"') {
                /** 
                 * Валидация не пройдена 
                 */
                return false;
            }
            $trig = 13;
            continue;
        }
        if ($trig === 13) {
            if ($l === '"') {
                $trig = 14;
            }
            continue;
        }
        if ($trig === 14) {
            if ($l === ' ') {
                $attr = '';
                continue;
            }
            if ($l === '>') {
/**
 * <<<mark2
 * дубль mark1, пока не пойму
 */
                if (!in_array($tag, $tags)) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 0;
                $tags_stack[] = $tag;
                continue;
/**
 * >>>mark2
 */
            }
            continue;
        }

        $tag .= $l;
    }

    return !(bool)$tags_stack;
}


//true
$txt1 = '<code><i><tr></code>';
//true
$txt2 = '<a href="something" title="something more">txt</a>';
//false
$txt3 = '<a nohref="something" title="something more">txt</a>';
//true
$txt4 = '123<i>123<strong>123</strong></i>123';
//false
$txt5 = '<i>123<strong>123</strong>';
//false
$txt6 = '<i>123<strong>123</i>';

var_dump(postValidation($txt1) === true);
var_dump(postValidation($txt2) === true);
var_dump(postValidation($txt3) === false);
var_dump(postValidation($txt4) === true);
var_dump(postValidation($txt5) === false);
var_dump(postValidation($txt6) === false);
