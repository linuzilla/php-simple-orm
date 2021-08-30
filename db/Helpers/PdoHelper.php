<?php


namespace Linuzilla\Database\Helpers;


use PDO;
use PDOStatement;

class PdoHelper {
    /**
     * @param PDO $pdo
     * @param string $query
     * @return array|null
     */
    public static function fetch_one_row(PDO $pdo, string $query): ?array {
        $stmt = $pdo->query($query);

        if (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
            return $row;
        }
        return null;
    }

    /**
     * @param PDO $pdo
     * @param string $query
     * @param int $index
     * @return mixed
     */
    public static function fetch_one_row_with_index(PDO $pdo, string $query, int $index = 0): mixed {
        if (!is_null($row = self::fetch_one_row($pdo, $query))) {
            if (count($row) > $index) {
                return $row[$index];
            }
        }
        return null;
    }

    /**
     * @param PDO $pdo
     * @param string $query
     * @param array $args
     * @param int $mode
     * @param callable $lambda
     * @return false|PDOStatement
     */
    public static function fetch(PDO $pdo, string $query, array $args, int $mode, callable $lambda): false|PDOStatement {
        $stmt = $pdo->prepare($query);

        if ($stmt !== false) {
            if ($stmt->execute($args)) {
                while (($row = $stmt->fetch($mode)) != false) {
                    $lambda($row);
                }
            }
        }
        return $stmt;
    }

    /**
     * @param PDO $pdo
     * @param string $query
     * @param array $args
     * @param callable $lambda
     * @return false|PDOStatement
     */
    public static function fetch_row(PDO $pdo, string $query, array $args, callable $lambda): false|PDOStatement {
        return self::fetch($pdo, $query, $args, PDO::FETCH_NUM, $lambda);
    }

    /**
     * @param PDO $pdo
     * @param string $query
     * @param array $args
     * @param callable $lambda
     * @return false|PDOStatement
     */
    public static function fetch_assoc(PDO $pdo, string $query, array $args, callable $lambda): false|PDOStatement {
        return self::fetch($pdo, $query, $args, PDO::FETCH_ASSOC, $lambda);
    }

    /**
     * @param PDO $pdo
     * @param string $query
     * @param array $args
     * @param callable $lambda
     * @return false|PDOStatement
     */
    public static function fetch_array(PDO $pdo, string $query, array $args, callable $lambda): false|PDOStatement {
        return self::fetch($pdo, $query, $args, PDO::FETCH_BOTH, $lambda);
    }
}