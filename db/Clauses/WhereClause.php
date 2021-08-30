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
use PDOStatement;

/**
 * Class WhereClause
 * @package Linuzilla\Database\Clauses
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class WhereClause implements Fetchable, LimitAndFetchable {
    use FetchableTrait;

    private BaseRepository $baseRepository;
    private string $whereSentence;
    private array $args;


    /**
     * WhereClause constructor.
     * @param BaseRepository $baseRepository
     * @param BaseEntity|Logical|array $condition
     */
    public function __construct(BaseRepository $baseRepository, BaseEntity|Logical|array $condition) {
        $this->baseRepository = $baseRepository;

        if (is_null($condition) or empty($condition)) {
            $this->whereSentence = '';
            $this->args = [];
        } else if ($condition instanceof Logical) {
            $queryAndArgs = $condition->buildQuery($baseRepository);
            $this->whereSentence = $queryAndArgs->query;
            $this->args = $queryAndArgs->args;
        } else {
            $data = new DataUnifier($condition);

            if ($data->count() == 0) {
                $this->whereSentence = '';
                $this->args = [];
            } else {
                $this->buildQuery($data);
            }
        }
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
     * @return bool|PDOStatement
     * @throws \Linuzilla\Database\DbException
     */
    public function delete(): bool|PDOStatement {
        return $this->baseRepository->performUpdateQuery(
            sprintf("DELETE FROM %s WHERE %s",
                $this->baseRepository->dialect()->quote($this->baseRepository->getTableName()),
                $this->getWhereSentence()),
            $this->args);
    }

    /**
     * @param BaseEntity|array $entity
     * @return bool|PDOStatement
     * @throws \Linuzilla\Database\DbException
     */
    public function update(BaseEntity|array $entity): bool|PDOStatement {
        $values = [];
        $columns = [];

        $data = new DataUnifier($entity);

        foreach ($this->baseRepository->getExtendedColumns() as $fieldName) {
            if ($data->isset($fieldName->fullColumnName)) {
                $data->richColumnValue($this->baseRepository, $fieldName,
                    Op::UPDATE_OPERATION,
                    function (string $columnPart, $valuePart) use (&$columns, &$values) {
                        if (!is_null($columnPart)) {
                            $columns[] = $columnPart;
                        }
                        if (!is_null($valuePart)) {
                            $values[] = $valuePart;
                        }
                    });
            }
        }

        return $this->baseRepository->performUpdateQuery(
            sprintf("UPDATE %s SET %s WHERE %s",
                $this->baseRepository->dialect()->quote($this->baseRepository->getTableName()),
                implode(', ', $columns),
                $this->getWhereSentence()),
            array_merge($values, $this->args));
    }

    /**
     * order by
     * @param array|string|Op|Order $order
     * @return OrderClause
     */
    #[Pure]
    public function order(array|string|Op|Order $order): OrderClause {
        return new OrderClause($this->baseRepository, $this, null, null, $order);
    }

    /**
     * order by ascending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function asc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this, null, null, Order::asc($order));
    }

    /**
     * order by descending
     * @param array|string|Op $order
     * @return OrderClause
     */
    public function desc(array|string|Op $order): OrderClause {
        return new OrderClause($this->baseRepository, $this, null, null, Order::desc($order));
    }

    /**
     * @param int $offset
     * @param int $rowCount
     * @return LimitClause
     */
    #[Pure]
    public function limit(int $offset, int $rowCount = 0): LimitClause {
        return new LimitClause($this->baseRepository, $this, null, null, null, $offset, $rowCount);
    }

    /**
     * @param array|string $groupItems
     * @return GroupByClause
     */
    #[Pure] public function groupBy(array|string $groupItems): GroupByClause {
        return new GroupByClause($this->baseRepository, $this, $groupItems);
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