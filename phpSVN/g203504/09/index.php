<?php

//подключаем класс шаблона
require_once 'Template.php';

// произвольный массив машин
$carsArr = array(
    array( 'manufactor' => 'mazda', 'model' => 'cx-5', "hp" => 80),
    array( 'manufactor' => 'mazda', 'model' => 'q', "hp" => 255),
    array( 'manufactor' => 'bmw', 'model' => 'cx-55', "hp" => 260),
    array( 'manufactor' => 'q', 'model' => 'x5', "hp" => 100),
    
    //
    array( 'manufactor' => 'x', 'model' => '5', "hp" => 160),
    array( 'manufactor' => 'y', 'model' => '6', "hp" => 170),

    
    //
    array( 'manufactor' => 'mazda', 'model' => 'cx-3', "hp" => 50),
    array( 'manufactor' => 'mazda', 'model' => 'cx-5', "hp" => 280),
);

// сортируем по лошадям
usort($carsArr, 'hpCompare');

// шаг объединения по лс
$step = 50;

// всего записей
$counter = count($carsArr);

//максимальная мощность машины из массива
$lastHp = $carsArr[$counter - 1];

// переменная-массив в которой будут лежать записи определенного диапазона лс
$horsePowerArr = array();

// массив подмассивов по лс
$fullArr = array();

// разбиваем на подмассивы по лошадям  
$index      = 0;
$horsePower = 0;

// идем пока не кончатся записи или не дойдем до последней(смотрим по max лс)
while ($index < $counter && $horsePower <= $lastHp) {
    
    // если лошадиные силы лежат в выбранном диапазоне -> кидаем в подмассив 
    if ($carsArr[$index]["hp"] >= $horsePower && $carsArr[$index]["hp"] < ($horsePower + $step)) {
        $horsePowerArr[] = $carsArr[$index];
        $index++;
        
        // на последнем проходе добавляем в массив оставшиеся элементы
        if ($index == $counter) {
            $fullArr[$horsePower] = $horsePowerArr;
            $horsePowerArr        = array();
            $horsePower           = $horsePower + $step; 
        }
    }
    
    // если нет - данная строка уже не находится в созданном подмассиве, этот подмассив кидаем в главный массив $fullArr
    else {

        // если новый элемент не в диапазоне -> все что было кидаем в массив
        $fullArr[$horsePower] = $horsePowerArr;
        $horsePowerArr        = array();
        $horsePower           = $horsePower + $step;  
    }
}

// нужно разделить на подмассивы по маркам
foreach ($fullArr as $hpRange => $modelArr) {
    
    //количество машин в подмассиве, который получили при разбиении по лс.
    $modelCounter = count($modelArr);
    $autoArr      = array();

    // если машины есть в этом диапазоне, то выделяем подмассивы по маркам машин
    if ($modelCounter != 0) {
        
        // проходим по подмассивам, которые получили разбиением по лс
        for ($index = 0; $index < $modelCounter; $index++) {
            
            // добавляем в новый массив по ключу "марка" записи с моделью и лс
            $autoArr[$modelArr[$index]["manufactor"]][] = array(
                                                           "model" => $modelArr[$index]["model"], 
                                                           "hp"    => $modelArr[$index]["hp"]
                                                       );
        }

        //car и auto в принципе синонимы, до этого была нарушена логика, что был подмассив марок, а массив автомобилей
        //кидаем в главный массив блоки по лс, которые разбиты на блоки по маркам 
        $resAutoArr[] = array("HpRange" => $hpRange."-".($hpRange + $step), "cars" => $autoArr);
    }
    
    // если нет - пишем диапазон и пустой массив в массиве вместо записей о машинах
    else {
        $resAutoArr[] = array("HpRange" => $hpRange."-".($hpRange + $step), "cars" => array(array()));
    }
}

// нужно еще раз пройти по подмассивам $resAutoArr, тк только теперь мы точно знаем количество элементов в этих подмассивах 
foreach ($resAutoArr as $key => $value) {
    
    // спускаемся до уровня cars, считаем для каждой марки кол-во моделей, вписываем сам массив моделей и пишем марку в одно из значений
    foreach ($value["cars"] as $mark => $value) {
        $resAutoArr[$key]["cars"][$mark] = array(
                                            "mark"        => $mark,
                                            "modelsCount" => count($resAutoArr[$key]["cars"][$mark]),
                                            "models"      => $resAutoArr[$key]["cars"][$mark]
                                        );  
    }
}

//шаг цвета, 255 - максимальное значение цвета в ргб
$colorRange = floor(255 / count($resAutoArr)); 
        
// убрать марку как ключ и поставить туда числа
$resArr = array();
foreach ($resAutoArr as $colorKey => $tab) {
    $carsResArr = array();
    
    // до этого ключом для подмассива марки была марка, меняем на автоинкремент
    foreach ($tab["cars"] as $key => $value) {
        $carsResArr[] = $value;
    }
    
    // в главный массив вносим все из старого массива поблочно и добавляем переменные цвета
    $resArr[] = array(
                 "firstColor"  => 255 - $colorKey * $colorRange, 
                 "secondColor" => $colorKey * $colorRange, 
                 "HpRange"     => $tab["HpRange"], 
                 "cars"        => $carsResArr
             );
}

// пишем конечный массив для шаблона и берем html код из файла
$templateDataArr = array("carsTable" => $resArr);
$str             = file_get_contents("template.html");

// выводим файл
print Template::build($str, $templateDataArr);  

/**
 * Функция сравнения подмассивов по лс.
 * 
 * @param array $__first  Первый элемент массива.
 * @param array $__second Второй элемент массива.
 * 
 * @return boolean
 */
function hpCompare(array $__first, array $__second)
{
    return $__first["hp"] > $__second["hp"]; 
}

/**
 * Функция сравнения подмассивов по марке.
 * 
 * @param array $__first  Первый элемент массива.
 * @param array $__second Второй элемент массива.
 * 
 * @return boolean
 */
function nameCompare(array $__first, array $__second)
{
    return $__first["manufactor"] > $__second["manufactor"]; 
}
