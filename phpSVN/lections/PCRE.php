<?php

//Регулярные выражения в формате PCRE - Perl compatible regular expressions
//Регулярные POSIX

/*0. Границы выражения
"/выражение/модификаторы(режимы)"
## || есть варианты

1. режимы
u - unicode
i - регистронезависимый
s - строчный
m - построчный
e,x
		
2. классы символов (множества) - какие символы
а - одна буква а
абв - подряд идущие "абв"
. - один любой символ
\s - один пробельный символ (" ", \t, \n, \r)
\S - не пробельный
[абв] - одна буква из множества {а,б,в}
[A-z0-9] - англ буква или цифра
[А-Яа-яЁё] - русская буква
[^0-9] - не цифра
^ - начало текста
$ - конец текста
\n - граница слова

3. кванторы (кватификаторы) - сколько штук
? - 0 или 1
* - [0, бесконечно)
+ - [1, бесконечно)
*?, +? - "нежадные" версии
{4,6} от 4 до 6
{4,} {,6}
{5} - ровно 5
*/

/*4. функции PHP 
preg_match($pattern, $subject, $out)
preg_match_all($pattern, $subject, $out)
preg_replace($pattern, $replace, $subject)
preg_split($pattern, $subject, $limit)*/

$text = "I send him E-mail to dev@yandex.ru, but he uses dev@google.com";
//print preg_match_all("/(([a-z0-9\.\-_]+)@([a-z0-9\.\-]+))/uis", $text, $outArr);
/*print_r($outArr);
foreach($outArr[0] as $index => $email)
	print $email." = ".$outArr[2][$index]."+".$outArr[3][$index];*/

//проверка, что файл в формате jpeg/jpg
/*$filename = "somepic.jpeg";
print preg_match("/^([a-z0-9]+)\.jpe?g$/uis", $filename, $resultArr) 
		? "нашел ".$resultArr[1] : "тут ничего нет";*/

//3. делаем почту ссылками (замена с подстановкой)
//print preg_replace("/[a-z0-9\.\-_]+@[a-z0-9\.\-]+/uis", 
//	"<a href=\"mailto:$0\">$0</a>", $text);

//4. Поиск текста в тегах
$template = '<h1>hello, world</h1>
<p style="font-weight:bold;" class="delete_me"><span>это</span> первый абзац<    /p>
<p class="not_delete_me now plz">это второй абзац</p>
< p id="pitty_situation" class="its delete_me" style="color:red;">это третий абзац</p>';
	
preg_match_all("/<\s*([a-z0-9]+)[^>]*\bclass\s*=\s*\"([^\"]*)\"[^>]*>(.*?)<\s*\/\s*\\1[^>]*>/uis", $template, $resultArr);
print_r($resultArr);





	
	

