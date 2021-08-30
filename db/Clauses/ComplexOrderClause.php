<?php


namespace Linuzilla\Database\Clauses;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\Interfaces\ComplexFetchableTrait;
use Linuzilla\Database\Interfaces\ComplexLimitAndFetchable;
use Linuzilla\Database\Repositories\AdvancedRepository;

/**
 * Class ComplexOrderClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class ComplexOrderClause implements ComplexLimitAndFetchable {
    use ComplexFetchableTrait;

    private array $order;

    /**
     * OrderClause constructor.
     * @param AdvancedRepository $baseRepository
     * @param ComplexWhereClause $whereClause
     * @param ComplexGroupByClause|null $groupByClause
     * @param ComplexHavingClause|null $havingClause
     * @param array|string|Order|Op $order
     */
    public function __construct(
        private AdvancedRepository $baseRepository,
        private ComplexWhereClause $whereClause,
        private ?ComplexGroupByClause $groupByClause,
        private ?ComplexHavingClause $havingClause,
        array|string|Order|Op $order) {

        $this->order = is_array($order) ? $order : [$order];
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return ComplexLimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): ComplexLimitClause {
        return new ComplexLimitClause($this->baseRepository, $this->whereClause, $this->groupByClause, $this->havingClause, $this, $offset, $rowCount);
    }

    /**
     * @return array
     */
    public function getOrder(): array {
        return $this->order;
    }
}