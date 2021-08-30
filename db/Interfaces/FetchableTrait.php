<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Clauses\GroupByClause;
use Linuzilla\Database\Clauses\HavingClause;
use Linuzilla\Database\Clauses\LimitClause;
use Linuzilla\Database\Clauses\OrderClause;
use Linuzilla\Database\Clauses\WhereClause;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Helpers\StringHelper;
use Linuzilla\Database\Models\ExtendedColumn;
use Linuzilla\Database\Repositories\BaseRepository;
use PDO;

/**
 * Trait FetchableTrait
 * @package Linuzilla\Database\Interfaces
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Mon 19 Jul 2021 11:55:25 PM UTC
 */
trait FetchableTrait {
    /**
     * @param $sentence
     * @return string
     */
    private function addWhereSentence($sentence): string {
        if (empty($sentence)) {
            return '';
        } else {
            return ' WHERE ' . $sentence;
        }
    }

    private function handleOrder(array $order): string {
        /** @var BaseRepository $base */
        $base = $this->baseRepository;

        return implode(',', array_map(function ($x) use ($base) {
            if ($x instanceof Order) {
                return $x->get($base->dialect());
            } else if ($x instanceof Op) {
                $result = '';
                try {
                    $x->apply($base, Op::ORDER_OPERATION, new ExtendedColumn('op','a'), function ($query, $columnName) use (&$result) {
                        $result = $query;
                    });
                } catch (DbException $e) {
                    error_log(sprintf("%s:%d - %s", __FILE__, __LINE__, $e->getMessage()));
                }
                return $result;
            } else {
                return $base->dialect()->quote($x);
            }
        }, $order));
    }

    private function composeQuery(): array {
        $query = '';
        $args = [];

        $whereInstance = $this instanceof WhereClause ? $this : $this->whereClause ?? null;

        if ($whereInstance !== null) {
            $query .= $this->addWhereSentence($whereInstance->getWhereSentence());
            $args = $whereInstance->getArgs();
        }

        $groupInstance = $this instanceof GroupByClause ? $this : $this->groupByClause ?? null;

        if ($groupInstance !== null) {
            $query .= ' GROUP BY ' . implode(", ", array_map(
                    fn($item) => $this->baseRepository->dialect()->quote($item),
                    $groupInstance->getGroupItems()
                ));
        }

        $havingInstance = $this instanceof HavingClause ? $this : $this->havingClause ?? null;

        if ($havingInstance !== null) {
            $query .= ' HAVING ' . $havingInstance->getHavingSentence();
            $args = array_merge($args, $havingInstance->getHavingArgs());
        }

        $orderInstance = $this instanceof OrderClause ? $this : $this->orderClause ?? null;

        if ($orderInstance !== null) {
            $query .= ' ORDER BY ' . $this->handleOrder($orderInstance->getOrder());
        }

        $limitInstance = $this instanceof LimitClause ? $this : null;

        if ($limitInstance !== null) {
            if ($limitInstance->getRowCount() <= 0) {
                $query .= sprintf(" LIMIT %d", $limitInstance->getOffset());
            } else {
                $query .= sprintf(" LIMIT %d,%d", $limitInstance->getOffset(), $limitInstance->getRowCount());
            }
        }

        return [$query, $args];
    }

    /**
     * @param callable $lambda
     * @return int
     * @throws DbException
     */
    public function fetch(callable $lambda): int {
        list ($where, $args) = $this->composeQuery();

        /** @var BaseRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT * FROM %s%s',
            $base->dialect()->quote($this->baseRepository->getTableName()),
            $where);

        return $base->fetch($query, $args, $lambda);

    }

    /**
     * @return array
     * @throws DbException
     */
    public function fetchAll(): array {
        $entities = [];

        $this->fetch(function ($row) use (&$entities) {
            $entities[] = $row;
        });
        return $entities;
    }

    /**
     * @param array $fields
     * @param callable $lambda
     * @return int
     * @throws DbException
     */
    public function select(array $fields, callable $lambda): int {
        list ($where, $args) = $this->composeQuery();

        /** @var BaseRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT %s FROM %s%s',
            StringHelper::sqlFieldsWithQuote($fields, $base->dialect()),
            $base->dialect()->quote($this->baseRepository->getTableName()),
            $where);

        return $base->fetch($query, $args, $lambda);
    }

    /**
     * @param array $fields
     * @return array
     * @throws DbException
     */
    public function selectAll(array $fields): array {
        $entities = [];

        $this->select($fields, function ($row) use (&$entities) {
            $entities[] = $row;
        });
        return $entities;
    }

    /**
     * @param string $field
     * @return int
     * @throws DbException
     */
    public function count(string $field = "*"): int {
        list ($where, $args) = $this->composeQuery();

        /* @var BaseRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT COUNT(%s) FROM %s%s',
            $field,
            $base->dialect()->quote($this->baseRepository->getTableName()),
            $where);

        $stmt = $base->query($query, $args);
        $rows = $stmt->fetch(PDO::FETCH_NUM);
        return intval($rows[0]);
    }
}