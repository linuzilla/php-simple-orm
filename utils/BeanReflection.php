<?php


namespace Linuzilla\Utils;


use Linuzilla\Utils\Attributes\ListOf;
use Linuzilla\Utils\Attributes\ObjectOf;
use Linuzilla\Utils\Interfaces\BeanReflectionInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class BeanReflection
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 */
class BeanReflection implements BeanReflectionInterface {
    private ReflectionClass $ref;
    private array $properties;
    private array $extendedPropertyNames;

    /**
     * BeanReflection constructor.
     * @throws ReflectionException
     */
    public function __construct(private string $clazz) {
        $this->ref = new ReflectionClass($this->clazz);
        $this->properties = [];
        $this->extendedPropertyNames = [];

        foreach ($this->ref->getProperties() as $reflectionProperty) {
            $beanProperty = new BeanProperty($reflectionProperty->getName(), $reflectionProperty);

            foreach ($reflectionProperty->getAttributes() as $attr) {
                if (is_subclass_of($attr->getName(), ObjectOf::class)) {
                    /** @var ObjectOf $instance */
                    $instance = $attr->newInstance();
                    $beanProperty->setBeanReflection(new BeanReflection($instance->getClazz()));

                    if ($instance instanceof ListOf) {
                        $beanProperty->setPropertyType(BeanProperty::LIST);
                    } else {
                        $beanProperty->setPropertyType(BeanProperty::OBJECT);
                    }
                    break;
                }
            }

            $this->properties[$beanProperty->getName()] = $beanProperty;
        }

        foreach ($this->properties as $propertyName => $value) {
            foreach ([StringUtils::toCamelCase($propertyName),
                         StringUtils::toCamelCase($propertyName, true),
                         StringUtils::toSnakeCase($propertyName)] as $alternateName) {
                if (!isset ($this->properties[$alternateName]) and !isset($this->extendedPropertyNames[$alternateName])) {
                    $this->extendedPropertyNames[$alternateName] = $value;
                }
            }
        }
    }

    /**
     * @param string $propertyName
     * @return BeanProperty|null
     */
    public function getProperty(string $propertyName): ?BeanProperty {
        return $this->properties[$propertyName] ?? $this->extendedPropertyNames[$propertyName] ?? $this->oneMoreTry($propertyName);
    }

    /**
     * @param string $propertyName
     * @return BeanProperty|null
     */
    private function oneMoreTry(string $propertyName): ?BeanProperty {
        $alternateName = StringUtils::toCamelCase($propertyName);

        return $this->properties[$alternateName] ?? $this->extendedPropertyNames[$alternateName] ?? null;
    }

    /**
     * @param array $data
     * @return object
     * @throws ReflectionException
     */
    public function newBean(array $data): object {
        $instance = $this->ref->newInstance();

        foreach ($data as $propertyName => $value) {
            $beanProperty = $this->getProperty($propertyName);

            if (!is_null($beanProperty)) {
                $beanProperty->setValue($instance, $value);
            }
        }
        return $instance;
    }
}