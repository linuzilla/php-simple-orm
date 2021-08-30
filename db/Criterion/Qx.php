<?php


namespace Linuzilla\Database\Criterion;


use Closure;
use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Helpers\StringHelper;

/**
 * Class Qx
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 12:30:04 CST 2022
 */
class Qx {
    private Closure $function;

    /**
     * Qx constructor.
     * @param Closure $function
     */
    private function __construct(Closure $function) {
        $this->function = $function;
    }

    /**
     * @return array
     */
    public function get(): array {
        /** @var callable $f */
        $f = $this->function;
        return $f();
    }

    /**
     * @return Qx
     */
    #[Pure]
    public static function notNull(): Qx {
        return new Qx(function () {
            return ['IS NOT NULL', null];
        });
    }

    /**
     * @return Qx
     */
    #[Pure]
    public static function isNull(): Qx {
        return new Qx(function () {
            return ['IS NULL', null];
        });
    }

    /**
     * @param string $value
     * @return Qx
     */
    #[Pure]
    public static function like(string $value): Qx {
        return new Qx(function () use ($value) {
            return ['LIKE ?', $value];
        });
    }

    /**
     * @param array $values
     * @return Qx
     */
    #[Pure]
    public static function in(array $values): Qx {
        return new Qx(function () use ($values) {
            return ['IN (' .
                StringHelper::array_convert_and_join($values, ',', function ($v) {
                    return '?';
                }) . ')', $values];
        });
    }

    /**
     * @param string|int $value
     * @return Qx
     */
    #[Pure]
    public static function lessThan(string|int $value): Qx {
        return new Qx(function () use ($value) {
            return ['<= ?', $value];
        });
    }

    /**
     * @param string|int $value
     * @return Qx
     */
    #[Pure]
    public static function lessEqual(string|int $value): Qx {
        return new Qx(function () use ($value) {
            return ['<= ?', $value];
        });
    }

    /**
     * @param $value
     * @return Qx
     */
    #[Pure]
    public static function greaterThan($value): Qx {
        return new Qx(function () use ($value) {
            return ['> ?', $value];
        });
    }

    /**
     * @param $value
     * @return Qx
     */
    #[Pure]
    public static function greaterEqual($value): Qx {
        return new Qx(function () use ($value) {
            return ['>= ?', $value];
        });
    }

    /**
     * @param $value
     * @return Qx
     */
    #[Pure] public static function notEqual($value): Qx {
        return new Qx(function () use ($value) {
            return ['!= ?', $value];
        });
    }

    /**
     * @return Qx
     */
    #[Pure]
    public static function timePassed(): Qx {
        return new Qx(function () {
            return ['< NOW()', null];
        });
    }

    /**
     * @return Qx
     */
    #[Pure]
    public static function timeNotArrive(): Qx {
        return new Qx(function () {
            return ['> NOW()', null];
        });
    }
}