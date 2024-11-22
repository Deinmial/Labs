<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;

// Декларация переменных
class VariableDeclaration extends Node
{
    // Тип
    public Type $type;
    // Сама переменная
    public Variable $var;

    public function __construct(Variable $var, Type $type)
    {
        $this->var = $var;
        $this->type = $type;
    }
}
