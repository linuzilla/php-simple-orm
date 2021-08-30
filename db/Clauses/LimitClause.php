<?php


namespace Linuzilla\Database\Clauses;


use Linuzilla\Database\Interfaces\Fetchable;
use Linuzilla\Database\Interfaces\FetchableTrait;
use Linuzilla\Database\Repositories\BaseRepository;

/**
 * Class LimitClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class LimitClause implements Fetchable {
    use FetchableTrait;

    public function __construct(
        private BaseRepository $baseRepository,
        private WhereClause $whereClause,
        private ?GroupByClause $groupByClause,
        private ?HavingClause $havingClause,
        private ?OrderClause $orderClause,
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