<?php


namespace Linuzilla\Database\Repositories;


use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Attributes\Expose;
use Linuzilla\Database\Clauses\WhereClause;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Entities\BaseEntity;
use Linuzilla\Database\Helpers\DataUnifier;
use Linuzilla\Database\Helpers\StringHelper;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;
use Linuzilla\Database\Interfaces\Repository;
use Linuzilla\Database\Models\ExtendedColumn;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;

/**
 * Class BaseRepository
 * @package Linuzilla\Database\Repositories
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 23:29:33 UTC 2021
 */
abstract class BaseRepository implements DataSource, Repository, ColumnsAndDialectProvider {
    protected ReflectionClass $ref;
    protected BaseEntity $entity;
    protected string $clazzName;
    /** @var ExtendedColumn[] */
    protected array $extendedColumns;


    /**
     * BaseRepository constructor.
     * @param BaseEntity $entity
     */
    public function __construct(BaseEntity $entity) {
        $this->entity = $entity;
        $this->ref = new ReflectionClass($entity);
        $this->clazzName = get_class($entity);
    }

    /**
     * @return string
     */
    public function getTableName(): string {
        return $this->entity->__getTableName($this->ref);
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey(): array {
        return $this->entity->__getPrimaryKeys($this->ref);
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array {
        return $this->entity->__getColumnNames($this->ref);
    }

    /**
     * @return string[]
     */
    public function getAutoIncrementColumns(): array {
        return $this->entity->__getAutoIncrement($this->ref);
    }

    /**
     * @return ExtendedColumn[]
     */
    public function getExtendedColumns(): array {
        if (!isset($this->extendedColumns)) {
            $this->extendedColumns = array_map(fn($columnName) => new ExtendedColumn($columnName, ''), $this->getColumnNames());
        }
        return $this->extendedColumns;
    }

    /**
     * @param string $query
     * @param array $args
     * @return array
     * @throws DbException
     */
    public function fetchAll(string $query, array $args): array {
        $entries = [];
        $this->fetch($query, $args, function ($row) use (&$entries) {
            $entries[] = $row;
        });
        return $entries;
    }

    /**
     * @param string $query
     * @param array $args
     * @return PDOStatement
     * @throws DbException
     */
    public function query(string $query, array $args = []): PDOStatement {
        $this->logger()->logQueryBeforeAction($query, $args);

        try {
            if (count($args) == 0) {
                $stmt = $this->pdo()->query($query);
            } else {
                $stmt = $this->pdo()->prepare($query);

                if ($stmt !== false) {
                    $stmt->execute($args);
                }
            }
            if ($stmt !== false) {
                return $stmt;
            } else {
                $e = new DbException($stmt);
                $this->logger()->logException($e);
                throw $e;
            }
        } catch (PDOException $e) {
            $this->logger()->logException($e);
            throw new DbException($e);
        }
    }

    /**
     * @param string $query
     * @param array $args
     * @param callable $receiver
     * @return int
     * @throws DbException
     */
    public function fetch(string $query, array $args, callable $receiver): int {
        $this->logger()->logQueryBeforeAction($query, $args);

        try {
            if (count($args) == 0) {
                $stmt = $this->pdo()->query($query);
            } else {
                $stmt = $this->pdo()->prepare($query);

                if ($stmt !== false) {
                    $stmt->execute($args);
                }
            }
            if ($stmt !== false) {
                $counter = 0;
                $stmt->setFetchMode(PDO::FETCH_CLASS, $this->clazzName);

                while (($row = $stmt->fetch()) !== false) {
                    $receiver($row);
                    $counter++;
                }
                $this->logger()->logQuery($query, $args, $counter);
                return $counter;
            } else {
                $e = new DbException($stmt);
                $this->logger()->logException($e);
                throw $e;
            }
        } catch (PDOException $e) {
            $this->logger()->logException($e);
            throw new DbException($e);
        }
    }

    /**
     * @param string $query
     * @param array $args
     * @return PDOStatement|false
     * @throws DbException
     */
    public function performUpdateQuery(string $query, array $args): PDOStatement|false {
        $this->logger()->logQueryBeforeAction($query, $args);

        $stmt = null;

        try {
            if (count($args) == 0) {
                $stmt = $this->pdo()->query($query);
                $this->logger()->logUpdate($query, $args, true);
                return $stmt;
            } else {
                $stmt = $this->pdo()->prepare($query);

                if ($stmt !== false) {
                    if ($stmt->execute($args) !== false) {
                        $this->logger()->logUpdate($query, $args, true);
                    } else {
                        $this->logger()->logUpdate($query, $args, false);
                        return false;
                    }
                    return $stmt;
                } else {
                    return false;
                }
            }
        } catch (PDOException $e) {
            $e = new DbException($e, $stmt);
            $this->logger()->logException($e);
            throw $e;
        }
    }

    /**
     * @param BaseEntity|array $entity
     * @param string $query
     * @param array $args
     * @return BaseEntity|false
     * @throws DbException
     */
    private function performInsertQuery(BaseEntity|array $entity, string $query, array $args): BaseEntity|false {
        $stmt = $this->performUpdateQuery($query, $args);

        if ($stmt !== false) {
            $counter = $stmt->rowCount();

            if ($counter > 0) {
                $auto = $this->getAutoIncrementColumns();

                if (count($auto) == 1) {
                    $unifier = new DataUnifier($entity);
                    $autoField = $auto[0];

                    if (!$unifier->isset($autoField)) {
                        $lastInsertQuery = $this->dialect()->lastInsertIdQuery();
                        $lastStmt = $this->pdo()->query($lastInsertQuery);
                        $this->logger()->logQueryBeforeAction($lastInsertQuery, []);
                        if (($row = $lastStmt->fetch(PDO::FETCH_NUM)) !== false) {
                            return $this->findById($row[0]);
                        } else {
                            $this->logger()->logException(new DbException($lastStmt));
                        }
                    } else {
                        return $this->findById($unifier->get($autoField));
                    }
                } else {
                    $rows = $this->find($entity);

                    switch (count($rows)) {
                        case 1:
                            return $rows[0];
                        case 0:
                            throw new DbException("could not find entry after insert");
                        default:
                            throw new DbException(sprintf("multiple (%d) match after insert", count($rows)));
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param BaseEntity|Logical|array $condition
     * @return WhereClause
     */
    public function where(BaseEntity|Logical|array $condition): WhereClause {
        return new WhereClause($this, $condition);
    }

    /**
     * @return WhereClause
     */
    public function all(): WhereClause {
        return new WhereClause($this, []);
    }

    /**
     * @param BaseEntity $obj
     * @return BaseEntity[]
     * @throws DbException
     */
    #[Expose]
    public function findByExample(BaseEntity $obj): array {
        return $this->where($obj)->fetchAll();
    }

    /**
     * @return BaseEntity[]
     * @throws DbException
     */
    #[Expose]
    public function findAll(): array {
        return $this->fetchAll(
            sprintf('SELECT * FROM %s', $this->dialect()->quote($this->getTableName())),
            []);
    }

    /**
     * @param BaseEntity|array $arg
     * @return array
     * @throws DbException
     */
    #[ArrayShape([
        0 => "string",
        1 => "array",
    ])]
    private function internalPrepareInsertQuery(BaseEntity|array $arg): array {
        $entity = new DataUnifier($arg);

        $columns = [];
        $questionMarks = [];
        $values = [];

        foreach ($this->getExtendedColumns() as $columnName) {
            $entity->richColumnValue($this, $columnName, Op::INSERT_OPERATION,
                function ($queryPart, $valuePart) use ($columnName, &$columns, &$values, &$questionMarks) {
                    $columns[] = $columnName->fullColumnName;

                    if (is_null($queryPart)) {
                        if (!is_null($valuePart)) {
                            $values[] = $valuePart;
                            $questionMarks[] = '?';
                        }
                    } else {
                        $questionMarks[] = $queryPart;
                    }
                });
        }

        if (count($columns) > 0) {
            return [sprintf(" INTO %s (%s) VALUES (%s)",
                $this->dialect()->quote($this->getTableName()),
                StringHelper::array_convert_and_join($columns, ',', function ($k) {
                    return $this->dialect()->quote($k);
                }),
                implode(',', $questionMarks)), $values];
        } else {
            throw new DbException("no value");
        }
    }

    /**
     * INSERT IGNORE INTO
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function saveOrIgnore(BaseEntity|array $entity): BaseEntity|false {
        list ($query, $values) = $this->internalPrepareInsertQuery($entity);
        return $this->performInsertQuery($entity, 'INSERT IGNORE' . $query, $values);
    }

    /**
     * INSERT INTO ...
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function save(BaseEntity|array $entity): BaseEntity|false {
        list ($query, $values) = $this->internalPrepareInsertQuery($entity);
        return $this->performInsertQuery($entity, 'INSERT' . $query, $values);
    }

    /**
     * REPLACE INTO ...
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function saveOrOverwrite(BaseEntity|array $entity): BaseEntity|false {
        list ($query, $values) = $this->internalPrepareInsertQuery($entity);
        return $this->performInsertQuery($entity, 'REPLACE' . $query, $values);
    }

    /**
     * @param BaseEntity|array $entity
     * @param BaseEntity|array|null $onUpdate
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function saveOrUpdate(BaseEntity|array $entity, BaseEntity|array|null $onUpdate = null): BaseEntity|false {
        list($query, $values) = $this->internalPrepareInsertQuery($entity);

        $data = new DataUnifier(!is_null($onUpdate) ? $onUpdate : $entity);

        $columns = [];

        foreach ($this->getExtendedColumns() as $columnName) {
            if (!in_array($columnName->columnName, $this->getPrimaryKey())) {
                $data->richColumnValue($this, $columnName, Op::UPDATE_OPERATION,
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

        return $this->performInsertQuery(
            $entity,
            sprintf("INSERT%s ON DUPLICATE KEY UPDATE %s", $query, implode(', ', $columns)),
            $values);
    }

    /**
     * @param BaseEntity|Logical|array $condition
     * @param array $updateFields
     * @return int|false
     * @throws DbException
     */
    public function conditionalUpdate(BaseEntity|Logical|array $condition, array $updateFields): int|false {
        $stmt = (new WhereClause($this, $condition))->update($updateFields);

        if ($stmt !== false) {
            return $stmt->rowCount();
        }
        return false;
    }


    /**
     * @param BaseEntity|array $entity Be careful! <b>ONLY primary key</b> will be the update condition.
     * @param array $updateFields Optional, if not specify, update fields in the first parameter which is not primary key.
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function update(BaseEntity|array $entity, array $updateFields = []): BaseEntity|false {
        $data = new DataUnifier($entity);

        $pk = [];
        $update = [];
        $values = [];

        if (count($updateFields) == 0) {
            foreach ($this->getExtendedColumns() as $columnName) {
                if ($data->isset($columnName->columnName)) {
                    if (!in_array($columnName, $this->getPrimaryKey())) {
//                        $update[] = $this->dialect()->quote($columnName) . '=?';
//                        $values[] = $data->get($columnName);

                        $data->richColumnValue($this, $columnName, Op::UPDATE_OPERATION,
                            function (string $columnPart, $valuePart) use (&$update, &$values) {
                                if (!is_null($columnPart)) {
                                    $update[] = $columnPart;
                                }
                                if (!is_null($valuePart)) {
                                    $values[] = $valuePart;
                                }
                            });
                    }
                }
            }
        } else {
            $updateData = new DataUnifier($updateFields);

            foreach ($this->getExtendedColumns() as $columnName) {
                if (!in_array($columnName, $this->getPrimaryKey())) {
                    if (isset($updateFields[$columnName->columnName])) {
//                        $update[] = $this->dialect()->quote($columnName) . '=?';
//                        $values[] = $updateFields[$columnName];
                        $updateData->richColumnValue($this, $columnName, Op::UPDATE_OPERATION,
                            function (string $columnPart, $valuePart) use (&$update, &$values) {
                                if (!is_null($columnPart)) {
                                    $update[] = $columnPart;
                                }
                                if (!is_null($valuePart)) {
                                    $values[] = $valuePart;
                                }
                            });
                    }
                }
            }
        }

        foreach ($this->getPrimaryKey() as $columnName) {
            if (!$data->isset($columnName)) {
                throw new DbException("primary key should exists on update");
            }
            $pk[] = $this->dialect()->quote($columnName) . '=?';
            $values[] = $data->get($columnName);
        }

        $stmt = $this->performUpdateQuery(
            sprintf("UPDATE %s SET %s WHERE %s",
                $this->dialect()->quote($this->getTableName()),
                implode(',', $update),
                implode(' AND ', $pk)),
            $values);

        if ($stmt !== false) {
            $entities = $this->find($entity);
            if (count($entities) == 1) {
                return $entities[0];
            } else {
                error_log(sprintf("%s:%d - update and find: got %d", __FILE__, __LINE__, count($entities)));
            }
        }
        return false;
    }

    /**
     * @param BaseEntity|array $entity
     * @return bool
     * @throws DbException
     */
    public function delete(BaseEntity|array $entity): bool {
        if (is_array($entity)) {
            return $this->deleteById($entity);
        } else {
            return $this->performUpdateQuery(
                    sprintf('DELETE FROM %s WHERE %s',
                        $this->dialect()->quote($this->getTableName()),
                        $this->primaryKeyWhereString()),
                    $this->primaryKeyValuesByEntity($entity)
                ) !== false;

        }
    }

    /**
     * @param int|string|array $id
     * @return bool
     * @throws DbException
     */
    public function deleteById(int|string|array $id): bool {
        return $this->performUpdateQuery(
                sprintf('DELETE FROM %s WHERE %s',
                    $this->dialect()->quote($this->getTableName()),
                    $this->primaryKeyWhereString()),
                $this->primaryKeyValuesOnById($id)
            ) !== false;
    }

    /**
     * @param BaseEntity|array $entity
     * @return BaseEntity[]|false
     * @throws DbException
     */
    #[Expose]
    public function find(BaseEntity|array $entity): array|false {
        $values = is_array($entity)
            ? $this->primaryKeyValuesByArray($entity)
            : $this->primaryKeyValuesByEntity($entity);

        return $this->fetchAll(
            sprintf('SELECT * FROM %s WHERE %s',
                $this->dialect()->quote($this->getTableName()),
                $this->primaryKeyWhereString()),
            $values);
    }

    /**
     * @param int|string|array $id
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function findById(int|string|array $id): BaseEntity|false {
        $values = $this->primaryKeyValuesOnById($id);

        $query = sprintf('SELECT * FROM %s WHERE %s',
            $this->dialect()->quote($this->getTableName()),
            $this->primaryKeyWhereString());

        $entry = $this->fetchAll($query, $values);

        if (count($entry) == 0) {
            return false;
        } else if (count($entry) > 1) {
            throw new DbException(sprintf("findById should have only one result, got %d", count($entry)));
        } else {
            return $entry[0];
        }
    }

    /**
     * @return string
     */
    private function primaryKeyWhereString(): string {
        return StringHelper::array_convert_and_join($this->getPrimaryKey(), ' AND ', function ($k) {
            return sprintf("%s=?", $this->dialect()->quote($k));
        });
    }

    /**
     * @param BaseEntity $entity
     * @return array
     */
    private function primaryKeyValuesByEntity(BaseEntity $entity): array {
        $values = [];
        foreach ($this->getPrimaryKey() as $pk) {
            if (isset($entity->$pk)) {
                $values[] = $entity->$pk;
            }
        }
        return $values;
    }

    /**
     * @param array $entity
     * @return array
     */
    private function primaryKeyValuesByArray(array $entity): array {
        $values = [];
        foreach ($this->getPrimaryKey() as $pk) {
            if (isset($entity[$pk])) {
                $values[] = $entity[$pk];
            }
        }
        return $values;
    }

    /**
     * @param int|string|array $id
     * @return array
     * @throws DbException
     */
    private function primaryKeyValuesOnById(int|string|array $id): array {
        $pk = $this->getPrimaryKey();

        switch (count($pk)) {
            case 0:
                throw new DbException(sprintf("Table '%s' did not have primary key", $this->getTableName()));

            case 1:
                if (is_int($id) or is_string($id)) {
                    return [$id];
                } else {
                    throw new DbException(sprintf("Table '%s' use simple primary key, input should be integer or string",
                        $this->getTableName()));
                }

            default:
                if (is_int($id) or is_string($id)) {
                    throw new DbException(sprintf("Table '%s' use composite primary key: %s",
                        $this->getTableName(),
                        StringHelper::array_backquoted_and_join($pk, ',')));
                } else if (count($id) != count($pk)) {
                    throw new DbException(sprintf("Table '%s use %d composite key, %d given",
                        $this->getTableName(),
                        count($pk),
                        count($id)));
                } else {
                    $values = [];

                    foreach ($pk as $k) {
                        if (isset($id[$k])) {
                            $values[] = $id[$k];
                        } else {
                            throw new DbException(sprintf("Table '%s use composite key %s, %s not found",
                                $this->getTableName(),
                                StringHelper::array_backquoted_and_join($pk, ','), $k));
                        }
                    }
                    return $values;
                }
        }
    }

    /**
     * @param callable $callable
     * @return bool
     * @throws DbException
     */
    public function transaction(callable $callable): bool {
        try {
            $this->query("START TRANSACTION");

            try {
                $callable();
                $this->query("COMMIT");
                return true;
            } catch (Exception $e) {
                $this->query("ROLLBACK");

                if ($e instanceof DbException) {
                    throw $e;
                } else if ($e instanceof PDOException) {
                    throw new DbException($e);
                }
                return false;
            }
        } catch (DbException $e) {
            throw $e;
        }
    }

    /**
     * @param string $aliasName
     * @return AdvancedRepository
     */
    #[Pure] public function as(string $aliasName): AdvancedRepository {
        return AdvancedRepository::create($this, $aliasName);
    }
}