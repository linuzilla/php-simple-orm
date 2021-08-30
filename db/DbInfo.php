<?php


namespace Linuzilla\Database;


use Linuzilla\Database\Dialects\DatabaseDialect;
use PDO;

class DbInfo {
    private PDO $pdo;
    private DatabaseDialect $dialect;

    /**
     * DbInfo constructor.
     * @param PDO $pdo
     * @param DatabaseDialect $dialect
     */
    public function __construct(PDO $pdo, DatabaseDialect $dialect) {
        $this->pdo = $pdo;
        $this->dialect = $dialect;
    }

    /**
     * @param string $databaseName
     * @throws DbException
     */
    private function checkDbName(string $databaseName) {
        if (!preg_match('/^[-_a-zA-Z0-9]+$/', $databaseName)) {
            throw new DbException("Database: '$databaseName' not allowed");
        }
    }

    /**
     * @param $tableName
     * @throws DbException
     */
    private function checkTableName($tableName) {
        if (!preg_match('/^[-_a-zA-Z0-9]+$/', $tableName)) {
            throw new DbException("Table: '$tableName' not allowed");
        }
    }

    /**
     * @return array
     */
    public function getDatabases(): array {
        $databases = [];

//        $stmt = $this->pdo->query('SELECT CATALOG_NAME FROM information_schema.SCHEMATA');
        $stmt = $this->pdo->query('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA');

        while (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
            $databases[] = $row[0];
        }

        return $databases;
    }

    /**
     * @param string $databaseName
     * @return array
     * @throws DbException
     */
    public function getTables(string $databaseName): array {
        $this->checkDbName($databaseName);
        return $this->dialect->getTables($this->pdo, $databaseName);
    }

    /**
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getColumns(string $databaseName, string $tableName): array {
        $this->checkDbName($databaseName);
        $this->checkTableName($tableName);

        return $this->dialect->getColumns($this->pdo, $databaseName, $tableName);
    }

    /**
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getPrimaryKeys(string $databaseName, string $tableName): array {
        $this->checkDbName($databaseName);
        $this->checkTableName($tableName);

        return $this->dialect->getPrimaryKeys($this->pdo, $databaseName, $tableName);
    }

    public function getAutoGenColumns(string $databaseName, string $tableName): array {
        $this->checkDbName($databaseName);
        $this->checkTableName($tableName);

        return $this->dialect->getAutoGenColumns($this->pdo, $databaseName, $tableName);
    }
}