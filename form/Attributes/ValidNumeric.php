<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\ValidatableAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidNumeric implements ValidatableAttribute {
    const DEFAULT_ERROR_MESSAGE = 'valid.numeric';
    private string $errorMessage;

    /**
     * ValidNumeric constructor.
     * @param string $errorMessage
     */
    public function __construct(string $errorMessage = self::DEFAULT_ERROR_MESSAGE) {
        $this->errorMessage = $errorMessage;
    }


    public function validate(string $value): ?string {
        return is_numeric($value) ? null : $this->errorMessage;
    }

    public function defaultErrorMessage(): string {
        return self::DEFAULT_ERROR_MESSAGE;
    }
}