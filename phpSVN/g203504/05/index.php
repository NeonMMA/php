<?php

// открываем сессию
session_start();

// забрали входные параметры
$argumentsArr = array(
    "name", 
    "login", 
    "password",
    "adminPass",
    
    //
    "new", 
    "exit", 
    "sendName",
    "text", 
    
    //
    "delete", 
    "selectPage",
);

// берем переменные из POST
foreach ($argumentsArr as $key => $value) {
    $$value = (isset($_POST[$value])) ? preg_replace("/[^A-z0-9 А-Яа-я]+-*/uis", "", $_POST[$value]) : "";
}

// подгружаем файлы
$usersFile    = "users.txt";
$messagesFile = "guestbook.txt";
touch($messagesFile);
touch($usersFile);

// дефолтные значения const не хотел брать md5, поэтому define, название переменной как в задании messagesOnPage
$errorUser = "";
define("adminPassword", md5("admin"));
const messagesOnPage = 5;

//вытащили массив пользователей 
$usersArr = unserialize(file_get_contents($usersFile));
if ($usersArr == "") {
    $usersArr = array();
}

// проверка на нового пользователя
if ($new == "new") {
    
    // сделать проверку если такой логин есть -> отакт
    if (!array_key_exists($login, $usersArr) && $name != "" && $login != "" && $password) {
        
        // раз зашли - ошибок нет
        $errorUser        = "";
        $status           = (md5($adminPass) === adminPassword) ? "admin" : "user";
        $usersArr[$login] = array(
                             "login"  => $login,
                             "name"   => $name, 
           
                             // заполняем остатки массива
                             "pass"   => md5($password), 
                             "status" => $status
                         );
        
        //записываем имя в сессию и выходим 
        file_put_contents($usersFile, serialize($usersArr));
        $_SESSION['user'] = $usersArr[$login];
        ruin();
    }
    else {
        $errorUser .= "Ошибка входных данных";
    }
}

// вход имеющегося в бд пользователя
else {
    if (isset($usersArr[$login])) {
        
        //проверяем логин и пароль среди существующих 
        if ($usersArr[$login]["pass"] == md5($password)) {
            $_SESSION['user'] = $usersArr[$login];
            ruin();
        }
        else {
            $errorUser = "Неверный логин или пароль";
        }
    }
    
    // не нашли пользователя 
    else {
        if (isset($_POST["login"])) {
            $errorUser = "такого пользователя не существует";
        }
    }
}

//разлогинились, массив не обьявляется, а обнуляется (кодснифер ругается зря)
if ($exit == "exit") {
    $_SESSION = array();
    ruin();
}

// создаем массив сообщений
$messagesArr = myUnSerializeMessage(file_get_contents($messagesFile));
$messagesArr = ($messagesArr == "") ? array() : $messagesArr;

//обработка отправленных сообщений 
$errorMess = "";
if ($text != "") {
    $name = isset($_SESSION['user']) ? $_SESSION['user']["name"] : $sendName;
    if ($name != "") {
        
        //кидаем инфу о новом сообщении в массив
        $messagesArr[] = array(
                          "login" => isset($_SESSION['user']) ? $_SESSION['user']['login'] : false, 
                          "name"  => $name, 
                          "date"  => date('H-i-s-d-F-Y'),
                          "text"  => $text, 
                    
                          // кидаем ip из массива сервера
                          "ip"    => $_SERVER["REMOTE_ADDR"]
                      );
    }
    else {
        $errorMess = "Имя пустое<br/> Зарегистрируетесь?";
    }
}

// объявляем права
$status = isset($_SESSION["user"]) ? $_SESSION["user"]["status"] : false;


//обработчик на кнопку delete с проверкой статуса пользователя
if ($delete != "" && $status != false) {
    
    // тк перед выводом массив был реверснут, меняем delete на противоположный (-1 - для учета нулевого элемента)
    $delete = count($messagesArr) - $delete - 1;
    unset($messagesArr[$delete]);
    $messagesArr = array_values($messagesArr);
}

// кидаем обновленный массив сообщений в файл
file_put_contents($messagesFile, mySerializeMessage($messagesArr));

/*
//убираем блокировку с файла сообщений
flock($fopenMessagesFile, LOCK_UN);
fclose($fopenMessagesFile);
*/

