<?php

// встречайте! теперь с целочисленным делением, отрицательными числами и вроде без ошибок
//массив систем счисления 
$basesArr = array(
    //проблемы с делением на 0, проблемы когда нет операндов 
    "2"  => array("1", "0"),
    "8"  => array("0", "1", "2", "3", "4", "5", "6", "7"),
    "9"  => array("0", "1", "2", "3", "4", "5", "6", "7", "8"),
    
    // 
    "10" => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"),
    "16" => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f")
);

//массив знаков
$signArr = array("+", "-", "*", "/");

//создание переменных из полученного массива POST (если он получен) 
//(не понятно на что ругается кодснифер, вызывается POST от условия, ошибок не выкидывает, мусор откидывается)
$firstNumber  = (isset($_POST["firstNumber"])) ? preg_replace("/[^A-z0-9 А-Яа-я\-]+-*/uis", "", $_POST["firstNumber"]) : 0;
$secondNumber = (isset($_POST["secondNumber"])) ? preg_replace("/[^A-z0-9 А-Яа-я\-]+-*/uis", "", $_POST["secondNumber"]) : 0;
$result       = (isset($_POST["result"])) ? preg_replace("/[^A-z0-9 А-Яа-я\-]+-*/uis", "", $_POST["result"]) : "";
$base         = (isset($_POST["base"])) ? preg_replace("/[^A-z0-9 А-Яа-я]+-*/uis", "", $_POST["base"]) : "2";

// проверяем наличие в массиве знаков
$sign = (isset($_POST["sign"])) ? ((in_array($_POST["sign"], $signArr)) ? $_POST["sign"] : "er") : "";

//для карусели создаем переменнубю ошибок раньше блока проверки
$errorStr = "";

// подставляем result в отсутствующий input
if ($result != "" && ($firstNumber == "" || $secondNumber == "")) {
    
    // из_за условия в ифе не будет ошибки если будет 1 и 2 аргумент
    $errorStr .= inputChecker($result, $basesArr, $base) ? "" : "ошибка в результате <br/>";
    if ($firstNumber == "" && $secondNumber != "") {
        $firstNumber = $result;
    }

    // как сказали, результат в первое число, первое - во второе
    elseif ($secondNumber == "" && $firstNumber != "") {
        $secondNumber = $firstNumber;
        $firstNumber  = $result;
    }
}

// блок ошибок
if ($secondNumber == "" && $firstNumber == "") {
    $errorStr .= "нет входных параметров <br/>";
}
else{
    
    // откидываем ошибку из результата если пришли оба аргумента
    $errorStr  = "";
    $errorStr .= inputChecker($firstNumber, $basesArr, $base) ? "" : "ошибка в первом числе <br/>";
    $errorStr .= inputChecker($secondNumber, $basesArr, $base) ? "" : "ошибка во втором числе <br/>";
}

// проверяем оставшиеся параметры
$errorStr .= (array_key_exists($base, $basesArr)) ? "" : "СС не содержится в списке CC <br/>";
if ($sign == "er") {
    $errorStr .= "ошибка со знаком";
}

// перевод кастомных чисел в dec 
$firstCodeNumber  = convertToDec($firstNumber, $basesArr, $base);
$secondCodeNumber = convertToDec($secondNumber, $basesArr, $base);

// cчитаем если не было ошибок, eval реально помог 
if ($errorStr == "") {
    if ($sign == "/" && $secondNumber == 0) {
        $errorStr .= "деление на ноль";
    }
    else {
        eval("\$codeDecResult =intval(intval(\$firstCodeNumber) $sign intval(\$secondCodeNumber));");
    }
}

// если все прошло удачно и мы посчитали - выводим, нет -> обнуляем результат
if (isset($codeDecResult)) {
    $baseResult = decBaseDecoding((string)$codeDecResult, $basesArr, $base);
}
else {
    $baseResult    = "";
    $codeDecResult = "";
}

// вывод самой страницы
echo createHtml($basesArr, $signArr, $base, $baseResult, $errorStr);

/**
 * Функция проверки чисел пришедших из input.
 * 
 * @param string $__number   Входящее число.
 * @param array  $__basesArr Массив СС.
 * @param string $__base     СС которую выбрал пользователь.
 * 
 * @return boolean true - все хорошо, false - есть проблемы.
 */
function inputChecker(string $__number, array $__basesArr, string $__base)
{
    if ($__number[0] == "-") {
        $__number = substr($__number, 1);
    }

    // посимвольно проверяем наличие в базе, если нет символа => выбиваем false
    $firstNumberArr = str_split($__number);
    foreach ($firstNumberArr as $latter) {
        if (array_search($latter, $__basesArr[$__base]) === false) {
            return false;
        }
    }
    
    //выводим, что все хорошо
    return true;
}

