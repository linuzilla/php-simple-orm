<?php


namespace Linuzilla\Form\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ConvertToLowerCase implements Categories\ConverterAttribute {
    /**
     * @param string $org
     * @return string
     */
    public function convert(string $org): string {
        return strtolower($org);
    }

}