//обработчик выбранной страницы, если не выбрана -> ставим метку на последнюю страницу
$selectPage = ($selectPage == "") ? 1 : $selectPage;

//проверка на пользователя и на ошибки
$isSetUser = isset($_SESSION['user']);

//вывод страницы
$html  = createHeaderHtml();
$html .= createMessagesHtml((($messagesArr == "") ? array() : $messagesArr), $selectPage);
$html .= "<hr/>";
$html .= (($selectPage == 1) ? createSenderHtml((($isSetUser) ? $_SESSION['user'] : ""), $errorMess) : "");
$html .= "<hr/>"; 

// докидываем выбор странички и выводим полный html код
$html .= createLinkBarHtml((($messagesArr == "") ? array() : $messagesArr), $selectPage)."</div>";
$html .= ($isSetUser) ? createExitHtml($_SESSION['user']["name"]) : createEntranceHtml($errorUser);
echo $html;

/**
 * Создание header для html.
 * 
 * @return string Код header'а.
 */
function createHeaderHtml()
{
    return "<header>"
    ."<style>@import url(css.css)</style>"
    ."</header>";
}

/**
 * Создание формы отправки сообщений.
 * 
 * @param mixed  $__isSetUser Hаличие имени в сессии.
 * @param string $__error     Ошибки создания сообщений.
 * 
 * @return string Вывод кода.
 */
function createSenderHtml($__isSetUser, string $__error)
{
    // пишем форму отправки сообщения
    $html = "<form class=\"sender\" method=\"post\">"
            
    // проверяем вошел ли пользователь 
    .(($__isSetUser == "") ? "<input name=\"sendName\" "
    ."placeholder=\"введите имя под которым хотите отправить сообщение\"/>" : "")
    ."<textarea name=\"text\" placeholder=\"тапай\"></textarea>"
    ."<p>".$__error."</p>"
            
    //пишем кнопку отправки сообщения и закрываем форму
    ."<button type=\"submit\">Отправить сообщение</button>"
    ."</form>";
    
    // вывод кода
    return $html;
}

/**
 * Функция переоткрытия файла.
 * 
 * @return void
 */
function ruin()
{
    header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
    die();
}

/**
 * Блок отправки сообщений.
 * 
 * @param string $__errors Ошибки отправки сообщений.
 * 
 * @return string
 */
function createEntranceHtml(string $__errors)
{
    $html = "<form class=\"enter form\" id=\"enter\" method=\"post\" ><input name=\"name\" placeholder=\"Имя\"/>"
    ."<input name=\"login\" placeholder=\"логин\"/>"
    ."<input name=\"password\" placeholder=\"пароль\"/>"
    
    // продолжение формы
    ."<input name=\"adminPass\" placeholder=\"пароль админа\"/>"
    ."<br/>"
    ."<input name=\"new\" type=\"checkbox\" value=\"new\">Новый пользователь</input>"
    ."<p>".$__errors."</p>"
    ."<button name=\"enter\" type=\"submit\" value=\"in\">Вход</button>"
    
    // закрываем форму и выводим код
    ."</form>";
    return $html;
}

/**
 * Создание выходной формы.
 * 
* @param string $__name Вывод имени.
 * 
 * @return string Вывод формы.
 */
function createExitHtml(string $__name)
{
    //форма выхода
    $html = "<form class=\"exit form\" method=\"post\">"
    ."<p>Вы вошли как: ".$__name."</p>"
    ."<button name=\"exit\" type=\"submit\" value=\"exit\">exit</button>"
    ."</form>";
    return $html;
}

/**
 * Создание блока сообщений.
 * 
 * @param array  $__messArr    Массив сообщений для вывода.
 * @param string $__selectPage Выбранная страница.
 * 
 * @return string Вывод кода.
 */
