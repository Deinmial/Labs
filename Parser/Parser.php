<?php

declare(strict_types=1);

namespace Pascal\Parser;

use Exception;
use Pascal\Lexer\{Token, TokenType};
use Pascal\Parser\Classes\{
    Assignment,
    BinaryOperation,
    Block,
    Compound,
    Node,
    NoOperation,
    Number,
    Program,
    Type,
    Variable,
    VariableDeclaration
};

class Parser
{
    protected int $position = 0;
    protected array $tokens;
    protected ?Token $currentToken = null;
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->currentToken = $this->getNextToken();
    }

    // Следующий токен из списка
    public function getNextToken(): ?Token
    {
        // Есть ли токен на текущей позиции
        if (!isset($this->tokens[$this->position])) {
            return null;
        }

        return $this->currentToken = $this->tokens[$this->position++];
    }

    // Парсинг факторов (разбор отдельных компонентов выражений)
    public function factor(): Node
    {
        // Сохраняем текущий токен
        $token = $this->currentToken;

        // Если токена нет
        if (null === $token) {
            throw new Exception('Missing expected token');
        }

        // Типы токенов
        switch ($token->type) {
            case TokenType::INTEGER_CONST:
                $this->analyze(TokenType::INTEGER_CONST);
                return new Number($token);
            case TokenType::REAL_CONST:
                $this->analyze(TokenType::REAL_CONST);
                return new Number($token);

            // Скобки
            case TokenType::OPEN_PAREN:
                // Парсим выражение при помощи рекурсивного вызова
                $this->analyze(TokenType::OPEN_PAREN);
                $node = $this->expression();
                $this->analyze(TokenType::CLOSE_PAREN);
                return $node;

            // Операторы
            case TokenType::OPERATOR:
                if ('+' === $token->value) {
                    $this->analyze(TokenType::OPERATOR);
                    // Узел двоичной операции с операндом 0
                    return new BinaryOperation(
                        new Number(new Token(TokenType::INTEGER_CONST, '0')),
                        $token,
                        $this->factor()
                    );
                } else {
                    $this->analyze(TokenType::OPERATOR);
                    return new BinaryOperation(
                        new Number(new Token(TokenType::INTEGER_CONST, '0')),
                        $token,
                        $this->factor()
                    );
                }
            default:
                return $this->variable();
        }
    }

    // Парсинг терм в выражениях (Блоки выражения, включающие в себя константы, переменные, выражения в скобках и т.д.)
    public function term(): Node
    {
        // Парсим первый фактор в терме
        $node = $this->factor();

        // Пока текущий токен не равен null и его тип соответствует типу оператора (TokenType::OPERATOR) или целочисленного деления (TokenType::INTEGER_DIV),
        // а его значение равно '', / или div
        while (
            null !== $this->currentToken &&
            ((TokenType::OPERATOR === $this->currentToken->type &&
                in_array($this->currentToken->value, ['*', '/'])) ||
             (TokenType::INTEGER_DIV === $this->currentToken->type))
        ) {
            // Текущая переменная в токене
            $token = $this->currentToken;

            // Анализируем текущий токен, продвигаясь к следующему
            if (TokenType::INTEGER_DIV === $this->currentToken->type) {
                $this->analyze(TokenType::INTEGER_DIV);
            } else {
                $this->analyze(TokenType::OPERATOR);
            }

            // Узел BinaryOperation с левым операндом $node, оператором $token и правым операндом, который является результатом парсинга следующего фактора.
            // Присваиваем узел BinaryOperation переменной $node.
            $node = new BinaryOperation($node, $token, $this->factor());
        }

        return $node;
    }

    // Парсинг выажения
    public function expression(): Node
    {
        // Парсинг первого терма
        $node = $this->term();

        // Пока текущий токен не равен null и его тип соответствует типу оператора (TokenType::OPERATOR), а его значение равно + или -.
        while (
            null !== $this->currentToken &&
            TokenType::OPERATOR === $this->currentToken->type &&
            in_array($this->currentToken->value, ['+', '-'])
        ) {
            // Сохраняем текущий токен
            $token = $this->currentToken;
            // Анализ оператора
            $this->analyze(TokenType::OPERATOR);
            //  Создается узел BinaryOperation с левым операндом $node, оператором $token и правым операндом,
            //  который является результатом парсинга следующего терма.
            //  Присваивает узел BinaryOperation переменной $node.
            $node = new BinaryOperation($node, $token, $this->term());
        }

        return $node;
    }

    // Создание и возврат узла дерева абстрактного синтаксиса
    public function parse(): Node
    {
        // Парсим узел Program
        $node = $this->program();

        // После того, как узел Program был создан, функция проверяет, существуют ли какие-либо дополнительные токены в потоке после конца программы.
        // Если текущий токен не равен маркеру конца файла (TokenType::EOF), это означает, что исходный код содержит дополнительный код после правильной программы,
        // что может указывать на ошибку.
        if (null !== $this->currentToken && TokenType::EOF !== $this->currentToken->type) {
            throw new Exception('Unexpected end');
        }

        return $node;
    }

    // Пасрим узел program (Вся программа на ЯП)
    public function program(): Node
    {
        // Текущий токен - PROGRAM
        $this->analyze(TokenType::PROGRAM);

        // Парсим имя программы в $programName
        $variable = $this->variable();
        $programName = (string) $variable->value;

        // Текущий токен - ;
        $this->analyze(TokenType::END_STATEMENT);

        // Парсим block - тело программы (все операторы и объявления)
        $block = $this->block();
        // Создаем узел Program с именем программы и блоком тела
        $node = new Program($programName, $block);

        // Текущий токен - .
        $this->analyze(TokenType::DOT);

        return $node;
    }

    // Обработка составных операторов
    public function compoundStatement(): Node
    {
        // Анализируем токен начала составного оператора (BEGIN)
        $this->analyze(TokenType::BEGIN);
        // Парсинг списка операторов внутри составного
        $nodes = $this->statementList();
        // Анализируем токен конца составного оператора (END)
        $this->analyze(TokenType::END);
        // Создаем узел Compound: Функция создает узел дерева абстрактного синтаксиса типа Compound. Узел Compound
        // представляет собой составной оператор и содержит список дочерних узлов, которые представляют отдельные операторы в списке операторов.
        $root = new Compound($nodes);
        return $root;
    }

    // Парсинг списка операторов внутри составного
    public function statementList(): array
    {
        // Парсинг первого оператора в списке
        $node = $this->statement();
        $results = [$node];

        // Пока текущий токен не равен null и его тип соответствует типу конца оператора (TokenType::END_STATEMENT).
        while (null !== $this->currentToken && TokenType::END_STATEMENT === $this->currentToken->type) {
            // Анализируем токен
            $this->analyze(TokenType::END_STATEMENT);
            // Парсим следующий оператор в списке
            $results[] = $this->statement();
        }

        // Является ли текущий токен идентификатором (TokenType::ID)
        if (null !== $this->currentToken && TokenType::ID === $this->currentToken->type) {
            throw new Exception();
        }

        return $results;
    }

    // Разбор синтаксиса операторов
    public function statement(): Node
    {
        // Пустой узел дерева
        $node = $this->empty();

        // Если текущий токен равен null, то в исходном коде больше нет токенов
        if (null === $this->currentToken) {
            return $node;
        }

        // Если текущий токен - BEGIN, то оператор является составным оператором
        // Функция переходит к обработке составного оператора compoundStatement()
        if (TokenType::BEGIN === $this->currentToken->type) {
            $node = $this->compoundStatement();
        // Оператор присваивания
        // Обработка операторов присваивания - assignmentStatement()
        } elseif (TokenType::ID === $this->currentToken->type) {
            $node = $this->assignmentStatement();
        }

        return $node;
    }

    // Разбор операторов присваивания
    public function assignmentStatement(): Node
    {
        if (null === $this->currentToken) {
            throw new Exception('Missing expected token');
        }

        // Разбираем левую часть оператора
        $left = $this->variable();

        // Анализируем токен присваивания
        $token = $this->currentToken;
        $this->analyze(TokenType::ASSIGNMENT);

        // Разбираем правую часть (выражение)
        $right = $this->expression();
        // Создаем узел синтаксического дерева для оператора присваивания, левую и правую части, а также токен присваивания
        return new Assignment($left, $token, $right);
    }

    // Разбор синтаксиса переменных
    public function variable(): Node
    {
        if (null === $this->currentToken) {
            throw new Exception('Missing expected token');
        }

        // Создаем узел синтаксического дерева для переменной, используя текущий токен
        $node = new Variable($this->currentToken);
        // Анализ и переход к следующему токену
        $this->analyze(TokenType::ID);
        return $node;
    }

    // Тело программы
    public function block(): Node
    {
        // Парсим любые объявления переменных в теле
        $declarations = $this->declarations();
        // Парсим составные операторы
        $compoundStatement = $this->compoundStatement();
        // Узел синтаксического дерева для тела
        return new Block($declarations, $compoundStatement);
    }

    // Разбор объявленных переменных
    public function declarations(): array
    {
        $result = [];

        // Существует ли объявление переменных, анализируя токен VAR
        if (null !== $this->currentToken && TokenType::VAR === $this->currentToken->type) {
            // Анализ и переход на следующий токен
            $this->analyze(TokenType::VAR);
            // Пока текущий токен не равен null и является идентификатором
            while (null !== $this->currentToken && TokenType::ID === $this->currentToken->type) {
                // Парсим объявления отдельных переменных
                $result = array_merge($result, $this->variableDeclarations());
                // Анализируем токен ;
                $this->analyze(TokenType::END_STATEMENT);
            }
        }
        return $result;
    }

    // Объявление переменных
    public function variableDeclarations(): array
    {
        if (null === $this->currentToken) {
            throw new Exception('Unexpected missing token');
        }

        // Узел переменной для текущего токена, который представляет имя первой объявляемой переменной
        $varNodes = [new Variable($this->currentToken)];
        $this->analyze(TokenType::ID);

        // Пока текущий токен не равен null и тип текущего токена равен ','
        while (null !== $this->currentToken && TokenType::COMMA === $this->currentToken->type) {
            // Анализ токена запятой и узел переменной для текущего токена, который представляет имя следующей объявляемой переменной
            $this->analyze(TokenType::COMMA);
            $varNodes[] = new Variable($this->currentToken);
            $this->analyze(TokenType::ID);
        }

        // Двоеточие
        $this->analyze(TokenType::COLON);
        // Спецификация типов
        $typeNode = $this->typeSpecification();
        $result = [];

        // Узлы объявления переменных для каждой разобранной переменной, объединяя узлы переменных со спецификацией типа
        foreach ($varNodes as $varNode) {
            $result[] = new VariableDeclaration($varNode, $typeNode);
        }

        return $result;
    }

    // Спецификация типов
    public function typeSpecification(): Node
    {
        // Ожидает токены INTEGER и REAL
        if (null === ($token = $this->currentToken)) {
            throw new Exception('Missing type declaration');
        }

        if (TokenType::INTEGER === $token->type) {
            $this->analyze(TokenType::INTEGER);
        } else {
            $this->analyze(TokenType::REAL);
        }

        return new Type($token);
    }

    // Пустая операция
    public function empty(): Node
    {
        return new NoOperation();
    }

    // Обработка синтаксических конструкций
    protected function analyze(string $type): void
    {
        // Существует ли токен и соответствует ли ожидаемому типу
        if (null !== $this->currentToken && $this->currentToken->type !== $type) {
            throw new Exception(sprintf(
                'Unexpected current token type. Expected %s but found %s',
                $type,
                $this->currentToken->type
            ));
        }
        // Следующий токен
        $this->getNextToken();
    }
}
