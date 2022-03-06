<?php

//Информация о взаимодействии с пользователем
//1. Cookie 
//setcookie($name, $value, $expires, $path, $domain, $secure, $httponly)
//$_COOKIE[];
/*- постоянно передаются в обе стороны
- открытые
- мало места
- можно угнать
- можно подделать*/

////2. В сеансах
/*- данные не передаются
- данные хранятся на сервере и их нельзя увидеть
- объем хранилища большой
- можно угнать ключ (но обычно он действует не более 15 минут)
- данные нельзя подделать*/

/*session_start();

/*if(isset($_SESSION['counter']))
	$_SESSION['counter']++;
else $_SESSION['counter'] = 1;
$_SESSION['counter'] = isset($_SESSION['counter'])
	? $_SESSION['counter'] + 1
	: 1;

print $_SESSION['counter'];

session_destroy();
$_SESSION = [];*/


//print md5("123");
//3. В хранилище браузера с помощью JS

session_start();

//Блок обработки параметров
foreach(['login', 'password', 'action'] as $variableName)
	$$variableName = isset($_REQUEST[$variableName])
		? preg_replace("/[^a-z0-9А-Яа-я]+/uis", '', $_REQUEST[$variableName])
		: '';


//вход пользователя
if($login && $password && $login == "admin" && md5($password) == "202cb962ac59075b964b07152d234b70") {
	$_SESSION['user'] = "admin";
	header("Location: http://". $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"]);
	die();
}

//вход пользователя
if($action == "Выйти") {
	//$session_destroy();
	$_SESSION = [];
	header("Location: http://". $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"]);
	die();

}

$html = htmlHeader();
$html.= isAdmin() ? logoutForm() : loginForm();
$html.= htmlFooter();

print $html;

function isAdmin() {
	return isset($_SESSION['user']);
}

function htmlHeader () {
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>lection1</title>
  <style>
	  * {padding:0px; margin:0px; border-collapse:collapse; font: 12px Tahoma;}
	  .login_form {margin: 30px auto; border: #666 1px solid; padding: 5px; width: 400px; background-color: #ccc;}
	  input {border: #666 1px solid; padding: 5px; width: 388px; margin-bottom: 3px;}
	  input[type="submit"] { width: 400px; margin-bottom: 0px;}
	  p {padding-bottom: 3px;}
  </style>
</head>
<body>';
}
function loginForm() {
	return '	<div class="login_form">
	<form action="" method="post">
		<input placeholder="Логин" type="text" name="login" />
		<input placeholder="Пароль" type="password" name="password" />
		<input type="submit" name="action" value="Войти" />
	</form>
	</div>';
}
function logoutForm() {
	return '	<div class="login_form">
	<form action="" method="post">
		<p>Привет, Иван!</p>
		<input type="submit" name="action" value="Выйти" />
	</form>
	</div>';
}
function htmlFooter() {
	return '</body>
</html>';
}






