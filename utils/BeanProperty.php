<?php


namespace Linuzilla\Utils;


use Linuzilla\Utils\Interfaces\BeanReflectionInterface;
use ReflectionException;
use ReflectionProperty;

class BeanProperty {
    const PLAIN = 0;
    const OBJECT = 1;
    const LIST = 2;

    private BeanReflectionInterface $beanReflection;
    private int $propertyType = self::PLAIN;

    /**
     * BeanProperty constructor.
     */
    public function __construct(
        private string $propertyName,
        private ReflectionProperty $reflectionProperty
    ) {
    }

    /**
     * @return BeanReflectionInterface
     */
    public function getBeanReflection(): BeanReflectionInterface {
        return $this->beanReflection;
    }

    /**
     * @param BeanReflectionInterface $beanReflection
     */
    public function setBeanReflection(BeanReflectionInterface $beanReflection): void {
        $this->beanReflection = $beanReflection;
    }

    /**
     * @return int
     */
    public function getPropertyType(): int {
        return $this->propertyType;
    }

    /**
     * @param int $propertyType
     */
    public function setPropertyType(int $propertyType): void {
        $this->propertyType = $propertyType;
    }

    public function getName(): string {
        return $this->propertyName;
    }

    /**
     * @param object $target
     * @param mixed $value
     * @throws ReflectionException
     */
    public function setValue(object $target, mixed $value) {
        switch ($this->propertyType) {
            case self::PLAIN:
                $this->reflectionProperty->setValue($target, $value);
                break;

            case self::OBJECT:
                $this->reflectionProperty->setValue($target, $this->beanReflection->newBean($value));
                break;

            case self::LIST:
                if (is_array($value)) {
                    $list = [];
                    foreach ($value as $item) {
                        $list[] = $this->beanReflection->newBean($item);
                    }
                    $this->reflectionProperty->setValue($target, $list);
                }
                break;
        }
    }
}