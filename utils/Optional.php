<?php


namespace Linuzilla\Utils;


use JetBrains\PhpStorm\Pure;

/**
 * Class Optional
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 */
class Optional {
    private mixed $data;

    /**
     * @param mixed $data
     * @return Optional
     */
    #[Pure] public static function of(mixed $data): Optional {
        $optional = new Optional();
        if (isset($data)) {
            $optional->data = $data;
        }
        return $optional;
    }

    /**
     * @return Optional
     */
    #[Pure] public static function empty(): Optional {
        return new Optional();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return !isset($this->data);
    }

    /**
     * @return bool
     */
    public function isPresent(): bool {
        return isset($this->data);
    }

    /**
     * @param mixed $elseValue
     * @return mixed
     */
    public function orElse(mixed $elseValue): mixed {
        return $this->data ?? $elseValue;
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function orElseGet(callable $callable): mixed {
        return $this->data ?? $callable();
    }
}