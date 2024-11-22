<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;

/**
 * Represents a `BEGIN ... END` block
 */
class Compound extends Node
{
    /** @var Node[] */
    public array $children = [];

    /**
     * @param Node[] $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }
}
