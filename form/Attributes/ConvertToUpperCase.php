<?php


namespace Linuzilla\Form\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ConvertToUpperCase implements Categories\ConverterAttribute {
    /**
     * @param string $org
     * @return string
     */
    public function convert(string $org): string {
        return strtoupper($org);
    }
}