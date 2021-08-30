<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\PropertyAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ReadOnly implements PropertyAttribute {
    public function attr(): string {
        return 'readonly';
    }
}