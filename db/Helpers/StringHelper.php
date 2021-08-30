<?php


namespace Linuzilla\Database\Helpers;


use Linuzilla\Database\Dialects\DatabaseDialect;

class StringHelper {
    /**
     * @param string $string
     * @param bool $capitalizeFirst
     * @return string
     */
    public static function to_camel_case(string $string, bool $capitalizeFirst = false): string {
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
    public static function camel_to_snake($input): string {
        $left = $input;
        $tail = '';

        while (preg_match('/(^.*[a-z0-9])([A-Z].*)$/', $left, $matches)) {
            $tail = '_' . strtolower($matches[2]) . $tail;
            $left = $matches[1];
        }

        return $left . $tail;
    }

    public static function array_backquoted_and_join(array $list, string $separator): string {
        return self::array_convert_and_join($list, $separator, fn($element) => sprintf("`%s`", $element));
    }

    public static function array_quoted_and_join(array $list, string $separator): string {
        return self::array_convert_and_join($list, $separator, fn($element) => sprintf("'%s'", $element));
    }

    public static function array_convert_and_join(array $list, string $separator, callable $converter): string {
        return implode($separator, array_map($converter, $list));
    }

    public static function sqlFieldsWithQuote(array|string $fields, DatabaseDialect $dialect, string $append = ''): string {
        if (is_array($fields)) {
            return implode(',', array_map(function ($field) use ($dialect, $append) {
                return $dialect->quote($field) . (empty($append) ? '' : ' ' . $append);
            }, $fields));
        } else {
            return $dialect->quote($fields) . (empty($append) ? '' : ' ' . $append);
        }
    }
}