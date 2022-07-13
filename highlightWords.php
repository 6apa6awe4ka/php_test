
<?php
require './vendor/autoload.php';

use Ds\Set;

const LOWER_TO_UPPER_UTF8 = [
    'a' => 'A',
    'b' => 'B',
    'c' => 'C',
    'd' => 'D',
    'e' => 'E',
    'f' => 'F',
    'g' => 'G',
    'h' => 'H',
    'i' => 'I',
    'j' => 'J',
    'k' => 'K',
    'l' => 'L',
    'm' => 'M',
    'n' => 'N',
    'o' => 'O',
    'p' => 'P',
    'q' => 'Q',
    'r' => 'R',
    's' => 'S',
    't' => 'T',
    'u' => 'U',
    'v' => 'V',
    'w' => 'W',
    'x' => 'X',
    'y' => 'Y',
    'z' => 'Z',
    
    'а' => 'А',
    'б' => 'Б',
    'в' => 'В',
    'г' => 'Г',
    'д' => 'Д',
    'е' => 'Е',
    'ё' => 'Ё',
    'ж' => 'Ж',
    'з' => 'З',
    'и' => 'И',
    'й' => 'Й',
    'к' => 'К',
    'л' => 'Л',
    'м' => 'М',
    'н' => 'Н',
    'о' => 'О',
    'п' => 'П',
    'р' => 'Р',
    'с' => 'С',
    'т' => 'Т',
    'у' => 'У',
    'ф' => 'Ф',
    'х' => 'Х',
    'ц' => 'Ц',
    'ч' => 'Ч',
    'ш' => 'Ш',
    'щ' => 'Щ',
    'ъ' => 'Ъ',
    'ы' => 'Ы',
    'ь' => 'Ь',
    'э' => 'Э',
    'ю' => 'Ю',
    'я' => 'Я',
];

/**
 * 
 * Не учтены знаки препинания и буквы {Е/Ё}.
 * Не учтены лишние пробелы.
 * Ну и прочая грамматика естественно не учтена, склонения и т. д..
 * 
 */
function highlightWords(string $text, array $array_of_words, ?callable $strtoupperCallable = null): string {
    $strtoupperCallable = $strtoupperCallable ?? strtoupper(...);
    $set_of_words = new Set();
    $set_of_highlighted_words = new Set();
    foreach ($array_of_words as $word) {
        $set_of_words->add($strtoupperCallable($word));
    }

    $result_text = '';
    $word = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $l = $text[$i];
        
        if ($l === ' ') {
            $result_text .= highlightWord($word, $set_of_words, $set_of_highlighted_words, $strtoupperCallable);
            $result_text .= ' ';
            $word = '';
            continue;
        }

        $word .= $l;
    }
    $result_text .= highlightWord($word, $set_of_words, $set_of_highlighted_words, $strtoupperCallable);

    return $result_text;
}

function highlightWord(string $word, Set $set_of_words, Set $set_of_highlighted_words, $strtoupperCallable) {
    $lowercased_word = $strtoupperCallable($word);
    if (
        !$set_of_highlighted_words->contains($lowercased_word) && 
        $set_of_words->contains($lowercased_word)
    ) {
        $set_of_highlighted_words->add($lowercased_word);
        return "[$word]";
    }
    return  "$word";
}

function strtoupper_utf8($word) {
    $result_word = '';
    foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $l) {
        $result_word .= LOWER_TO_UPPER_UTF8[$l] ?? $l;
    }
    return $result_word;
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
        strtoupper_utf8(...)
    ) === '[Мама] мЫла [раму] мама'
);

var_dump(
    highlightWords(
        'Мама мЫла raMu мама',
        [
            'мама', 'ramu',
        ],
        strtoupper_utf8(...)
    ) === '[Мама] мЫла [raMu] мама'
);
