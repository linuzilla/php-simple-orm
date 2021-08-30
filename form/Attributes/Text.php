<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\PropertyAttribute;
use Linuzilla\Form\Attributes\Categories\TypeAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Text implements TypeAttribute {
    private int $size;
    private int $maxLength;

    /**
     * Text constructor.
     * @param int $size
     * @param int $maxLength
     */
    public function __construct(int $size, int $maxLength) {
        $this->size = $size;
        $this->maxLength = $maxLength;
    }

    /**
     * @return string
     */
    public function inputType(): string {
        return 'text';
    }

    /**
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getMaxLength(): int {
        return $this->maxLength;
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param array $propertyAttributes
     * @return string
     */
    public function output(string $fieldName, string $value, array $propertyAttributes): string {
        return sprintf("<input type='%s' name='%s' value='%s' size='%d' maxlength='%d' %s>",
            $this->inputType(), $fieldName, htmlspecialchars($value),
            $this->size, $this->maxLength,
            implode(' ', array_map(function ($attr) {
                /**@var PropertyAttribute $attr */
                return $attr->attr();
            }, $propertyAttributes)));
    }
}