<?php


namespace Linuzilla\Form\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Password extends Text {
    public function __construct(int $size, int $maxLength) {
        parent::__construct($size, $maxLength);
    }

    /**
     * @return string
     */
    public function inputType(): string {
        return 'password';
    }
}