/**
 * Из числа в dec CC переводим в base CC, где число будет 
 * состоять из символов массива basesArr.
 * 
 * @param string $__number   Число для перевода.
 * @param array  $__basesArr Массив СС.
 * @param string $__base     Выбранная пользователем СС.
 * 
 * @return array
 */
function decBaseDecoding(string $__number, array $__basesArr, string $__base)
{
    $signNumber     = local_gmp_sign($__number);
    $codeBaseNumber = base_convert(abs($__number), 10, $__base);
    $decodeNumber   = "";
    
    // начинаются танцы с бубном, теперь это массив символов адекватной СС
    $codeBaseNumberArr = str_split($codeBaseNumber);
    foreach ($codeBaseNumberArr as $symbol) {
        
        // а теперь мы имеем десятичный ключ к нашей базе
        $internalSymbol = base_convert($symbol, $__base, 10);
        
        //собственно склейка с символами из кастомной СС по нормальному ключу
        $decodeNumber .= $__basesArr[$__base][$internalSymbol];
    }
    
    //возвращаем число в СС base из символов из basesArr[base]
    return $signNumber.$decodeNumber;
}

/**
 * Функция преобразует число из кастомной СС в dec для удобства последующего счета.
 * 
 * @param string $__number   Входное число.
 * @param array  $__basesArr Массив СС.
 * @param string $__base     Выбрааная пользователем СС.
 * 
 * @return int Возврат dec значения.
 */
function convertToDec(string $__number, array $__basesArr, string $__base)
{
    // вырвем знак минус если он есть, в число кинем модуль
    $signNumber   = local_gmp_sign($__number);
    $__number     = (string)(abs($__number));
    $numberLength = mb_strlen($__number);
    $codeNumber   = 0;
    
    // идем справа налево, ищем в массиве и умнажаем на базу в степени номера (как в школе ручками делали)
    for ($index = 0; $index < $numberLength; $index++) {
        $codeNumber += (array_search(
            $__number[$index], $__basesArr[$__base]
        )
        
        //хотел разнести на 2 строчки ;( , кодснифер сказал что так правильно
        ) * pow((int)$__base, $numberLength - $index - 1);
    }
    
    //возвращаем dec значение
    return $signNumber.$codeNumber;
}


/**
 * Программа генерации HTML кода страницы(Чтобы не смешивать с логической частью кода).
 * 
 * @param array  $__bases    Массив систем счисления.
 * @param array  $__signArr  Массив знаков.
 * @param string $__base     Выбранная система счисления.
 * @param string $__result   Ответ калькулятора.
 * @param string $__errorStr Набор ошибок.
 * 
 * @return string Возвращает HTML код страницы строкой.
 */
function createHtml(
    array $__bases, array $__signArr, string $__base, string $__result, string $__errorStr
)
{
    
    //создаем из массива baseArr выпадающий список
    $selectStr = "<select type=\"text\" name=\"base\">";
    foreach ($__bases as $key => $__) {
        $selectStr = $selectStr."<option ".(($__base == $key) ? "selected=\"selected\"" : "").">".$key."</option>";
    }
    
    //закрываем select
    $selectStr .= "</select><br/>";
    
    //создаем окошки ввода двух чисел
    $inputStr  = "<input type=\"string\" name=\"firstNumber\"/><br/>";
    $inputStr .= "<input type=\"string\" name=\"secondNumber\"/><br/>";
    
    // создание кнопок со знакaми
    $signInputStr = "<div align=\"center\">";
    foreach ($__signArr as $key => $__sign) {
        $signInputStr .= "<button name=\"sign\" type=\"submit\" value=\"".$__sign
        ."\" >".$__sign."</button>";
    }
    
    // закрываем поле выбора знака
    $signInputStr .= "</div>";
    
    //формирование всего кода из кусков строк
    $html = "<header><style>@import url(css.css)</style></header><form align=\"center\" method=\"POST\"><br/>"
    .$selectStr.$inputStr.$signInputStr
    ."<input name=\"result\" readonly placeholder=\"тут будет выведен результат"
    ." вычислений\" value=\"".$__result."\"/><p>".$__errorStr."</p>";
    return $html;
}

/**
 * Стандартную функцию gmp_sign netbeans не видит, поэтому такая.
 * 
 * @param string $number Входное число.
 * 
 * @return string Возвращаем знак полученного числа.
 */
function local_gmp_sign(string $number)
{
    if ($number[0] == "-") {
        return "-";
    }
    
    //>=0
    else {
        return "";
    }
}
