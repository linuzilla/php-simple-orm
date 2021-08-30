<?php


namespace Linuzilla\Database\Attributes;


use Attribute;

/**
 * Class Properties
 * @package Linuzilla\Database\Attributes
 * @author Mac Liu <linuzilla@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Properties {
    private string $name;
    private string $dataType;
    private string $columnType;

    /**
     * Column constructor.
     * @param string $name
     * @param string $dataType
     * @param string $columnType
     */
    public function __construct(string $name, string $dataType, string $columnType) {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->columnType = $columnType;
    }


    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDataType(): string {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getColumnType(): string {
        return $this->columnType;
    }
}