<?php

declare(strict_types=1);

namespace Pascal\Lexer;

abstract class TokenType
{
    public const EOF = 'EOF';
    public const INTEGER = 'INTEGER';
    public const OPERATOR = 'OPERATOR';
    public const OPEN_PAREN = 'OPEN_PAREN';
    public const CLOSE_PAREN = 'CLOSE_PAREN';
    public const BEGIN = 'BEGIN';
    public const END = 'END';
    public const DOT = 'DOT';
    public const ID = 'ID';
    public const ASSIGNMENT = 'ASSIGNMENT';
    public const END_STATEMENT = 'END_STATEMENT';
    public const PROGRAM = 'PROGRAM';
    public const VAR = 'VAR';
    public const COLON = 'COLON';
    public const COMMA = 'COMMA';
    public const REAL = 'REAL';
    public const INTEGER_CONST = 'INTEGER_CONST';
    public const REAL_CONST = 'REAL_CONST';
    public const INTEGER_DIV = 'INTEGER_DIV';
    public const FLOAT_DIV = 'FLOAT_DIV';
}
