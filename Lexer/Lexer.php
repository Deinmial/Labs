<?php

declare(strict_types=1);

namespace Pascal\Lexer;


use Exception;

class Lexer
{
    public const REGEX_ALPHA_INSENSITIVE = '/[a-z]/i';
    public const REGEX_ALPHANUMERIC_INSENSITIVE = '/[a-z0-9]/i';
    public const REGEX_NUMERIC = '/[0-9]/';
    public const REGEX_WHITESPACE = '/\s/';

    /**
     * client string input, e.g. "3+5"
     *
     * @var string
     */
    protected string $text;

    /**
     * index into `Interpreter::$text`
     *
     * @var int
     */
    protected int $position = 0;

    /**
     * current token instance
     *
     * @var Token|null
     */
    protected ?Token $currentToken = null;

    protected ?string $currentChar;

    /**
     * @var array<string, Token>
     */
    protected array $reservedKeywords;

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

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        $result = [];
        $this->currentToken = $this->getNextToken();

        while (TokenType::EOF !== $this->currentToken->type) {
            $result[] = $this->currentToken;
            $this->currentToken = $this->getNextToken();
        }

        $result[] = $this->currentToken;

        return $result;
    }

    /**
     * Advance `position`, and set `currentChar`.
     *
     * @return string|null `currentChar`
     */
    public function advance(): ?string
    {
        $this->position++;

        if ($this->position > mb_strlen($this->text) - 1) {
            $this->currentChar = null;
        } else {
            $this->currentChar = $this->text[$this->position];
        }

        return $this->currentChar;
    }

    public function skipWhitespace(): void
    {
        while (null !== $this->currentChar && preg_match((string) self::REGEX_WHITESPACE, $this->currentChar)) {
            $this->advance();
        }
    }

    public function skipComment(): void
    {
        while ('}' !== $this->currentChar) {
            $this->advance();
        }
        // include closing brace
        $this->advance();
    }

    /**
     * Return a (multidigit) number consumed from the input.
     */
    public function number(): Token
    {
        $result = '';

        while (null !== $this->currentChar && preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
            $result .= $this->currentChar;
            $this->advance();
        }

        if ('.' !== $this->currentChar) {
            return new Token(TokenType::INTEGER_CONST, $result);
        }

        $result .= $this->currentChar;
        $this->advance();

        while (null !== $this->currentChar && preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
            $result .= $this->currentChar;
            $this->advance();
        }

        return new Token(TokenType::REAL_CONST, $result);
    }

    /**
     * Lexical analyzer (also known as scanner or tokenizer)
     * This method is responsible for breaking a sentence
     * apart into tokens. One token at a time.
     */
    public function getNextToken(): Token
    {
        while (null !== $this->currentChar) {
            if (preg_match((string) self::REGEX_WHITESPACE, $this->currentChar)) {
                $this->skipWhitespace();
                continue;
            }

            if (preg_match((string) self::REGEX_NUMERIC, $this->currentChar)) {
                $number = $this->number();
                return $number;
            }

            if ('+' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::OPERATOR, '+');
            }

            if ('-' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::OPERATOR, '-');
            }

            if ('*' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::OPERATOR, '*');
            }

            if ('/' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::OPERATOR, '/');
            }

            if ('(' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::OPEN_PAREN, '(');
            }

            if (')' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::CLOSE_PAREN, ')');
            }

            if (preg_match(self::REGEX_ALPHA_INSENSITIVE, $this->currentChar)) {
                return $this->id();
            }

            if (':' === $this->currentChar && '=' === $this->peek()) {
                $this->advance();
                $this->advance();
                return new Token(TokenType::ASSIGNMENT, ':=');
            }

            if (';' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::END_STATEMENT, ';');
            }

            if ('.' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::DOT, '.');
            }

            if ('{' === $this->currentChar) {
                $this->advance();
                $this->skipComment();
                continue;
            }

            if (':' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::COLON, ':');
            }

            if (',' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::COMMA, ',');
            }

            if ('/' === $this->currentChar) {
                $this->advance();
                return new Token(TokenType::FLOAT_DIV, '/');
            }

            throw new Exception(sprintf('Unknown token: %s', (string) $this->currentChar));
        }

        return new Token(TokenType::EOF);
    }

    public function peek(): ?string
    {
        $peekPosition = $this->position + 1;

        if ($peekPosition > mb_strlen($this->text) - 1) {
            return null;
        }

        return $this->text[$peekPosition];
    }

    public function id(): Token
    {
        $result = '';

        while (
            null !== $this->currentChar &&
            preg_match((string) self::REGEX_ALPHANUMERIC_INSENSITIVE, $this->currentChar)
        ) {
            $result .= $this->currentChar;
            $this->advance();
        }

        if (isset($this->reservedKeywords[$result])) {
            return $this->reservedKeywords[$result];
        }

        return new Token(TokenType::ID, $result);
    }
}
