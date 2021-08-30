<?php


namespace Linuzilla\Form\Attributes\Categories;


use ReflectionClass;

interface AdvancedValidateAttribute extends ValidatableAttribute {
    /**
     * @param string $value
     * @param array $values
     * @param object $bean
     * @param ReflectionClass $ref
     * @return string|null
     */
    public function advancedValidate(string $value, array $values, object $bean, ReflectionClass $ref): ?string;
}