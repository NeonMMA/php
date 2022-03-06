<?php
// я лох, я неправильно прписал ширину 
//массив выходных (читать и менять такой вид намного удобнее. Из того массива, что в условии, можно получить регуляркой по точке)
$holidaysArr = array(
    1  => array(1, 2, 3, 4, 5, 6, 7), 
    2  => array('23'),
    3  => array(8),
    4  => array(1, 14),
    
    // грустно живем
    5  => array(1, 9),
    6  => array(12),
    7  => array(),
    8  => array(),
    9  => array(1),
    
    // праздников мало
    10 => array(),
    11 => array(4),
    12 => array(20, 31),
);

// номера дней недели() + количество дней в неделе
const saturday   = 5;
const sunday     = 6;
const dayPerWeek = 7;

// открыли html для записи
$html = "";

// если пришел POST, то выводим только часть календаря
if (isset($_POST["year"]) || isset($_POST["month"])) {
    $year  = isset($_POST["year"]) ? preg_replace("/[^0-9]/xuis", "", $_POST["year"]) : "";
    $month = isset($_POST["month"]) ? preg_replace("/[^0-9]/xuis", "", $_POST["month"]) : "";
    
    // проверка диапазона для года
    if ($year == "" || !($year >= 2005 && $year <= 2033 )) {
        $year = date("Y");
    }
    
    // проверка диапазона для месяца
    if ($month == "" || !($month >= 1 && $month <= 12 )) {
        $month = date("m");
    }
    
    // в див выводим только таблицу
    $html .= createCalendar($year, $month, $holidaysArr[$month], ($month == date("m") && $year == date("Y")));
}

// если не пришел POST с js выводим полную форму
else {
    $html .= createHeaderHtml();
    $html .= createSelectHtml();
    
    // кидаем календарь от текущей даты
    $html .= createCalendar(date("Y"), date("m"), $holidaysArr[date("m")], true);
    $html .= "</div></form></body></html>";
}

//вывод кода
echo $html;

/**
 * Head страницы.
 * 
 * @return string Header
 */
function createHeaderHtml()
{
    return "<!DOCTYPE HTML>"
    ."<html><head>"
    ."<script src=\"js.js\"></script>"
            
    //jquery
    ."<script src=\"jquery-3.2.1.min.js\"></script>"
    ."<style>@import url(css.css)</style>"
    ."</head><body>";
}

/**
 * HTML форма выбора 
 * 
 * @return string Форма select
 */
function createSelectHtml()
{
    // создаем оption для лет
    $yearSelect = "";
    for ($index = 2005; $index < 2033; $index++) {
        $yearSelect .= "<option ".(($index == date("Y")) ? "selected" : "").">".$index."</option>";
    }
    
    // посмотрел, понял что реально говнокод был
    $monthSelect = "";
    for ($month = 1; $month <= 12; $month++) {
        $monthSelect .= "<option value=\"".$month."\"".(($month == date("n")) ? "selected" : "").">"
        .date("F", strtotime("01.".$month.".2000"))
        ."</option>";
    }
    
    //вывод формы
    return "<form align=\"center\">"
    ."<select id=\"year\" onchange=\"update()\">"
    .$yearSelect
    ."</select>"

    //select для месяца
    ."<select id=\"month\" onchange=\"update()\">"
    .$monthSelect
    ."</select>"
    ." <div id = \"calendar\" align=\"center\">";
}

/**
 * Создание кода календаря.
 * 
 * @param integer $__year       Год.
 * @param integer $__month      Месяц.
 * @param array   $__holydayArr Массив выходных этого месяца.
 * @param boolean $__current    Сегодня.
 * 
 * @return string Html код.
 */
function createCalendar(int $__year, int $__month, array $__holydayArr, bool $__current)
{    
    // количество дней в месяце
    $dayCounter = date("t", strtotime("1-".$__month."-".$__year));
    
    // номер первого дня недели
    $firstDay = date("w", strtotime("1-".$__month."-".$__year));
    
    // создаем массив дней, в ячейке лежит число
    $dayArr = range(1, $dayCounter); 
    
    // сколько в начале пустых дней
    $firsDayCount = ($firstDay - 1) % dayPerWeek;
    if ($firsDayCount == -1) {
        $firsDayCount = sunday;
    }
    
    // пока не очень понимаю зачем, но вот количество строк
    $weekCount   = ceil((count($dayArr) + $firsDayCount) / dayPerWeek);
    $maxDayCount = $weekCount * dayPerWeek;
    
    //сколько ячеек нужно добавить
    $lastDayCount = $maxDayCount - count($dayArr) - $firsDayCount;
    
    // открыли таблицу
    $html = "<table><tr>";
    
    // воскресенье
    $sunday = "01.08.2021";
    
    // cоставляем строку дней недели 
    for ($weekday = 1; $weekday <= dayPerWeek; $weekday++) {
        $html .= "<th>"
        .date("D", strtotime($sunday) + $weekday * 24 * 60 * 60)
        ."</th>";
    }
    
    // зкрыли строчку дней недели
    $html .= "</tr>";
    
    // так и не понял зачем считать строчки, поэтому пусть от них зависит вывод
    $dayIndex = 0;
    while ($dayIndex < $maxDayCount) {
        
        // если надо - открываем строчку
        if ($dayIndex % dayPerWeek == 0) {
            $html .= "<tr>";
        }
        
        // пустые ячейки до и после массива
        if ($dayIndex < $firsDayCount || $dayIndex >= count($dayArr) + $firsDayCount) {
            $html .= "<td class=\"grey\"></td>";
        }
        else{
            
            // собираем ячейку
            $html .= "<td class=\""

            # выбираем класс для ячейки
            .getClass(
                date("N", mktime(1, 1, 1, $__month, $dayIndex - $firsDayCount, $__year)), 
                $dayArr[$dayIndex - $firsDayCount], 
                $__holydayArr, 
                $__current
            )
                    
            //
            ."\">"
            .$dayArr[$dayIndex - $firsDayCount]
            ."</td>";
        }
        
        // по необходимости закрываем строчку
        if ($dayIndex % dayPerWeek == sunday) {
            $html .= "</tr>";
        }
        
        // увеличиваем счетчик дня
        $dayIndex++;
    }
    
    // закрыли табличку
    $html .= "</table>";
    
    // вывод кода
    return $html;
}

/**
 * Определение класса для дня.
 * 
 * @param integer $__key        День недели.
 * @param integer $__day        День месяца.
 * @param array   $__holydayArr Массив праздников этого месяца.
 * @param boolean $__current    Сегодня.
 * 
 * @return string Html код.
 */
function getClass(int $__key, int $__day, array $__holydayArr, bool $__current)
{
    // задаем класс, первоначально у обычного дня его нет.
    $class = "";

    // проверяем на субботу и воскресенье 
    if ($__key % dayPerWeek == saturday) {
        $class = "pink";
    }
    elseif ($__key % dayPerWeek == sunday) {
        $class = "purple";
    }

    // праздник красный 
    if (array_search($__day, $__holydayArr) !== false) {
        $class = "red";
    }

    // текущий день зеленый
    if ($__day == date("j") && $__current) {
        $class = "green";
    }
    
    // возвращаем готовый класс для ячейки
    return $class;
}
