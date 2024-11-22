<?php

declare(strict_types=1);

namespace Pascal\Lexer;

class Token
{
    // Тип токена
    public string $type;

    // Значение токена
    public ?string $value;

    public function __construct(string $type, ?string $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    // Экземпляр класса в строковом представлении
    public function __toString(): string
    {
        return sprintf('Token(%s, %s)', $this->type, $this->value ?? '');
    }
}
