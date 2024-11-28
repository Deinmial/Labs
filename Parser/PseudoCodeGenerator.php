<?php

namespace Pascal\Parser;

use Exception;

class PseudoCodeGenerator {
    public function generate($node) {
        if ($node instanceof \Pascal\Parser\Classes\Program) {
            return $this->generate($node->block);
        } elseif ($node instanceof \Pascal\Parser\Classes\Block) {
            $declarations = array_map([$this, 'generate'], $node->declarations);
            $statements = array_map([$this, 'generate'], $node->compoundStatement->childNode);
            return implode("\n", array_merge($declarations, $statements));
        } elseif ($node instanceof \Pascal\Parser\Classes\Compound) {
            $statements = array_map([$this, 'generate'], $node->childNode);
            return implode("\n", $statements);
        } elseif ($node instanceof \Pascal\Parser\Classes\VariableDeclaration) {
            return "DECLARE {$node->var->value} AS {$node->type->value}";
        } elseif ($node instanceof \Pascal\Parser\Classes\Assignment) {
            $right = $this->generate($node->right);
            return "{$node->left->value} := {$right}";
        } elseif ($node instanceof \Pascal\Parser\Classes\BinaryOperation) {
            $left = $this->generate($node->left);
            $right = $this->generate($node->right);
            return "({$left} {$node->op->value} {$right})";
        } elseif ($node instanceof \Pascal\Parser\Classes\Number) {
            return $node->value;
        } elseif ($node instanceof \Pascal\Parser\Classes\Variable) {
            return $node->value;
        } elseif ($node instanceof \Pascal\Parser\Classes\NoOperation) {
            return "";
        } else {
            throw new Exception("Unknown node type: " . get_class($node));
        }
    }
}