function createMessagesHtml(array $__messArr, string $__selectPage)
{
    $html = "<div class=\"mess\">";
    if (empty($__messArr)) {
        return $html."<p>Еще нет ни одного сообщения. Стань первым!</p>";
    }
    
    // перевернем для обратного порядка
    $__messArr = array_reverse($__messArr);
    
    //задаем номера первого и последнего сообщения на странице 
    $firstMessage = ($__selectPage - 1) * messagesOnPage;
    $lastMessage  = min($__selectPage * messagesOnPage - 1, count($__messArr) - 1);

    // проходимся по массиву сообщений 
    for ($index = $firstMessage; $index <= $lastMessage; $index++) {
        $value = $__messArr[$index];
        
        //если есть сессия - из нее можно взять login если нет - то он false в $value
        $__login  = isset($_SESSION["user"]) ? $_SESSION["user"]["login"] : false;
        $__status = isset($_SESSION["user"]) ? $_SESSION["user"]["status"] : false;

        // начинаем клейку сообщнений
        $html .= "<div class=\"message ".(($value["login"] == $__login) ? "out" : "in")."\" ><time>";

        // ip только админ видит, удалить только для авторизованных
        $html .= $value["name"]." ".$value["date"]." "
        .(($__status == "admin") ? $value["ip"] : "")
        .(($__status == "admin" || $__login != false && $value["login"] == $__login) ? "<form"
                ." method=\"post\"><button name=\"delete\" "
                ."type=\"submit\" value=\"".$index."\">"
                
                //вставляем картинку на удаление сообщения
                ."<img src=\"images/crossed.png\"></button></form>" : "")
        ."</time><br/><p>"

        //параметры вывели, осталось сообщение
        .$value["text"];
        $html .= "</p></div>";
    }
    
    //закрываем поле вывода и отправляем код 
    $html .= "";
    return $html;
}

/**
 * Создание html выбора странички.
 * 
 * @param array   $__messArr    Массив сообщений.
 * @param integer $__selectPage Номер выбранной страницы.
 * 
 * @return string Html код.
 */
function createLinkBarHtml(array $__messArr, int $__selectPage)
{
    $html        = "<div class=\"select\"><form method=\"post\"><p> Select page: </p><div>";
    $pagesNumber = intdiv(count($__messArr), messagesOnPage) + ((count($__messArr) % messagesOnPage != 0) ? 1 : 0);
    
    // вывод индексов делаем обратным для удобства восприятия (тк 1 по сути это первая страница, а не последняя)
    for ($index = 1; $index <= $pagesNumber; $index++) {
        $html .= "<button name=\"selectPage\" type=\"submit\" value=\""
        
        // проверка на выбранную страницу 
        .($index)."\">".(($index == $__selectPage) ? "<b>" : "")." ["
        .($index)."]"
        .(($index == $__selectPage) ? "</b>" : "")
        ." </button>";
    }
    
    //закрываем форму и выводим
    $html .= "</div></form></div>";
    return $html;
}

/**
 * Из массива в кодированную строку.
 * 
 * @param array $__messagesArr Массив сообщений.
 * 
 * @return string Кодированная строка для записи в файл.
 */
function mySerializeMessage(array $__messagesArr)
{
    if (empty($__messagesArr)) {
        return "";
    }

    // [:|||:] разделитель между элементами
    // [:||:] разделитель внутри переменной 
    $serMessArr = array();
    
    // склеиваем через разделитель сами сообщения
    foreach ($__messagesArr as $messKey => $messArray) {
        $serMessArr[] = $messKey."[:||:]".implode("[:||:]", $messArray);  
    }
    
    //конечная строка сообщений
    $serializeStr = implode("[:|||:]", $serMessArr);
    return $serializeStr;
}

/**
 * Парс кодированной строки из файла.
 * 
 * @param string $__serializeStr Строка для парса.
 * 
 * @return array Массив сообщений.
 */
function myUnSerializeMessage(string $__serializeStr)
{
    $unSerializeArr = array();
    if ($__serializeStr == "") {
        return "";
    }
    
    //разбиваем на массивы по разделителю
    $arrayPart = explode("[:|||:]", $__serializeStr);
    foreach ($arrayPart as $value) {
        $paramArr = explode("[:||:]", $value);
        
        // создаем массив параметров, для массива сообщений
        $unSerializeArr[$paramArr[0]] = array(
                                         "login" => $paramArr[1], 
                                         "name"  => $paramArr[2], 
                                         "date"  => $paramArr[3],
                                         "text"  => $paramArr[4], 
            
                                         // вносим ip и закрываем массив
                                         "ip"    => $paramArr[5],
                                     );
    }
    
    //возвращаем массив сообщений
    return $unSerializeArr;
}
