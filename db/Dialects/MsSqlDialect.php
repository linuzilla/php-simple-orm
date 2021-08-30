<?php


namespace Linuzilla\Database\Dialects;


use Linuzilla\Database\DbException;
use Linuzilla\Database\Helpers\PdoHelper;
use PDO;

class MsSqlDialect implements DatabaseDialect {
    use CommonDialectTraits;

    /**
     * @param PDO $pdo
     * @return string|null
     */
    public function getCurrentDatabase(PDO $pdo): ?string {
        return PdoHelper::fetch_one_row_with_index($pdo, 'SELECT DB_NAME()');
    }

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @return array
     * @throws DbException
     */
    public function getTables(PDO $pdo, string $databaseName): array {
        $currentDatabase = $this->getCurrentDatabase($pdo);

        if ($currentDatabase != $databaseName) {
            $this->useDatabase($databaseName);
        }
        $tables = [];

        $stmt = PdoHelper::fetch_row(
            $pdo,
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_TYPE = 'BASE TABLE'", [],
            function (array $row) use (&$tables) {
                $table = new DbDialectTable($row[0]);
                $table->tableType = DbDialectTable::TABLE;

                $tables[] = $table;
            });

        if ($stmt !== false) {
            throw new DbException($stmt);
        }

        if ($currentDatabase != $databaseName) {
            $this->useDatabase($currentDatabase);
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
                $columns[] = $column;
            }
        );

        if ($stmt !== false) {
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
            "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_CATALOG=?  AND TABLE_NAME=? AND CONSTRAINT_NAME LIKE 'PK_%' ORDER BY ORDINAL_POSITION",
            [$databaseName, $tableName],
            function (array $row) use (&$primaryKeys) {
                $primaryKeys[] = $row[0];
            });

        if ($stmt !== false) {
            throw new DbException($stmt);
        }
        return $primaryKeys;
    }

    public function getAutoGenColumns(PDO $pdo, string $databaseName, string $tableName): array {
        // TODO: Implement getAutoGenColumns() method.
        return [];
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function quote(string $fieldName): string {
        return "[" . $fieldName . "]";
    }

    /**
     * @return string
     */
    public function lastInsertIdQuery(): string {
        // FIXME
        return 'SELECT LAST_INSERT_ID()';
    }
}