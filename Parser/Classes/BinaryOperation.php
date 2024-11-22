<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;
include 'Parser/Classes/Node.php';
use Pascal\Lexer\Token;

class BinaryOperation extends Node
{
    // Левый операнд
    public Node $left;
    // Бинарная операция (operator)
    public Token $op;
    // Правый операнд
    public Node $right;
    // Хранение информации о позиции операции в исходном коде
    public Token $token;

    public function __construct(Node $left, Token $op, Node $right)
    {
        $this->left = $left;
        $this->token = $this->op = $op;
        $this->right = $right;
    }
}
