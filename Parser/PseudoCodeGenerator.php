<?php

namespace Pascal\Parser;

use Exception;

class PseudoCodeGenerator {
    // Генерируем псевдокод
    public function generate($node) {
        // Если узел является экземпляром класса, то генерируется псевдокод для его блока (block)
        if ($node instanceof \Pascal\Parser\Classes\Program) {
            return $this->generate($node->block);

        // Если узел является экземпляром класса, то генерируется псевдокод для всех объявлений переменных (declarations)
        // и всех операторов (compoundStatement->childNode)
        } elseif ($node instanceof \Pascal\Parser\Classes\Block) {
            $declarations = array_map([$this, 'generate'], $node->declarations);
            $statements = array_map([$this, 'generate'], $node->compoundStatement->childNode);
            return implode("\n", array_merge($declarations, $statements));

        // Если узел является экземпляром класса, то генерируется псевдокод для всех дочерних узлов (childNode)
        } elseif ($node instanceof \Pascal\Parser\Classes\Compound) {
            $statements = array_map([$this, 'generate'], $node->childNode);
            return implode("\n", $statements);

        // Если узел является экземпляром класса, то генерируется строка вида DECLARE variable AS type
        } elseif ($node instanceof \Pascal\Parser\Classes\VariableDeclaration) {
            return "DECLARE {$node->var->value} AS {$node->type->value}";

        // Если узел является экземпляром класса, то генерируется строка вида variable := expression
        } elseif ($node instanceof \Pascal\Parser\Classes\Assignment) {
            // Правая часть генерируется рекурсивно
            $right = $this->generate($node->right);
            return "{$node->left->value} := {$right}";

        // Если узел является экземпляром класса, то генерируется строка вида
        // (left_expression operator right_expression)
        } elseif ($node instanceof \Pascal\Parser\Classes\BinaryOperation) {
            $left = $this->generate($node->left);
            $right = $this->generate($node->right);
            return "({$left} {$node->op->value} {$right})";

        // Если узел является экземпляром класса, то возвращается его значение (число)
        } elseif ($node instanceof \Pascal\Parser\Classes\Number) {
            return $node->value;

        // Если узел является экземпляром класса, то возвращается его значение (переменные)
        } elseif ($node instanceof \Pascal\Parser\Classes\Variable) {
            return $node->value;

        // Если узел является экземпляром класса, то возвращается пустая строка
        } elseif ($node instanceof \Pascal\Parser\Classes\NoOperation) {
            return "";

        // Исключение
        } else {
            throw new Exception("Unknown node type: " . get_class($node));
        }
    }
}
