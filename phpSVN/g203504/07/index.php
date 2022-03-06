<?php

// массив всех тестовых URL
$addressArr = array(
    'http://http.ru/folder/subfolder/../././script.php?var1=val1&var2=val2',
    'https://http.google.com/folder//././?var1=val1&var2=val2',
    'ftp://mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2',
    'mail.ru/?hello=world&url=https://http.google.com/folder//././?var1=val1&var2=val2',
    
    //
    'index.html?mail=ru',
    'domain2.zone:8080/folder/subfolder/../././../asdss/.././//////../myfolder/script.php?var1=val1&var2=val2',
    'http://dom.bb.bb.dom.domain2.com:8080/folder/subfolder/./myfolder/script.php?var1=val1&var2=val2?var1=val1&var2=val2',
    'http://dom.bb.bb.dom.domain2.com/folder/subfolder/.././././../asdss/.././//////../myfolder/script.ph?var1=val1&var2=val2&var3=val3&var4=val4',
);


// вывод парсинга для всех URL
foreach ($addressArr as $value) {
    print_r($value."\n\n");
    var_dump(myParse($value));
    print_r("\n\n\n");
}

/**
 * Фунция парсинга
 * 
 * @param string $__addressStr Строка URL.
 * 
 * @return array Парсинг введенной строки.
 */
function myParse(string $__addressStr)
{
    $linkParse = "/(?:(?<protocol>[A-z]+):\/)? # протокол
    \/?                                       # разделитель 
    ((?<=:\/\/)                               # условие на домен
    (?<domain>[A-z0-9\.]*?                    # полный домен
    (?<domain2>[A-z0-9]*)                     # 2 домен
    \.                                        # точка перед зоной
    (?<zone>[A-z]*)                           # зона
    \:*                                       # возможно существующий разделитель перед портом
    (?<port>[0-9]*)                           # порт
    )                                         # конец домена
    (?:\/)                                    # разделитель до folder
    (?<rawFolderPlusFile>[^\?]*)*?\?          # путь+файл
    (?<parameters> .*)                        # строка параметров
    ) |                                       # если не нашлось разделителя протокола
    (?<rawFolderPlusFile>[^\?]*)*?\?          # путь+файл
    (?<parameters> .*)                        # строка параметров
    /xuisJ";                                  # J - модификатор повтора ключей
    
    // общий парс
    preg_match($linkParse, $__addressStr, $resultArr);

    // разбиваем папки и файл
    preg_match("/(.*)?\/([^\/]*)/xuisJ", $resultArr["rawFolderPlusFile"], $folderArr);
    
    // если прошло удачно
    if (!empty($folderArr)) {
        $resultArr["rawFolder"] = $folderArr[1]."/";
        $resultArr["file"]      = $folderArr[2];
    }
    else {
        
        // если не было папки+файл -> все обнуляем
        if ($resultArr["rawFolderPlusFile"] == "") {
            $resultArr["rawFolder"] = "";
            $resultArr["file"]      = "";
        }
        
        // иначе все идет в файл
        else {
            $resultArr["rawFolder"] = "";
            $resultArr["file"]      = $resultArr["rawFolderPlusFile"];
        }
    }
    
    // слишком длинное
    $file = ($resultArr["file"] != "") ? $resultArr["file"] : (($resultArr["parameters"] != "") ? "index.php" : "");
    
    //собираем конечный массив парсинга
    $finishArr = array(
        'protocol'     => $resultArr["protocol"],
        'domain'       => $resultArr["domain"],
        
        //
        'zone'         => $resultArr["zone"],
        'secondDomain' => $resultArr["domain2"],
        'port'         => ($resultArr["protocol"] != "") ? (($resultArr["port"] == "") ? "80" : $resultArr["port"]) : "",
        'rawFolder'    => $resultArr["rawFolder"], 
        'folder'       => searchPath($resultArr["rawFolder"]),
        
        //
        'script_path'  => searchPath($resultArr["rawFolder"]).$file,
        'scriptName'   => $file,
        'isPhp'        => (bool)preg_match("/.*\.(php)/xuisJ", $file), 
        'parameters'   => parseParam($resultArr["parameters"]),
        
        //
        'isError'      => (bool)(count(preg_split("/\./xuisJ", $resultArr["domain"])) > 5),
    );
    
    // возвращаем парс
    return $finishArr;
}

/**
 * Ищет путь до папки
 * 
 * @param string $__rawFolder Сырая строка пути до файла.
 * 
 * @return string Возвращаем обработанный путь.
 */
function searchPath(string $__rawFolder)
{
    // убираем . и ./
    while ($__rawFolder != preg_replace("/(\/+)|((?<=[^\.])\.\/)/xuis", "/", $__rawFolder)) {
        $__rawFolder = preg_replace("/(\/+)|((?<=[^\.])\.\/)/xuis", "/", $__rawFolder);
    }
    
    //удаляем лишние папки
    while ($__rawFolder != preg_replace("/([^\/]+\/\.\.\/) | (^\.\.\/)/xuis", "", $__rawFolder)) {
        $__rawFolder = preg_replace("/([^\/]+\/\.\.\/) | (^\.\.\/)/xuis", "", $__rawFolder);
    }
    
    // путь готов
    return $__rawFolder;
}

/**
 * Переводит параметры из строки в массив.
 * 
 * @param string $__paramStr Строка параметров.
 * 
 * @return array Массив параметров.
 */
function parseParam(string $__paramStr)
{   
    if ($__paramStr == "") {
        return array();
    }
    
    // выдернули отдельно ключи и значения
    preg_match_all("/([^=&]+)=([^&]+)/xuis", $__paramStr, $paramArr);
    
    //раскидываем в массив
    $resArr = array();
    foreach ($paramArr[1] as $key => $value) {
        $resArr[$value] = $paramArr[2][$key];
    }
    
    // возвращаем готовый массив
    return $resArr;
}
