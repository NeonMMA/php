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
    "send", 
    "sendName",
    "text", 
    
    //
    "delete", 
    "selectPage",
    "redact",
    "redactText",
    "sendRedact",
    
// переменные кончились, нужен комментарий
);

// берем переменные из POST
foreach ($argumentsArr as $key => $value) {
    
    //
    $$value = (isset($_POST[$value])) ? preg_replace(
        "/[^A-z0-9 А-Яа-яёЁ\/—\s\.,?:!\-]+-*/uis",
        "",  
        preg_replace("/<br\/>/xuis", "\n", $_POST[$value])
    ) : "";
}

// оставим перенос строки
$text       = preg_replace("/\n+/uis", "<br/>", $text);
$redactText = preg_replace("/\n+/uis", "<br/>", $redactText);


// подгружаем файлы
$usersFile    = "user.txt";
$messagesFile = "guestbook.txt";
touch($messagesFile);
touch($usersFile);

// дефолтные значения, const не хотел брать md5, поэтому define, название переменных как в задании
$errorUser = "";
define("adminPassword", md5("admin"));
const messagesOnPage = 5;
const pagesAround    = 1;

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
        
        // открываем файл пользователей, локаем
        $openUsersFile = fopen($usersFile, "r+");
        flock($openUsersFile, LOCK_EX);
        
        //записываем имя в сессию и выходим 
        fseek($openUsersFile, 0);
        ftruncate($openUsersFile, 0);
        fwrite($openUsersFile, serialize($usersArr));
        
        //убираем блокировку с файла пользователей
        flock($openUsersFile, LOCK_UN);
        fclose($openUsersFile);
        $_SESSION['user'] = $usersArr[$login];
        ruin();
    }
    
    // пользователь уже существует
    else {
        $errorUser .= "Ошибка входных данных, пользователь уже существует";
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

//разлогинились; массив не обьявляется, а обнуляется (кодснифер ругается зря)
if ($exit == "exit") {
    $_SESSION = array();
    ruin();
}

// создаем массив сообщений (2стр - нужен именно массив а не пустая строка)
$messagesArr = unserialize(file_get_contents($messagesFile));
if ($messagesArr == "") {
    $messagesArr = array();
}

//обработка входящих сообщений
$errorMess = "";
if ($text != "") {
    $name = isset($_SESSION['user']) ? $_SESSION['user']["name"] : $sendName;
    if ($name != "") {
        
        //кидаем инфу о новом сообщении в массив
        $messagesArr[] = array(
                          "login"  => isset($_SESSION['user']) ? $_SESSION['user']['login'] : false, 
                          "name"   => $name, 
                          "date"   => date('H-i-s-d-F-Y'),
                          "text"   => $text, 
                    
                          // кидаем ip из массива сервера
                          "ip"     => $_SERVER["REMOTE_ADDR"],
                          "redact" => "",
                      );
    }
    else {
        $errorMess = "Имя пустое<br/> Зарегистрируетесь?";
    }
}

// объявляем права
$status = isset($_SESSION["user"]) ? $_SESSION["user"]["status"] : false;

// накрутить обработчик на кнопку delete + проверка прав
if ($delete != "" && $status != false) {
   
    // тк перед выводом массив был реверснут, меняем delete на противоположный (-1 - для учета нулевого элемента)
    $delete = count($messagesArr) - $delete - 1;
    unset($messagesArr[$delete]);
    $messagesArr = array_values($messagesArr);
}

// переходим на страницу которую нужно нарисовать с редактором сообщений
if ($redact != "") {
    
    // тк перед выводом массив был реверснут, меняем delete на противоположный (-1 - для учета нулевого элемента)
    $redact     = count($messagesArr) - $redact - 1;
    $selectPage = ceil(count($messagesArr) / messagesOnPage) - ceil(($redact + 1) / messagesOnPage) + 1;
}

// время редактировать сообщения + проверка прав
if ($redactText != "" && $status != false) {
    
    // нам нужно редачить только тогда, когда текст изменился
    if ($messagesArr[$sendRedact]["text"] != $redactText) {
        $messagesArr[$sendRedact]["text"]   = $redactText;
        $messagesArr[$sendRedact]["redact"] = "отредактировано пользователем "
        
        //  вкидываем имя и время изменения
        .$_SESSION["user"]["name"]." "
        .date("d.m.Y")
        ." в "
        .date("H.i.s");
    }
}

// ставим лок на файл сообщений для записи
$openMessagesFile = fopen($messagesFile, "r+");
flock($openMessagesFile, LOCK_EX);

// кидаем обновленный массив сообщений в файл
fseek($openMessagesFile, 0);
ftruncate($openMessagesFile, 0);
fwrite($openMessagesFile, serialize($messagesArr));
flock($openMessagesFile, LOCK_UN);
fclose($openMessagesFile);

//обработчик выбранной страницы, если не выбрана -> ставим метку на первую страницу
$selectPage = ($selectPage == "") ? 1 : $selectPage;

//проверка на пользователя и на ошибки
$isSetUser = isset($_SESSION['user']);

//вывод страницы
$html  = createHeaderHtml();
$html .= createMessagesHtml((($messagesArr == "") ? array() : $messagesArr), $selectPage, $redact);
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
    ."<style>@import url(csss.css)</style>"
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
    ."<button name=\"send\" value=\"send\" type=\"submit\">Отправить сообщение</button>"
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
 * @param string $__redact     Какое сообщение редактируется.
 * 
 * @return string Вывод кода.
 */
function createMessagesHtml(array $__messArr, string $__selectPage, string $__redact)
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
                
                // допиливаем перед кнопкой удаления кнопку редактирования тк права те же
                ." method=\"post\"><button name=\"redact\" "
                ."type=\"submit\" value=\"".$index."\">"
                
                //вставляем картинку на редактирование сообщения
                ."<img src=\"images/redact.png\"></button></form>"
                
                // удаление
                ."<form method=\"post\"><button name=\"delete\" "
                ."type=\"submit\" value=\"".$index."\">"
                
                //вставляем картинку на удаление сообщения
                ."<img src=\"images/crossed.png\"></button></form>" : "")
        ."</time><br/>"
        
        // форма редактирования сообщения; массив реверснут, поэтому такая сложная проверка
        .(($__redact == (count($__messArr) - $index - 1) && $__redact != "") ? "<form method=\"post\"><textarea name=\"redactText\">"
                .str_replace("/\n/xuis", "<br/>", $value["text"]).
                "</textarea><button type=\"submit\" "
                ."name=\"sendRedact\" value=\"".$__redact
                ."\">Редактировать</button></form>" : "<p>".str_replace("/\n/xuis", "<br/>", $value["text"])."</p>");
            
        // кидаем инфу о редактировании и закрываем сообщение
        $html .= "<time>".$value["redact"]."</time>";
        $html .= "</div>";
    }
    
    // отправляем код (закрытие в linkbar)
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
    //создаем границы вывода для текущей страницы и начинаем код
    $pagesNumber = ceil(count($__messArr) / messagesOnPage);
    $currentPage = $pagesNumber - $__selectPage + 1;
    $leftBorder  = max($currentPage - pagesAround, 1);
    $rightBorder = min($currentPage + pagesAround, $pagesNumber);
    $html        = "<div class=\"select\"><form method=\"post\"><p> Select page: </p><div>";
    
    
    // вывод индексов делаем обратным для удобства восприятия (тк 1 по сути это первая страница, а не последняя)
    for ($index = 1; $index <= $pagesNumber; $index++) {
        if ($pagesNumber - $index + 1 >= $leftBorder 
            && $pagesNumber - $index + 1 <= $rightBorder 
            || $index == 1 || $index == $pagesNumber
        ) {
            $html .= "<button name=\"selectPage\" type=\"submit\" value=\""

            // проверка на выбранную страницу 
            .$index."\">".(($index == $__selectPage) ? "<b>" : "")." ["
            .($index)."]"
            .(($index == $__selectPage) ? "</b>" : "")
            ." </button>";
        }
        
        //если нам не подходит кнопка - вместо нее ставим точку
        else {
            $html .= ".";
        }
    }
    
    // меняем группу точек на нерабочую кнопку с многоточием того же стиля, тк иначе точки будут идти после кнопок
    $endHtml = preg_replace("/\.+/xius", "<button disabled>...</button>", $html);

    //закрываем форму и выводим
    $endHtml .= "</div></form></div>";
    return $endHtml;
}
