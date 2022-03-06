<?php
//исправил вроде все; паровозик который смог без for )

// Создание массива
$wordArraysArr = array(
    array("bigword1.2"),
    array("moreword2.34aaaaaaaaaaaa", "word2.1", "bigword2.2", "moreword2.3"),
    array("moreword3.3", "word3.1", "bigword3.2"),
    array("moreword3.3", "word3.1"),
    array("moreword3.3", "word3.1", "bigword3.2"),
);

// Задание переменных размеров массива для удобства 
$arrCount    = count($wordArraysArr);
$maxStrCount = maxStringCount($wordArraysArr);

// Добавление строк и пробелов к строкам с нужной стороны
foreach ($wordArraysArr as $arrKey => $wordsArr) {
    
    // добавляем пустые строки к массивам слов до максимального количества
    $addedStringNumber      = $maxStrCount - count($wordsArr);
    $wordArraysArr[$arrKey] = array_pad($wordsArr, $maxStrCount, ' ');
    
    //добавляем пробелы в сами строки до нужного числа
    $spaceCount = findMaxStringLength($wordsArr);
    foreach ($wordArraysArr[$arrKey] as $stringKey => $string) {
        
        //само добавление пробелов слева или справа в зависимости от четности массива
        $wordArraysArr[$arrKey][$stringKey] = str_pad(
            $string, $spaceCount, ' ', ($arrKey % 2 === 0) ? STR_PAD_RIGHT : STR_PAD_LEFT
        );  
    }
}

// собираем все в одну строку(внутренний ключ выводим во внешний foreach чтобы получилс аналог транспонирования)
$lastWordsString = "";
foreach ($wordArraysArr[0] as $innerIndex => $words) {
    foreach ($wordArraysArr as $outerKey => $arr) {
        
        //убрал лишние пробелы ( было в TODO)
        $lastWordsString .= $wordArraysArr[$outerKey][$innerIndex].(($outerKey < count($arr) - 1) ? " " : "");
    }
    
    // переводим строчку
    $lastWordsString .= "\n";
}

// выводим получившуюся строку
echo "<pre>".$lastWordsString."</pre>";


/**
 * Находит максимальное количество строк во вложенных массивах.
 * 
 * @param array $__wordArraysArr Массив массивов строк.
 * 
 * @return integer Возвращает максимальное количество строк из всех массивов.
 */
function maxStringCount(array $__wordArraysArr)
{
    $maxStringCount = 0;
    foreach ($__wordArraysArr as $wordsArr) {
        $maxStringCount = max($maxStringCount, count($wordsArr));
    }
    
    //return count of string in array
    return $maxStringCount;
}

/**
 * Находит максимальную длинну строки в массиве.
 * 
 * @param array $__stringArr Массив строк.
 * 
 * @return integer Возвращает длину самой длинной строки.
 */
function findMaxStringLength(array $__stringArr)
{
    $maxStringSize = 0;
    foreach ($__stringArr as $string) {
        $maxStringSize = max($maxStringSize, strlen($string));
    }

    // выводим максимальную длину слова в массиве    
    return $maxStringSize;
}
