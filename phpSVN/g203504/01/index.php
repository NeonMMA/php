<?php
//вывод рекурсивной последовательности 
for ($index = 1; $index <= 10; $index++) {
    echo fibonacciLinear($index).' ';
}

//строка разделитель 
print "<br/>";

//вывод рекурсивной последовательности 
for ($index = 1; $index <= 10; $index++) {
    echo fibonacciRecursive($index).' ';
}

/**
 * Линейный поиск элемента последовательности Фибоначчи
 * 
 * @param integer $__index Номер члена последовательности, который нужно вернуть.
 * 
 * @return integer Вывод элемента последовательности Фибоначчи с заданным номером.
 */
function fibonacciLinear(int $__index)
{
    $firstNumber  = 1;
    $secondNumber = 1;
    $lastNumber   = 0;
    
    //проверка валидности индекса 
    if ($__index < 1) {
        return 0;
    }
    
    //вывод первых двух чисел последовательности без использования фунции
    elseif ($__index == 1 || $__index == 2) {
        return 1;
    }
    else{
        
        //нахождение каждого числа последовательности путем сложения двух предыдущих
        for ($index = 3; $index <= $__index; $index++) {
            $lastNumber   = $firstNumber + $secondNumber;
            $firstNumber  = $secondNumber;
            $secondNumber = $lastNumber;
        }
        
        //выовд найденного числа последовательности  *звуки ора в подушку*
        return $lastNumber;
    }
}

/**
 * Рекурсивный поиск элемента последовательности Фибоначчи
 * 
 * @param integer $__index Номер члена последовательности который нужно вернуть.
 * 
 * @return integer Вывод элемента последовательности Фибоначчи с заданным номером.
 */
function fibonacciRecursive(int $__index)
{
    //проверка валидности индекса 
    if ($__index < 1) {
        return 0;
    }
    
    //вывод первых двух чисел последовательности без использования фунции
    elseif ($__index == 1 || $__index == 2) {
        return 1;
    }
    else{
        return fibonacciRecursive($__index - 1) + fibonacciRecursive($__index - 2);
    }
}
