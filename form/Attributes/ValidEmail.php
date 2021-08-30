<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\ValidatableAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidEmail implements ValidatableAttribute {
    const DEFAULT_ERROR_MESSAGE = 'valid.email.error';
    private string $errorMessage;

    /**
     * ValidEmail constructor.
     * @param string $errorMessage
     */
    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MESSAGE) {
        $this->errorMessage = $errorMessage;
    }


    /**
     * @param string $value
     * @return string|null
     */
    public function validate(string $value): ?string {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : $this->errorMessage;
    }

    /**
     * @return string
     */
    public function defaultErrorMessage(): string {
        return self::DEFAULT_ERROR_MESSAGE;
    }
}