<?php


namespace Linuzilla\Database\Models;


use JetBrains\PhpStorm\Pure;

/**
 * Class Qn
 * @package Linuzilla\Database\Models
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class Qn {
    public static string $delimiter = "::";

    /**
     * @return string
     */
    public static function delimiter(): string {
        return self::$delimiter;
    }

    /**
     * @param string $aliasName
     * @param string|null $columnName
     * @return string
     */
    #[Pure] public static function q(string $aliasName, string $columnName = null): string {
        return $aliasName . self::delimiter() . $columnName ?? "";
    }
}