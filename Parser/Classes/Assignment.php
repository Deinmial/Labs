<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;
include 'Parser/Classes/BinaryOperation.php';

// Представляет оператор присваивания
// Левый узел представляет присваиваемую переменную
// Правый узел представляет присваиваемое значение

class Assignment extends BinaryOperation
{
}
