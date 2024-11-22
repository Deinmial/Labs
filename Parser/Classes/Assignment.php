<?php

declare(strict_types=1);

namespace Pascal\Parser\AST;
include 'Parser/Classes/BinaryOperation.php';
/**
 * Represents an assignment statement
 *
 * Left node represents the variable being assigned to.
 * Right node represents the value being assigned.
 */
class Assignment extends BinaryOperation
{
}
