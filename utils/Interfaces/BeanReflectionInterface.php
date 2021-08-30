<?php


namespace Linuzilla\Utils\Interfaces;


use ReflectionException;

interface BeanReflectionInterface {
    /**
     * @param array $data
     * @return object
     * @throws ReflectionException
     */
    public function newBean(array $data): object;
}