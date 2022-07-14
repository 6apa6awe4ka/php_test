
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
function highlightWords(string $text, array $array_of_words): string {
    $set_of_words = new Set();
    $set_of_highlighted_words = new Set();
    foreach ($array_of_words as $word) {
        $set_of_words->add(mb_strtoupper($word, 'UTF-8'));
    }

    $result_text = '';
    $word = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $l = $text[$i];
        
        if ($l === ' ') {
            $result_text .= highlightWord($word, $set_of_words, $set_of_highlighted_words);
            $result_text .= ' ';
            $word = '';
            continue;
        }

        $word .= $l;
    }
    $result_text .= highlightWord($word, $set_of_words, $set_of_highlighted_words);

    return $result_text;
}

function highlightWord(string $word, Set $set_of_words, Set $set_of_highlighted_words) {
    $lowercased_word = mb_strtoupper($word, 'UTF-8');
    if (
        !$set_of_highlighted_words->contains($lowercased_word) && 
        $set_of_words->contains($lowercased_word)
    ) {
        $set_of_highlighted_words->add($lowercased_word);
        return "[$word]";
    }
    return  "$word";
}

var_dump(
    highlightWords(
        'Mama mYla ramu mama',
        [
            'mama', 'Ramu',
        ]
    ) === '[Mama] mYla [ramu] mama'
);

var_dump(
    highlightWords(
        'Мама мЫла раму мама',
        [
            'мама', 'Раму',
        ],
    ) === '[Мама] мЫла [раму] мама'
);

var_dump(
    highlightWords(
        'Мама мЫла raMu мама',
        [
            'мама', 'ramu',
        ],
    ) === '[Мама] мЫла [raMu] мама'
);
