<?php


namespace Linuzilla\Database\Repositories;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Clauses\ComplexWhereClause;
use Linuzilla\Database\Criterion\Logical;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Dialects\DatabaseDialect;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;
use Linuzilla\Database\Interfaces\QueryLogger;
use Linuzilla\Database\Models\ExtendedColumn;
use Linuzilla\Database\Models\JoinInfo;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class AdvancedRepository
 * @package Linuzilla\Database\Repositories
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class AdvancedRepository implements ColumnsAndDialectProvider {
    /** @var JoinInfo[] */
    private array $joins;

    /** @var ExtendedColumn[] */
    private array $extendedColumns;

    /**
     * AdvancedRepository constructor.
     * @param BaseRepository $base
     * @param string $aliasName
     */
    #[Pure] private function __construct(
        private BaseRepository $base,
        private string $aliasName) {
        $this->joins = [new JoinInfo($base, $this->aliasName, [], JoinInfo::INIT)];
        $this->extendedColumns = $this->retrieveExtendColumns($base, $this->aliasName);
    }

    /**
     * @param ColumnsAndDialectProvider $provider
     * @param string $alias
     * @return ExtendedColumn[]
     */
    private function retrieveExtendColumns(ColumnsAndDialectProvider $provider, string $alias): array {
        return array_map(fn($columnName) => new ExtendedColumn($columnName, $alias), $provider->getColumnNames());
    }

    /**
     * @param BaseRepository $base
     * @param string $aliasName
     * @return AdvancedRepository
     */
    #[Pure] public static function create(BaseRepository $base, string $aliasName): AdvancedRepository {
        return new AdvancedRepository($base, $aliasName);
    }

    /**
     * @return DatabaseDialect
     */
    public function dialect(): DatabaseDialect {
        return $this->base->dialect();
    }

    /**
     * @param BaseRepository $anotherRepos
     * @param string $aliasName
     * @param array $condition
     * @return $this
     */
    public function join(BaseRepository $anotherRepos, string $aliasName, array $condition): AdvancedRepository {
        array_push($this->joins, new JoinInfo($anotherRepos, $aliasName, $condition, JoinInfo::JOIN));
        $this->extendedColumns = array_merge($this->extendedColumns, array_map(fn($columnName) => new ExtendedColumn($columnName, $aliasName), $anotherRepos->getColumnNames()));

        return $this;
    }

    /**
     * @param BaseRepository $anotherRepos
     * @param string $aliasName
     * @param array $condition
     * @return $this
     */
    public function leftJoin(BaseRepository $anotherRepos, string $aliasName, array $condition): AdvancedRepository {
        array_push($this->joins, new JoinInfo($anotherRepos, $aliasName, $condition, JoinInfo::LEFT_JOIN));
        $this->extendedColumns = array_merge($this->extendedColumns, array_map(fn($columnName) => new ExtendedColumn($columnName, $aliasName), $anotherRepos->getColumnNames()));

        return $this;
    }

    /**
     * @param Logical|array $condition
     * @return ComplexWhereClause
     */
    public function where(Logical|array $condition): ComplexWhereClause {
        return new ComplexWhereClause($this, $this->joins, $condition);
    }

    /**
     * @return ComplexWhereClause
     */
    public function all(): ComplexWhereClause {
        return self::where([]);
    }

    /**
     * @return QueryLogger
     */
    public function logger(): QueryLogger {
        return $this->base->logger();
    }

    /**
     * @return PDO
     */
    public function pdo(): PDO {
        return $this->base->pdo();
    }

    /**
     * @param string $query
     * @param array $args
     * @return PDOStatement
     * @throws DbException
     */
    public function query(string $query, array $args = []): PDOStatement {
        return $this->base->query($query, $args);
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

                while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
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

    public function getColumnNames(): array {
        return array_map(fn(ExtendedColumn $extendedColumn) => $extendedColumn->fullColumnName, $this->extendedColumns);
    }

    public function getExtendedColumns(): array {
        return $this->extendedColumns;
    }

    /**
     * @return string
     */
    public function getAliasName(): string {
        return $this->aliasName;
    }

    /**
     * @param string $columnName
     * @param string|null $alias
     * @return string
     */
    public function quote(string $columnName, string $alias = null): string {
        return (new ExtendedColumn($columnName, $alias ?? $this->aliasName))->q($this);
    }

    /**
     * @return string
     */
    public function getFromSentence(): string {
        ob_start();
        $prevAlias = '';

        foreach ($this->joins as $j) {
            switch ($j->joinType) {
                case JoinInfo::INIT:
                    printf("FROM %s %s",
                        $this->dialect()->quote($j->repos->getTableName()),
                        $j->alias);
                    break;

                /** @noinspection PhpMissingBreakStatementInspection */
                case JoinInfo::LEFT_JOIN:
                    echo ' LEFT';

                case JoinInfo::JOIN:

                    $joinConditions = [];

                    foreach ($j->condition as $k => $v) {
                        $joinConditions[] = sprintf("%s = %s",
                            $this->quote($k, $prevAlias),
                            $this->quote($v, $j->alias));
                    }
                    printf(" JOIN %s %s ON %s",
                        $this->dialect()->quote($j->repos->getTableName()),
                        $j->alias, implode(" AND ", $joinConditions));

            }
            $prevAlias = $j->alias;
        }
        $content = ob_get_contents();
        ob_clean();

        return $content;
    }
}