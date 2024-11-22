<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;

// Блок BEGIN и END
class Compound extends Node
{
    // Массив дочерних узлов, представляющих операторы внутри составного узла
    public array $childNode = [];

    public function __construct(array $childNode)
    {
        $this->childNode = $childNode;
    }
}
