<?php


namespace Linuzilla\Database\Attributes;


use Attribute;

/**
 * Class Entity
 * @package Linuzilla\Database\Attributes
 * @author Mac Liu <linuzilla@gmail.com>
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Entity {
    private string $name;
    private string $type;

    /**
     * Entity constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type) {
        $this->name = $name;
        $this->type = $type;
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
    public function getType(): string {
        return $this->type;
    }

}