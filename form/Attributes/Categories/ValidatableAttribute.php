<?php


namespace Linuzilla\Form\Attributes\Categories;


interface ValidatableAttribute {
    /**
     * @param string $value
     * @return string|null
     */
    public function validate(string $value): ?string;

    /**
     * @return string
     */
    public function defaultErrorMessage(): string;
}