<?php


namespace Linuzilla\Database\Criterion;


use Closure;
use Linuzilla\Database\Dialects\DatabaseDialect;
use Linuzilla\Database\Helpers\StringHelper;

/**
 * Class Order
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 12:30:04 CST 2022
 */
class Order {
    private Closure $function;

    private function __construct(Closure $function) {
        $this->function = $function;
    }


    /**
     * @param DatabaseDialect $dialect
     * @return string
     */
    public function get(DatabaseDialect $dialect): string {
        /** @var callable $f */
        $f = $this->function;

        return $f($dialect);
    }

    /**
     * @param string|array $fields
     * @return Order
     */
    public static function asc(string|array $fields): Order {
        return new Order(function (DatabaseDialect $dialect) use ($fields) {
            return StringHelper::sqlFieldsWithQuote($fields, $dialect, 'ASC');
        });
    }

    /**
     * @param string|array $fields
     * @return Order
     */
    public static function desc(string|array $fields): Order {
        return new Order(function (DatabaseDialect $dialect) use ($fields) {
            return StringHelper::sqlFieldsWithQuote($fields, $dialect, 'DESC');
        });
    }
}