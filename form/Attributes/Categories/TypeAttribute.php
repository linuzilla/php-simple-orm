<?php


namespace Linuzilla\Form\Attributes\Categories;


interface TypeAttribute extends GenericFormAttribute {
    /**
     * @param string $fieldName
     * @param string $value
     * @param array $propertyAttributes
     * @return string
     */
    public function output(string $fieldName, string $value, array $propertyAttributes): string;
}