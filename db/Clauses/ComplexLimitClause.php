<?php


namespace Linuzilla\Database\Clauses;


use Linuzilla\Database\Interfaces\ComplexFetchable;
use Linuzilla\Database\Interfaces\ComplexFetchableTrait;
use Linuzilla\Database\Repositories\AdvancedRepository;

/**
 * Class ComplexLimitClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class ComplexLimitClause implements ComplexFetchable {
    use ComplexFetchableTrait;

    public function __construct(
        private AdvancedRepository $baseRepository,
        private ComplexWhereClause $whereClause,
        private ?ComplexGroupByClause $groupByClause,
        private ?ComplexHavingClause $havingClause,
        private ?ComplexOrderClause $orderClause,
        private int $offset,
        private int $rowCount) {
    }

    /**
     * @return int
     */
    public function getOffset(): int {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getRowCount(): int {
        return $this->rowCount;
    }
}