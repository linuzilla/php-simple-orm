<?php


namespace Linuzilla\Database\Attributes;


use Attribute;

/**
 * Class ColumnType
 * @package Linuzilla\Database\Attributes
 * @author Mac Liu <linuzilla@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ColumnType {
    private string $value;

    /**
     * ColumnType constructor.
     * @param string $value
     */
    public function __construct(string $value) {
        $this->value = $value;
    }
}