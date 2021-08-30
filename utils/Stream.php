<?php


namespace Linuzilla\Utils;


use JetBrains\PhpStorm\Pure;

/**
 * Like Java stream api, but just use array and the operation can be EXPENSIVE.
 *
 * Class Stream
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 */
class Stream {
    /**
     * Stream constructor.
     * @param array $dataArray
     */
    private function __construct(private array $dataArray) {
    }

    /**
     * @param array $array
     * @return Stream
     */
    #[Pure] public static function of(array $array): Stream {
        return new Stream($array);
    }

    /**
     * @param callable $callable
     * @return Stream
     */
    public function map(callable $callable): Stream {
        return new Stream(array_map($callable, $this->dataArray));
    }

    /**
     * @param callable $callable
     * @return Stream
     */
    public function flatMap(callable $callable): Stream {
        return new Stream(array_merge(...array_map($callable, $this->dataArray)));
    }

    /**
     * @param callable $predicate
     * @return Stream
     */
    public function filter(callable $predicate): Stream {
        return new Stream(ArrayUtils::reduce($this->dataArray,
            fn(&$carry, $entry) => $predicate($entry) and array_push($carry, $entry)
            , []));
    }

    /**
     * @return Optional
     */
    #[Pure] public function findFirst(): Optional {
        return Optional::of(count($this->dataArray) > 0 ? $this->dataArray[0] : null);
    }

    /**
     * @param callable $predicate
     * @return bool
     */
    public function anyMatch(callable $predicate): bool {
        foreach ($this->dataArray as $item) {
            if ($predicate($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param callable $callable
     */
    public function forEach(callable $callable): void {
        foreach ($this->dataArray as $item) {
            $callable($item);
        }
    }

    /**
     * @return array
     */
    public function collectList(): array {
        return $this->dataArray;
    }

    /**
     * @param callable $keyFunction
     * @param callable $valueFunction
     * @return array
     */
    public function collectMap(callable $keyFunction, callable $valueFunction): array {
        return ArrayUtils::reduce($this->dataArray, fn(&$accumulator, $entry) => $accumulator[$keyFunction($entry)] =
            $valueFunction($entry), []);
    }
}