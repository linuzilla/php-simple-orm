<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\ValidatableAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidRegularExpression implements ValidatableAttribute {
    const DEFAULT_ERROR_MESSAGE = 'valid.not.match.pattern';
    private string $pattern;
    private string $errorMessage;

    /**
     * ValidRegularExpression constructor.
     * @param string $pattern
     * @param string $errorMessage
     */
    public function __construct(string $pattern, string $errorMessage = self::DEFAULT_ERROR_MESSAGE) {
        $this->pattern = '/' . $pattern . '/';
        $this->errorMessage = $errorMessage;
    }

    public function validate(string $value): ?string {
        return preg_match($this->pattern, $value) ? null : $this->errorMessage;
    }

    public function defaultErrorMessage(): string {
        return self::DEFAULT_ERROR_MESSAGE;
    }
}