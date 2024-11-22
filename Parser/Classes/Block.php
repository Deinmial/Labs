<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;

class Block extends Node
{
    // Составные операторы
    public Node $compoundStatement;
    // Оюъявление переменных или типов
    public array $declarations;

    public function __construct(array $declarations, Node $compoundStatement)
    {
        $this->declarations = $declarations;
        $this->compoundStatement = $compoundStatement;
    }
}
