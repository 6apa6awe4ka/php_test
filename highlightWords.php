<?php
require './vendor/autoload.php';

use Ds\Set;

/**
 * 
 * Не учтены знаки препинания и буквы {Е/Ё}.
 * Не учтены лишние пробелы.
 * Ну и прочая грамматика естественно не учтена, склонения и т. д..
 * 
 */
function highlightWords($text, $array_of_words) {
    $set_of_words = new Set();
    foreach ($array_of_words as $word) {
        $set_of_words->add(strtolower($word));
    }

    $result_text = '';
    $word = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $l = $text[$i];
        
        if ($l === ' ') {
            $result_text .= highlightWord($word, $set_of_words);
            $result_text .= ' ';
            $word = '';
            continue;
        }

        $word .= $l;
    }
    $result_text .= highlightWord($word, $set_of_words);

    return $result_text;
}

function highlightWord(string $word, Set $set_of_words) {
    if ($set_of_words->contains(strtolower($word))) {
        return "[$word]";
    }
    return  "$word";
}
