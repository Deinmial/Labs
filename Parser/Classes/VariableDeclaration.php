<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;

class VariableDeclaration extends Node
{
    public Type $type;
    public Variable $var;

    public function __construct(Variable $var, Type $type)
    {
        $this->var = $var;
        $this->type = $type;
    }
}
