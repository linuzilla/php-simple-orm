<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Clauses\ComplexGroupByClause;
use Linuzilla\Database\Clauses\ComplexHavingClause;
use Linuzilla\Database\Clauses\ComplexLimitClause;
use Linuzilla\Database\Clauses\ComplexOrderClause;
use Linuzilla\Database\Clauses\ComplexWhereClause;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\Order;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Models\ExtendedColumn;
use Linuzilla\Database\Repositories\AdvancedRepository;
use PDO;

trait ComplexFetchableTrait {
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
        /** @var AdvancedRepository $base */
        $base = $this->baseRepository;

        return implode(',', array_map(function ($x) use ($base) {
            if ($x instanceof Order) {
                return $x->get($base->dialect());
            } else if ($x instanceof Op) {
                $result = '';
                try {
                    $x->apply($base, Op::ORDER_OPERATION, '', function ($query, $columnName) use (&$result) {
                        $result = $query;
                    });
                } catch (DbException $e) {
                    error_log(sprintf("%s:%d - %s", __FILE__, __LINE__, $e->getMessage()));
                }
                return $result;
            } else {
                return $base->quote($x);
            }
        }, $order));
    }

    private function composeQuery(): array {
        /** @var AdvancedRepository $base */
        $base = $this->baseRepository;

        $query = '';
        $args = [];

        $whereInstance = $this instanceof ComplexWhereClause ? $this : $this->whereClause ?? null;

        if ($whereInstance !== null) {
            $query .= $this->addWhereSentence($whereInstance->getWhereSentence());
            $args = $whereInstance->getArgs();
        }

        $groupInstance = $this instanceof ComplexGroupByClause ? $this : $this->groupByClause ?? null;

        if ($groupInstance !== null) {
            $query .= ' GROUP BY ' . implode(", ", array_map(
                    fn($item) => $base->quote($item),
                    $groupInstance->getGroupItems()));
        }

        $havingInstance = $this instanceof ComplexHavingClause ? $this : $this->havingClause ?? null;

        if ($havingInstance !== null) {
            $query .= ' HAVING ' . $havingInstance->getHavingSentence();
            $args = array_merge($args, $havingInstance->getHavingArgs());
        }

        $orderInstance = $this instanceof ComplexOrderClause ? $this : $this->orderClause ?? null;

        if ($orderInstance !== null) {
            $query .= ' ORDER BY ' . $this->handleOrder($orderInstance->getOrder());
        }

        $limitInstance = $this instanceof ComplexLimitClause ? $this : null;

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

        /** @var AdvancedRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT %s %s%s',
            implode(",", array_map(fn(ExtendedColumn $extendedColumn) => $extendedColumn->as($base), $base->getExtendedColumns())),
            $base->getFromSentence(),
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
     * @param string[] $fields
     * @param callable $lambda
     * @return int
     * @throws DbException
     */
    public function select(array $fields, callable $lambda): int {
        list ($where, $args) = $this->composeQuery();

        /** @var AdvancedRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT %s %s%s',
            implode(",", array_map(fn(string $field) => (new ExtendedColumn($field, $base->getAliasName()))->as($base), $fields)),
            $base->getFromSentence(),
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

        /* @var AdvancedRepository $base */
        $base = $this->baseRepository;

        $query = sprintf('SELECT COUNT(%s) %s%s',
            $field == "*" ? $field : $base->quote($field),
            $base->getFromSentence(),
            $where);

        $stmt = $base->query($query, $args);
        $rows = $stmt->fetch(PDO::FETCH_NUM);
        return intval($rows[0]);
    }
}