<?php


namespace Linuzilla\Utils;


/**
 * Class ArrayUtils
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul  8 01:31:10 UTC 2021
 */
class ArrayUtils {
    /**
     * @param array $list
     * @param callable $callable
     * @param mixed $initial
     * @return mixed
     */
    public static function reduce(array $list, callable $callable, mixed $initial): mixed {
        return array_reduce($list, function ($accumulator, $entry) use ($callable) {
            $callable($accumulator, $entry);
            return $accumulator;
        }, $initial);
    }

    /**
     * @param array $list
     * @param callable $callable
     * @return mixed
     */
    public static function collectMap(array $list, callable $callable): mixed {
        return array_reduce($list, function ($accumulator, $entry) use ($callable) {
            $keyOnlyOrEntry = $callable($entry);

            if (is_array($keyOnlyOrEntry)) {
                $accumulator[$keyOnlyOrEntry[0]] = $keyOnlyOrEntry[1];
            } else {
                $accumulator[$keyOnlyOrEntry] = $entry;
            }
            return $accumulator;
        }, []);
    }

    public static function collectList(array $list, callable $callable): mixed {
        return array_reduce($list, function ($accumulator, $entry) use ($callable) {
            array_push($accumulator, $callable($entry));
            return $accumulator;
        }, []);
    }


    /**
     * @param array $list
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function arrayOfMapAppend(array &$list, string $key, mixed $value): array {
        if (!isset($list[$key])) {
            $list[$key] = [$value];
        } else {
            $list[$key][] = $value;
        }
        return $list;
    }
}