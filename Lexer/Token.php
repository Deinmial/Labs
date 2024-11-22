<?php

declare(strict_types=1);

namespace Pascal\Lexer;

class Token
{
    /**
     * @var string
     */
    public string $type;

    /**
     * @var string|null
     */
    public ?string $value;

    public function __construct(string $type, ?string $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * String representation of the class instance.
     *
     * Examples:
     *      Token(INTEGER, 3)
     *      Token(OPERATOR '+')
     */
    public function __toString(): string
    {
        return sprintf('Token(%s, %s)', $this->type, $this->value ?? '');
    }
}
