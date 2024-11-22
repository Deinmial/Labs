<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;

class Block extends Node
{
    public Node $compoundStatement;
    /** @var Node[] */
    public array $declarations;

    /**
     * @param Node[] $declarations
     */
    public function __construct(array $declarations, Node $compoundStatement)
    {
        $this->declarations = $declarations;
        $this->compoundStatement = $compoundStatement;
    }
}
