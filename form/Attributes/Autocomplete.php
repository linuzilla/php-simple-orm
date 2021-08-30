<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\PropertyAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autocomplete implements PropertyAttribute {

    /**
     * Autocomplete constructor.
     */
    public function __construct(private string $flag = 'off') {
    }

    public function attr(): string {
        return sprintf("autocomplete='%s'", $this->flag);
    }

}