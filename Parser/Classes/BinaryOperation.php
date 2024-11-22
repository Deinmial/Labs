<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;
include 'Parser/Classes/Node.php';
use Pascal\Lexer\Token;

class BinaryOperation extends Node
{
    public Node $left;
    public Token $op;
    public Node $right;
    public Token $token;

    public function __construct(Node $left, Token $op, Node $right)
    {
        $this->left = $left;
        $this->token = $this->op = $op;
        $this->right = $right;
    }
}
