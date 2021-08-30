<?php


namespace Linuzilla\Database\Dialects;


use Linuzilla\Database\DbException;
use PDO;

interface DatabaseDialect {
    /**
     * @param PDO $pdo
     * @return string|null
     */
    public function getCurrentDatabase(PDO $pdo): ?string;

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @return bool
     */
    public function useDatabase(PDO $pdo, string $databaseName): bool;

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @return array
     * @throws DbException
     */
    public function getTables(PDO $pdo, string $databaseName): array;

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getColumns(PDO $pdo, string $databaseName, string $tableName): array;

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @param string $tableName
     * @return array
     * @throws DbException
     */
    public function getPrimaryKeys(PDO $pdo, string $databaseName, string $tableName): array;

    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @param string $tableName
     * @return array
     */
    public function getAutoGenColumns(PDO $pdo, string $databaseName, string $tableName): array;

    /**
     * @param string $fieldName
     * @return string
     */
    public function quote(string $fieldName): string;

    public function lastInsertIdQuery(): string;
}