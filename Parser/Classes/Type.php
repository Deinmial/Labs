<?php

declare(strict_types=1);

namespace Pascal\Parser\Classes;

use Pascal\Lexer\Token;

class Type extends Node
{
    // Тип токена
    public Token $token;
    // Строковое значение
    public $value;

    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->value = $token->value;
    }
}
