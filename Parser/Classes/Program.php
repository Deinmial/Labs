<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;

class Program extends Node
{
    // Тело программы
    public Node $block;
    // Имя программы
    public string $name;

    public function __construct(string $name, Node $block)
    {
        $this->name = $name;
        $this->block = $block;
    }
}
