<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Interfaces\ComplexFetchableTrait;
use Linuzilla\Database\Interfaces\ComplexLimitAndFetchable;
use Linuzilla\Database\Repositories\AdvancedRepository;

/**
 * Class ComplexGroupByClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class ComplexGroupByClause implements ComplexLimitAndFetchable {
    use ComplexFetchableTrait;

    /** @var string[] $groupItems */
    private array $groupItems;

    public function __construct(
        private AdvancedRepository $baseRepository,
        private ComplexWhereClause $whereClause,
        array|string $groupItems) {
        $this->groupItems = is_array($groupItems) ? $groupItems : [$groupItems];
    }

    public function having(Logical|array $condition): ComplexHavingClause {
        return new ComplexHavingClause($this->baseRepository, $this->whereClause, $this, $condition);
    }

    /**
     * @param array|string|Order|Op $order
     * @return ComplexOrderClause
     */
    #[Pure]
    public function order(array|string|Order|Op $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this->whereClause, $this, null, $order);
    }

    /**
     * order by ascending
     * @param array|string|Op $order
     * @return ComplexOrderClause
     */
    public function asc(array|string|Op $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this->whereClause, $this, null, Order::asc($order));
    }

    /**
     * order by descending
     * @param array|string|Op $order
     * @return ComplexOrderClause
     */
    public function desc(array|string|Op $order): ComplexOrderClause {
        return new ComplexOrderClause($this->baseRepository, $this->whereClause, $this, null, Order::desc($order));
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return ComplexLimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): ComplexLimitClause {
        return new ComplexLimitClause($this->baseRepository, $this->whereClause, $this, null, null, $offset, $rowCount);
    }

    /**
     * @return string[]
     */
    public function getGroupItems(): array {
        return $this->groupItems;
    }
}