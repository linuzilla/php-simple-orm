<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Entities\BaseEntity;
use Linuzilla\Database\Helpers\DataUnifier;
use Linuzilla\Database\Interfaces\Fetchable;
use Linuzilla\Database\Interfaces\FetchableTrait;
use Linuzilla\Database\Interfaces\LimitAndFetchable;
use Linuzilla\Database\Repositories\BaseRepository;

/**
 * Class HavingClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class HavingClause implements Fetchable, LimitAndFetchable {
    use FetchableTrait;

    private string $havingSentence;
    private array $havingArgs;

    public function __construct(
        private BaseRepository $baseRepository,
        private WhereClause $whereClause,
        private GroupByClause $groupByClause,
        BaseEntity|Logical|array $condition) {

        if (is_null($condition) or empty($condition)) {
            $this->havingSentence = '';
            $this->havingArgs = [];
        } else if ($condition instanceof Logical) {
            $queryAndArgs = $condition->buildQuery($baseRepository);
            $this->havingSentence = $queryAndArgs->query;
            $this->havingArgs = $queryAndArgs->args;
        } else {
            $data = new DataUnifier($condition);

            if ($data->count() == 0) {
                $this->havingSentence = '';
                $this->havingArgs = [];
            } else {
                $this->buildQuery($data);
            }
        }
    }

    private function buildQuery(DataUnifier $data) {
        $queryAndArgs = $data->buildQuery($this->baseRepository);
        $this->havingSentence = $queryAndArgs->query;
        $this->havingArgs = $queryAndArgs->args;
    }

    /**
     * order by
     * @param array|string|Op|Order $order
     * @return OrderClause
     */
    #[Pure]
    public function order(array|string|Op|Order $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this, $order);
    }

    /**
     * order by ascending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function asc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this, Order::asc($order));
    }

    /**
     * order by descending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function desc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this, Order::desc($order));
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return LimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): LimitClause {
        return new LimitClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this, null, $offset, $rowCount);
    }

    /**
     * @return string
     */
    public function getHavingSentence(): string {
        return $this->havingSentence;
    }

    /**
     * @return array
     */
    public function getHavingArgs(): array {
        return $this->havingArgs;
    }
}