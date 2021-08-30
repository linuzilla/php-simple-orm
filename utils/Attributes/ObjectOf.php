<?php


namespace Linuzilla\Utils\Attributes;


use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ObjectOf {
    /**
     * ObjectOf constructor.
     * @param string $clazz
     */
    public function __construct(private string $clazz) {
    }

    /**
     * @return string
     */
    public function getClazz(): string {
        return $this->clazz;
    }
}