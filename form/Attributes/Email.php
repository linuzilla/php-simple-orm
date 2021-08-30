<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\ValidatableAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email extends Text {
    public function __construct(int $size, int $maxLength) {
        parent::__construct($size, $maxLength);
    }

    /**
     * @return string
     */
    public function inputType(): string {
        return 'email';
    }
}