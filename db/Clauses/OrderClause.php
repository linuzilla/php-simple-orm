<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Interfaces\Fetchable;
use Linuzilla\Database\Interfaces\FetchableTrait;
use Linuzilla\Database\Interfaces\LimitAndFetchable;
use Linuzilla\Database\Repositories\BaseRepository;

/**
 * Class OrderClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class OrderClause implements Fetchable, LimitAndFetchable {
    use FetchableTrait;

    private array $order;

    /**
     * OrderClause constructor.
     * @param BaseRepository $baseRepository
     * @param WhereClause $whereClause
     * @param GroupByClause|null $groupByClause
     * @param HavingClause|null $havingClause
     * @param array|string|Order|Op $order
     */
    public function __construct(
        private BaseRepository $baseRepository,
        private WhereClause $whereClause,
        private ?GroupByClause $groupByClause,
        private ?HavingClause $havingClause,
        array|string|Order|Op $order) {

        $this->baseRepository = $baseRepository;
        $this->whereClause = $whereClause;
        $this->order = is_array($order) ? $order : [$order];
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return LimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): LimitClause {
        return new LimitClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this->havingClause, $this, $offset, $rowCount);
    }

    /**
     * @return array
     */
    public function getOrder(): array {
        return $this->order;
    }
}