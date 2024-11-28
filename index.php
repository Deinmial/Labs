<?php

include 'Lexer/Lexer.php';
include 'Lexer/Token.php';
include 'Lexer/TokenType.php';
include 'Parser/Parser.php';
include 'Parser/PseudoCodeGenerator.php';
include 'Parser/Classes/Assignment.php';
include 'Parser/Classes/Block.php';
include 'Parser/Classes/Compound.php';
include 'Parser/Classes/NoOperation.php';
include 'Parser/Classes/Number.php';
include 'Parser/Classes/Program.php';
include 'Parser/Classes/VariableDeclaration.php';

use Pascal\Lexer\Lexer;
use Pascal\Parser\Parser;
use Pascal\Parser\PseudoCodeGenerator;

$code = file_get_contents('demo.pas');
echo "Source code:\n\n";
echo $code . "\n\n\n";


$lexer = new Lexer($code); // Создаем экземпляр класса Lexer
$tokens = $lexer->getTokens(); // Получаем токены из входной строки

print_r($tokens); // Выводим массив токенов

echo "\n\n\n";

$parser = new Parser($tokens); // Создаем экземпляр класса Parser
$codes = $parser->parse(); // Парсим

print_r($codes); // Выводим массив парсинга

echo "\n\n\n";

$generator = new PseudoCodeGenerator(); // Создаем экземпляр класса PseudoCodeGenerator
$pseudoCode = $generator->generate($codes); // Генерируем псевдокод

echo $pseudoCode; // Выводим псевдокод
