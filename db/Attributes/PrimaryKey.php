<?php


namespace Linuzilla\Database\Attributes;


use Attribute;

/**
 * Class PrimaryKey
 * @package Linuzilla\Database\Attributes
 * @author Mac Liu <linuzilla@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class PrimaryKey {
    private array $value;

    /**
     * PrimaryKey constructor.
     * @param array $value
     */
    public function __construct(array $value = []) {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getValue(): array {
        return $this->value;
    }

}