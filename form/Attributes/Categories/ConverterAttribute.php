<?php


namespace Linuzilla\Form\Attributes\Categories;


interface ConverterAttribute {
    public function convert(string $org): mixed;
}