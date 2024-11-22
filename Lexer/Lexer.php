<?php

declare(strict_types=1);

namespace Pascal\Lexer;


use Exception;

class Lexer
{
    // Буквы независимо от регистра
    public const REGEX_LETTERS = '/[a-z]/i';
    // Буквы и цифры независимо от регистра
    public const REGEX_LETTERS_NUM = '/[a-z0-9]/i';
    // Цифры
    public const REGEX_NUMERIC = '/[0-9]/';
    // Пробелы
    public const REGEX_SPACE = '/\s/';

    // Код
    protected string $text;

    // Устанавливается на 0, указывая на текущее положение в исходном коде
    protected int $position = 0;

    // Текущий токен
    protected ?Token $currentToken = null;

    // Первый символ из входной строки 
    protected ?string $currentChar;

    // Словарь, содержащий ключевые слова языка и соответствующие им токены
    protected array $reservedKeywords;

    // Инициализация переменных
    public function __construct(string $text)
    {
        $this->text = $text;
        $this->currentChar = $this->text[$this->position];
        $this->reservedKeywords = [
            'PROGRAM' => new Token(TokenType::PROGRAM, 'PROGRAM'),
            'VAR' => new Token(TokenType::VAR, 'VAR'),
            'DIV' => new Token(TokenType::INTEGER_DIV, 'DIV'),
            'INTEGER' => new Token(TokenType::INTEGER, 'INTEGER'),
            'REAL' => new Token(TokenType::REAL, 'REAL'),
            'BEGIN' => new Token(TokenType::BEGIN, 'BEGIN'),
            'END' => new Token(TokenType::END, 'END'),
        ];
    }

    // Все токены исходного кода
    public function getTokens(): array
    {
        // Хранение токенов
        $result = [];
        // Первый токен из кода
        $this->currentToken = $this->getNextToken();

        // Продолжается, пока тип текущего токена не станет `TokenType::EOF` (конец файла)
        while (TokenType::EOF !== $this->currentToken->type) {
            // Добавляем токен в массив
            $result[] = $this->currentToken;
            // Получаем следующий токен из исходного кода и сохраняем его в currentToken
            $this->currentToken = $this->getNextToken();
        }

        // Добавляем последний токен (конец файла)
        $result[] = $this->currentToken;

        return $result;
    }

    // Перемещение по исходному коду
    public function moveCode(): ?string
    {
        // +1 - переходим к следующему символу
        $this->position++;

        // Проверяем, вышел ли position за пределы длины исходного кода
        if ($this->position > mb_strlen($this->text) - 1) {
            // null - конец файла
            $this->currentChar = null;
        } else {
            // Устанавливаем currentChar в символ исходного кода по индексу position
            $this->currentChar = $this->text[$this->position];
        }

        return $this->currentChar;
    }

    // Пропуск пробелов
    public function skipWhitespace(): void
    {
        // currentChar не конец файла и текущий символ REGEX_SPACE - пробелы
        while (null !== $this->currentChar && preg_match((string) self::REGEX_SPACE, $this->currentChar)) {
            // Переход к следующему символу в исходном коде
            $this->moveCode();
        }
    }

    // Пропуск комментов
    public function skipComment(): void
    {
        while ('}' !== $this->currentChar) {
            $this->moveCode();
        }
        $this->moveCode();
    }

    // Возвращает (многозначное) число, полученное из входных данных
    public function number(): Token
    {
        // Цифровые константы
        $result = '';

        // Пока не конец файла и не число (Сбор цифр до точки)
        while (null !== $this->currentChar && preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
            // Добавляем текущий символ в $result
            $result .= $this->currentChar;
            // Переход к след. символу    
            $this->moveCode();
        }

        // Если не точка, то INTEGER
        if ('.' !== $this->currentChar) {
            return new Token(TokenType::INTEGER_CONST, $result);
        }

        // Добавляем точку и собираем их после точки
        $result .= $this->currentChar;
        $this->moveCode();

        // Пока не конец файла и не число (Сбор цифр после точки)
        while (null !== $this->currentChar && preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
            $result .= $this->currentChar;
            $this->moveCode();
        }
        // Присваиваем токен REAL_CONST
        return new Token(TokenType::REAL_CONST, $result);
    }

    // Получение следующего лексического токена из исходного кода и return соответствующий токен, представляющий этот токен
    public function getNextToken(): Token
    {
        // Пока не пустая строка
        while (null !== $this->currentChar) {

            // Пропуск проеблов
            if (preg_match((string) self::REGEX_SPACE, $this->currentChar)) {
                $this->skipWhitespace();
                continue;
            }

            // Идентифицируем числовую константу (REAL или INTEGER)
            if (preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
                $number = $this->number();
                return $number;
            }

            // Соответсвующий токен
            if ('+' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::OPERATOR, '+');
            }

            if ('-' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::OPERATOR, '-');
            }

            if ('*' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::OPERATOR, '*');
            }

            if ('/' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::OPERATOR, '/');
            }

            if ('(' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::OPEN_PAREN, '(');
            }

            if (')' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::CLOSE_PAREN, ')');
            }

            // Если REGEX_LETTERS, то идентифицируем зарезервированное слово или идентификатор
            if (preg_match(self::REGEX_LETTERS, $this->currentChar)) {
                return $this->id();
            }

            // Оператор присваивания
            if (':' === $this->currentChar && '=' === $this->choose()) {
                $this->moveCode();
                $this->moveCode();
                return new Token(TokenType::ASSIGNMENT, ':=');
            }

            if (';' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::END_STATEMENT, ';');
            }

            if ('.' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::DOT, '.');
            }

            if (':' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::COLON, ':');
            }

            if ('/' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::FLOAT_DIV, '/');
            }

            if (',' === $this->currentChar) {
                $this->moveCode();
                return new Token(TokenType::COMMA, ',');
            }

            // Комментарии
            if ('{' === $this->currentChar) {
                $this->moveCode();
                $this->skipComment();
                continue;
            }

            // Исключение для неизвестного токена
            throw new Exception(sprintf('Unknown token: %s', (string) $this->currentChar));
        }

        return new Token(TokenType::EOF);
    }

    // Следующий символ, не перемещая позицию
    public function choose(): ?string
    {
        // Позиция следующего символа в исходном коде и текущая позиция
        $choosePosition = $this->position + 1;

        // Выходит ли $choosePosition за пределы длины исходного кода
        if ($choosePosition > mb_strlen($this->text) - 1) {
            // Конец кода
            return null;
        }
        // Если не выходит, то возвращаем символ кода по индексу
        return $this->text[$choosePosition];
    }

    // Идентифицируем и возвращаем токен
    public function id(): Token
    {
        // Идентификатор
        $result = '';

        // Пока не конец и буквы с цифрами
        while (
            null !== $this->currentChar &&
            preg_match((string) self::REGEX_LETTERS_NUM, $this->currentChar)
        ) {
            // Добавляем символ в строку и переходим к следующему
            $result .= $this->currentChar;
            $this->moveCode();
        }

        // Есть ли в словаре зарезервированное слово
        if (isset($this->reservedKeywords[$result])) {
            // Токен слова из словаря
            return $this->reservedKeywords[$result];
        }

        // Создаём токен и инициализируем его
        return new Token(TokenType::ID, $result);
    }
}
