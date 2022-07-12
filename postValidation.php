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
            'href' => false,
            'title' => false,
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
                if (!in_array($tag, $tags)) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 0;
                $tags_stack[] = $tag;
                continue;
            }
            if ($l === ' ') {
                if (!in_array($tag, $tags)) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $trig = 11;
                $tags_stack[] = $tag;
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
/**
 * Атрибуты
 */
        if ($trig === 11) {
            if ($l === '=') {
                if (!array_key_exists($attr, $attrs_per_tags[$tag]) || $attrs_per_tags[$tag][$attr]) {
                    /** 
                     * Валидация не пройдена 
                     */
                    return false;
                }
                $attrs_per_tags[$tag][$attr] = true;
                $trig = 12;
                $attr = '';
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
                /** Устанавливается значение на повторяющихся пробелах */
                $trig = 11;
                continue;
            }
            if ($l === '>') {
                $trig = 0;
                foreach ($attrs_per_tags[$tag] as &$v) {
                    $v = false;
                }
            }
            continue;
        }

        $tag .= $l;
    }
    
    return !(bool)$tags_stack;
}


var_dump(postValidation('<code><i><tr></code>') === false);
var_dump(postValidation('<a href="something" title="something more">txt</a>') === true);
var_dump(postValidation('<a nohref="something" title="something more">txt</a>') === false);
var_dump(postValidation('123<i>123<strong>123</strong></i>123') === true);
var_dump(postValidation('<i>123<strong>123</strong>') === false);
var_dump(postValidation('<i>123<strong>123</i>') === false);
var_dump(postValidation('<i> test</i> text <code> ') === false);
var_dump(postValidation('<ii></ii>') === false);
var_dump(postValidation('<code>test</code>lala<i>New</i>strong man<b> next</b> test wrong tags') === false);
var_dump(postValidation('Text <code><i><strong>example</i></strong></code>') === false);
var_dump(postValidation('<a title="something" title="something more">txt</a>') === false);
