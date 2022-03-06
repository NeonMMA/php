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
    'http://dom.bb.bb.dom.domain2.com:8080/folder/subfolder/.'
    .'/myfolder/script.php?var1=val1&var2=val2?var1=val1&var2=val2',
);

// вывод парсинга для всех URL
foreach ($addressArr as $value) {
    print_r($value."\n\n");
    print_r(myParse($value));
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
    $resultArr = array(
        'protocol'     => false,
        'domain'       => false,
        
        //
        'zone'         => false,
        'secondDomain' => false,
        'port'         => "80",
        'rawFolder'    => false, 
        'folder'       => false,
        
        //
        'script_path'  => false,
        'scriptName'   => false,
        'isPhp'        => false, 
        'parameters'   => array(),
        
        //
        'isError'      => false  
    );
    
    // отделяем параметры от юрла
    $parametersArr  = explode("?", $__addressStr, 2);
    $scriptFullPath = $parametersArr[0];
    $parametersStr  = isset($parametersArr[1]) ? $parametersArr[1] : "";
    
    // парсим параметры 
    $keyValueArr     = explode("&", $parametersStr);
    $parametersArray = array();
    foreach ($keyValueArr as $keyValue) {
        $keyVal                      = explode("=", $keyValue, 2);
        $parametersArray[$keyVal[0]] = isset($keyVal[1]) ? $keyVal[1] : "";
    }
    
    //кидаем параметры в конечный массив
    $resultArr['parameters'] = $parametersArray;
    
    //отделяем протокол и (домен и путь) если он есть, если нет -> все в путь
    $protocolArr = explode("://", $scriptFullPath, 2);
    if (isset($protocolArr[1])) {
        $resultArr['protocol'] = $protocolArr[0];
        $domainAndFolder       = $protocolArr[1];
        
        // выделяем домен и путь
        $domainAndFolderArr = explode("/", $domainAndFolder, 2);
        $fullDomain         = $domainAndFolderArr[0];
        $rawFolder          = isset($domainAndFolderArr[1]) ? $domainAndFolderArr[1] : "";
        
        //откидываем порт, если он есть
        $portArr           = explode(":", $fullDomain);
        $resultArr['port'] = isset($portArr[1]) ? $portArr[1] : "80";
        
        // кидаем оставшийся домен в ответ
        $domain              = $portArr[0];
        $resultArr['domain'] = $domain;
        
        // смотрим состав домена/
        $domainArr            = explode(".", $domain);
        $domainNumber         = count($domainArr);
        $resultArr['isError'] = ($domainNumber > 5) ? "Error" : "";
        $resultArr['zone']    = $domainArr[$domainNumber - 1];
        
        // ну вдруг разделов будет меньше 2х
        $resultArr['secondDomain'] = ($domainNumber > 1) ? ($domainArr[$domainNumber - 2]
                .".".$domainArr[$domainNumber - 1]) : $domainArr[0]; 
    }
    
    //если не нашли протокол -> обнуляем порт, остаток кидаем в сырой путь 
    else {
        $rawFolder         = $protocolArr[0];
        $resultArr['port'] = false;
    }
    
    //отделяем исполняемый файл и обрабатываем его
    $folderArr               = explode("/", $rawFolder);
    $file                    = array_pop($folderArr);
    $resultArr['scriptName'] = ($file != "") ? $file : (($resultArr['parameters'] != false) ? "index.php" : "");
    
    // 4 - длина строки ".php"  
    $resultArr['isPhp'] = (strpos($resultArr['scriptName'], ".php") === strlen($resultArr['scriptName']) - 4) ? "true" : "false";
    
    //вносим пути в конечный массив
    $rawFoldersStr            = (!empty($folderArr)) ? (implode("/", $folderArr)."/") : "";    
    $resultArr['rawFolder']   = $rawFoldersStr;
    $resultArr['folder']      = searchPath($rawFoldersStr);
    $resultArr['script_path'] = $resultArr['folder'].$resultArr['scriptName'];
    
    //выводим парс
    return $resultArr;
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
    if ($__rawFolder == "") {
        return "";
    }
    
    //разбиваем путь по / на папки 
    $rawFolderArr = explode("/", $__rawFolder);
    $skipArr      = array("", ".");
    $pathArr      = array();
    
    // проходимся по папкам, если стоит .. -> удаляем последний элемент из массива папок
    foreach ($rawFolderArr as $folder) {
        if (in_array($folder, $skipArr)) {
            continue;
        }
        elseif ($folder == "..") {
            array_pop($pathArr);
        }
        
        //если есть имя папки -> выводим
        else {
            $pathArr[] = $folder;
        }
    }
    
    //вывод конечного пути до исполняемого файла
    $folder = (isset($pathArr)) ? implode('/', $pathArr).'/' : "";
    return $folder;
}
