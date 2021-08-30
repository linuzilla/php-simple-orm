<?php


namespace Linuzilla\Database\Dialects;


use Linuzilla\Database\DbException;
use Linuzilla\Database\Helpers\PdoHelper;
use PDO;

class MySqlDialect implements DatabaseDialect {
    use CommonDialectTraits;

    /**
     * @param PDO $pdo
     * @return string|null
     */
    public function getCurrentDatabase(PDO $pdo): ?string {
        return PdoHelper::fetch_one_row_with_index($pdo, 'SELECT DATABASE()');
    }

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @return array
     * @throws DbException
     */
    public function getTables(PDO $pdo, string $databaseName): array {
        $tables = [];

        $stmt = PdoHelper::fetch_row(
            $pdo,
            "SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_TYPE IN ('BASE TABLE','VIEW') AND TABLE_SCHEMA=?",
            [$databaseName],
            function (array $row) use (&$tables) {
                $table = new DbDialectTable($row[0]);
                $table->tableType = match ($row[1]) {
                    'VIEW' => DbDialectTable::VIEW,
                    default => DbDialectTable::TABLE
                };
                $tables[] = $table;
            });

        if ($stmt === false) {
            throw new DbException($stmt);
        }
        return $tables;
    }

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getColumns(PDO $pdo, string $databaseName, string $tableName): array {
        $columns = [];

        $stmt = PdoHelper::fetch_assoc(
            $pdo,
            "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? ORDER BY ORDINAL_POSITION",
            [$tableName, $databaseName],
            function (array $row) use (&$columns) {
                $column = new DbDialectColumn($row['COLUMN_NAME']);

                $column->columnType = $row['COLUMN_TYPE'];
                $column->dataType = $row['DATA_TYPE'];
                $column->nullable = $row['IS_NULLABLE'] == 'YES';
                $column->autoIncrement = str_contains($row['EXTRA'], 'auto_increment');
                $column->updateTimeStamp = str_contains($row['EXTRA'], 'on update CURRENT_TIMESTAMP');

                $column->phpColumnType = match ($column->dataType) {
                    'int', 'smallint', 'tinyint' => 'int',
                    default => 'string',
                };
                $columns[] = $column;
            }
        );

        if ($stmt === false) {
            throw new DbException($stmt);
        }
        return $columns;
    }

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getPrimaryKeys(PDO $pdo, string $databaseName, string $tableName): array {
        $primaryKeys = [];

        $stmt = PdoHelper::fetch_row(
            $pdo,
            "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME = 'PRIMARY' ORDER BY ORDINAL_POSITION",
            [$databaseName, $tableName],
            function (array $row) use (&$primaryKeys) {
                $primaryKeys[] = $row[0];
            });

        if ($stmt === false) {
            throw new DbException($stmt);
        }
        return $primaryKeys;
    }

    public function getAutoGenColumns(PDO $pdo, string $databaseName, string $tableName): array {
        $autoIncrements = [];

        $stmt = PdoHelper::fetch_row(
            $pdo,
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND EXTRA LIKE '%auto_increment%' ORDER BY ORDINAL_POSITION",
            [$databaseName, $tableName],
            function (array $row) use (&$autoIncrements) {
                $autoIncrements[] = $row[0];
            });

        if ($stmt === false) {
            throw new DbException($stmt);
        }
        return $autoIncrements;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function quote(string $fieldName): string {
        return "`" . $fieldName . "`";
    }

    /**
     * @return string
     */
    public function lastInsertIdQuery(): string {
        return 'SELECT LAST_INSERT_ID()';
    }
}