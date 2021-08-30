<?php


namespace Linuzilla\Utils\Attributes;


use Attribute;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ListOf extends ObjectOf {

    /**
     * ListOf constructor.
     */
    #[Pure] public function __construct(string $clazz) {
        parent::__construct($clazz);
    }
}