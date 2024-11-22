<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;

class Program extends Node
{
    public Node $block;
    public string $name;

    public function __construct(string $name, Node $block)
    {
        $this->name = $name;
        $this->block = $block;
    }
}
