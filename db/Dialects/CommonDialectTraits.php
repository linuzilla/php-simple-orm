<?php


namespace Linuzilla\Database\Dialects;


use Linuzilla\Database\PdoConstants;
use PDO;

trait CommonDialectTraits {
    /**
     * @param PDO $pdo
     * @param string $databaseName
     * @return bool
     */
    public function useDatabase(PDO $pdo, string $databaseName): bool {
        $stmt = $pdo->query('use ' . $databaseName);
        if ($stmt !== false) {
            if ($stmt->errorCode() == PdoConstants::ERROR_CODE_SUCCESS) {
                return true;
            }
        }
        return false;
    }
}