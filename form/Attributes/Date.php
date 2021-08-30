<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\TypeAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date implements TypeAttribute {
    /**
     * @param string $fieldName
     * @param string $value
     * @param array $propertyAttributes
     * @return string
     */
    public function output(string $fieldName, string $value, array $propertyAttributes): string {
        return sprintf("<input type='date' name='%s' value='%s'>", $fieldName, $value);
    }
}