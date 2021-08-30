<?php


namespace Linuzilla\Database\Criterion;


use Closure;
use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Entities\BaseEntity;
use Linuzilla\Database\Helpers\DataUnifier;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;

/**
 * Class Logical
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 */
class Logical {
    private Closure $function;


    /**
     * Logical constructor.
     * @param Closure $function
     */
    private function __construct(Closure $function) {
        $this->function = $function;
    }

    /**
     * @param ColumnsAndDialectProvider $repos
     * @return QueryWithArgs
     */
    public function buildQuery(ColumnsAndDialectProvider $repos): QueryWithArgs {
        $f = $this->function;
        return $f($repos);
    }


    /**
     * @param ColumnsAndDialectProvider $repos
     * @param Logical|BaseEntity|SpecialCondition|array $logical
     * @return QueryWithArgs
     * @throws DbException
     */
    private static function build(ColumnsAndDialectProvider $repos, Logical|BaseEntity|SpecialCondition|array $logical): QueryWithArgs {
        if (is_array($logical) or $logical instanceof BaseEntity or $logical instanceof SpecialCondition) {
            return (new DataUnifier($logical))->buildQuery($repos);
        } else if ($logical instanceof Logical) {
            return $logical->buildQuery($repos);
        } else {
            throw new DbException("data type not acceptable");
        }
    }

    /**
     * @param Logical|SpecialCondition|mixed ...$conditions
     * @return Logical
     */
    #[Pure] public static function or(Logical|SpecialCondition|array ...$conditions): Logical {
        return new Logical(function (ColumnsAndDialectProvider $repos) use ($conditions) {
            $queryAndArgs = array_map(fn($condition) => self::build($repos, $condition), $conditions);

            return new QueryWithArgs(
                implode(" OR ", array_map(fn(QueryWithArgs $q) => "(" . $q->query . ")", $queryAndArgs)),
                array_merge(...array_map(fn(QueryWithArgs $q) => $q->args, $queryAndArgs))
            );
        });
    }

    /**
     * @param Logical|SpecialCondition|mixed ...$conditions
     * @return Logical
     */
    #[Pure] public static function and(Logical|SpecialCondition|array ...$conditions): Logical {
        return new Logical(function (ColumnsAndDialectProvider $repos) use ($conditions) {
            $queryAndArgs = array_map(fn($condition) => self::build($repos, $condition), $conditions);

            return new QueryWithArgs(
                implode(" AND ", array_map(fn(QueryWithArgs $q) => "(" . $q->query . ")", $queryAndArgs)),
                array_merge(...array_map(fn(QueryWithArgs $q) => $q->args, $queryAndArgs))
            );
        });
    }

    /**
     * @param Logical|SpecialCondition|array $first
     * @return Logical
     */
    #[Pure] public static function not(Logical|SpecialCondition|array $first): Logical {
        return new Logical(function (ColumnsAndDialectProvider $repos) use ($first) {
            $qa1 = self::build($repos, $first);

            return new QueryWithArgs(
                sprintf("(NOT %s)", $qa1->query,),
                $qa1->args);
        });
    }
}