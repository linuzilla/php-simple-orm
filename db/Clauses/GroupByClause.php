<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Entities\BaseEntity;
use Linuzilla\Database\Interfaces\Fetchable;
use Linuzilla\Database\Interfaces\FetchableTrait;
use Linuzilla\Database\Interfaces\LimitAndFetchable;
use Linuzilla\Database\Repositories\BaseRepository;

/**
 * Class GroupByClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class GroupByClause implements Fetchable, LimitAndFetchable {
    use FetchableTrait;

    /** @var string[] $groupItems */
    private array $groupItems;

    public function __construct(
        private BaseRepository $baseRepository,
        private WhereClause $whereClause,
        array|string $groupItems) {
        $this->groupItems = is_array($groupItems) ? $groupItems : [$groupItems];
    }

    /**
     * @return string[]
     */
    public function getGroupItems(): array {
        return $this->groupItems;
    }

    /**
     * @param BaseEntity|Logical|array $condition
     * @return HavingClause
     */
    public function having(BaseEntity|Logical|array $condition): HavingClause {
        return new HavingClause($this->baseRepository, $this->whereClause, $this, $condition);
    }

    /**
     * @param array|string|Order|Op $order
     * @return OrderClause
     */
    #[Pure]
    public function order(array|string|Order|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this, null, $order);
    }

    /**
     * order by ascending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function asc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this, null, Order::asc($order));
    }

    /**
     * order by descending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function desc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this->whereClause, $this, null, Order::desc($order));
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return LimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): LimitClause {
        return new LimitClause($this->baseRepository, $this->whereClause, $this, null, null, $offset, $rowCount);
    }
}