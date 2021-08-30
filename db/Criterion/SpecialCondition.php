<?php


namespace Linuzilla\Database\Criterion;


use Closure;
use JetBrains\PhpStorm\Pure;

/**
 * Class SpecialCondition
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 */
class SpecialCondition {
    private Closure $function;

    /**
     * SpecialCondition constructor.
     * @param Closure $function
     */
    public function __construct(Closure $function) {
        $this->function = $function;
    }

    public function retrieve(): QueryWithArgs {
        $f = $this->function;
        return $f();
    }

    /**
     * @param string $condition
     * @return SpecialCondition
     */
    #[Pure] public static function raw(string $condition): SpecialCondition {
        return new SpecialCondition(function () use ($condition) {
            return new QueryWithArgs($condition, []);
        });
    }
}