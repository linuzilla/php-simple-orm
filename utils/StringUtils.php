<?php


namespace Linuzilla\Utils;


/**
 * Class StringUtils
 * @package Linuzilla\Utils
 * @author Mac Liu <linuzilla@gmail.com>
 */
class StringUtils {
    /**
     * @param string $string
     * @param bool $capitalizeFirst
     * @return string
     */
    public static function toCamelCase(string $string, bool $capitalizeFirst = false): string {
        $str = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));

        if (!$capitalizeFirst) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    /**
     * @param $input
     * @return string
     */
    public static function toSnakeCase($input): string {
        $left = $input;
        $tail = '';

        while (preg_match('/(^.*[a-z0-9])([A-Z].*)$/', $left, $matches)) {
            $tail = '_' . strtolower($matches[2]) . $tail;
            $left = $matches[1];
        }

        return $left . $tail;
    }
}