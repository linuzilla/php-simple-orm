<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Helpers\DataUnifier;
use Linuzilla\Database\Interfaces\ComplexFetchableTrait;
use Linuzilla\Database\Interfaces\ComplexLimitAndFetchable;
use Linuzilla\Database\Models\JoinInfo;
use Linuzilla\Database\Repositories\AdvancedRepository;

/**
 * Class ComplexWhereClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class ComplexWhereClause implements ComplexLimitAndFetchable {
    use ComplexFetchableTrait;

    private string $whereSentence;
    private array $args;

    /**
     * ComplexWhereClause constructor.
     * @param AdvancedRepository $baseRepository
     * @param JoinInfo[] $joins
     * @param Logical|array $condition
     */
    public function __construct(
        private AdvancedRepository $baseRepository,
        private array $joins,
        private Logical|array $condition) {

//        $joinMap = array_reduce($this->joins, function ($carry, JoinInfo $joinInfo) {
//            $carry[$joinInfo->alias] = $joinInfo;
//            return $carry;
//        }, []);

        if (!isset($this->condition) or empty($condition)) {
            $this->whereSentence = '';
            $this->args = [];
        } else if ($this->condition instanceof Logical) {
            $queryAndArgs = $condition->buildQuery($this->baseRepository);
            $this->whereSentence = $queryAndArgs->query;
            $this->args = $queryAndArgs->args;
        } else {
            $data = new DataUnifier($this->condition);

            if ($data->count() == 0) {
                $this->whereSentence = '';
                $this->args = [];
            } else {
                $this->buildQuery($data);
            }
        }
//
//        echo ' WHERE ' . $this->whereSentence . PHP_EOL;
//        echo $this->args . PHP_EOL;
    }

    /**
     * @param DataUnifier $data
     */
    private function buildQuery(DataUnifier $data) {
        $queryAndArgs = $data->buildQuery($this->baseRepository);
        $this->whereSentence = $queryAndArgs->query;
        $this->args = $queryAndArgs->args;
    }

    /**
     * @param array|string $groupItems
     * @return ComplexGroupByClause
     */
    #[Pure] public function groupBy(array|string $groupItems): ComplexGroupByClause {
        return new ComplexGroupByClause($this->baseRepository, $this, $groupItems);
    }

    /**
     * order by
     * @param array|string|Op|Order $order
     * @return ComplexOrderClause
     */
    #[Pure]
    public function order(array|string|Op|Order $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this, null, null, $order);
    }

    /**
     * order by ascending
     * @param array|string|Op $order
     * @return ComplexOrderClause
     */
    public function asc(array|string|Op $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this, null, null, Order::asc($order));
    }

    /**
     * order by descending
     * @param array|string|Op $order
     * @return ComplexOrderClause
     */
    public function desc(array|string|Op $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this, null, null, Order::desc($order));
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return ComplexLimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): ComplexLimitClause {
        return new ComplexLimitClause($this->baseRepository, $this, null, null, null, $offset, $rowCount);
    }

    /**
     * @return string
     */
    public function getWhereSentence(): string {
        return $this->whereSentence;
    }

    /**
     * @return array
     */
    public function getArgs(): array {
        return $this->args;
    }
}