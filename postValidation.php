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
    $codeTrig = false;
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
                    $codeTrig = true;
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
            }
            continue;
        }
        if ($trig === 4) {
            if ($l === '>') {
                if ($tag === 'code') {
                    $codeTrig = false;
                    $trig = 0;
                } else {
                    $trig = 3;
                    $tag = '';
                }
                continue;
            }
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
            continue;
        }

        $tag .= $l;
    }
    
    return !(bool)$tags_stack && !$codeTrig;
}


var_dump(postValidation('<code><i><tr></code>') === true);
var_dump(postValidation('<a href="something" title="something more">txt</a>') === true);
var_dump(postValidation('<a nohref="something" title="something more">txt</a>') === false);
var_dump(postValidation('123<i>123<strong>123</strong></i>123') === true);
var_dump(postValidation('<i>123<strong>123</strong>') === false);
var_dump(postValidation('<i>123<strong>123</i>') === false);
var_dump(postValidation('<i> test</i> text <code> ') === false);
var_dump(postValidation('<ii></ii>') === false);


/**
 * Это под вопросом
 */
// var_dump(postValidation('Text <code><i><strong>example</i></strong></code>') === false);
