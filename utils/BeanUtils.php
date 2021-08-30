<?php


namespace Linuzilla\Utils;


use Linuzilla\Utils\Attributes\PropertyDescription;
use Linuzilla\Utils\Attributes\SomethingOf;
use ReflectionClass;
use ReflectionException;

/**
 * Class BeanUtils
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 */
class BeanUtils {
    public static function copy(object $source, object $target) {
        $r1 = new ReflectionClass($source);
        $r2 = new ReflectionClass($target);

        foreach ($r1->getProperties() as $p) {
            $propertyName = $p->getName();

            try {
                $p2 = $r2->getProperty($propertyName);

                if ($p->isInitialized($source)) {
                    $p2->setValue($target, $p->getValue($source));
                }
            } catch (ReflectionException $e) {
            }
        }
    }

    /**
     * @param array $source
     * @param object $target
     * @return object
     */
    public static function array2Object(array $source, object $target): object {
        $ref = new ReflectionClass($target);

        foreach ($source as $propertyName => $value) {
            try {
                $reflectionProperty = $ref->getProperty($propertyName);
                $reflectionProperty->setValue($target, $value);
            } catch (ReflectionException) {
            }
        }
        return $target;
    }

    /**
     * @param array $source
     * @param string $clazz
     * @return object|array
     * @throws ReflectionException
     */
    public static function deepConvert(array $source, string $clazz): object|array {
        $ref = new BeanReflection($clazz);

        if (array_keys($source) !== range(0, count($source) - 1)) {
            return $ref->newBean($source);
        } else {
            return array_map(function ($item) use ($ref) {
                return $ref->newBean($item);
            }, $source);
        }
    }
}