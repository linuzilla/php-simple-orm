<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\PropertyAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements PropertyAttribute {
    public function attr(): string {
        return 'required';
    }
}