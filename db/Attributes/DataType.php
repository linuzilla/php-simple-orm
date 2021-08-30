<?php


namespace Linuzilla\Database\Attributes;


use Attribute;

/**
 * Class DataType
 * @package Linuzilla\Database\Attributes
 * @author Mac Liu <linuzilla@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DataType {
    private string $value;

    /**
     * DataType constructor.
     * @param string $value
     */
    public function __construct(string $value) {
        $this->value = $value;
    }

}