<?php


namespace Linuzilla\Database;


use Exception;
use PDOException;
use PDOStatement;

/**
 * Class DbException
 * @package Linuzilla\Database
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 23:30:40 UTC 2021
 */
class DbException extends Exception {
    private PDOException $pdoException;
    private PDOStatement $pdoStatement;

    /**
     * DbException constructor.
     * @param PDOStatement|PDOException|string $e
     * @param PDOStatement|null $statement
     */
    public function __construct(PDOStatement|PDOException|string $e, ?PDOStatement $statement = null) {
        if (isset($statement)) {
            $this->pdoStatement = $statement;
        }

        if ($e instanceof PDOException) {
            $this->pdoException = $e;
            $this->message = $e->getMessage();
        } else if ($e instanceof PDOStatement) {
            $this->pdoStatement = $e;
            $errorInfo = $e->errorInfo();
            $this->message = $errorInfo[2];
        } else if (is_string($e)) {
            $this->message = $e;
        } else {
            $this->message = "something wrong";
        }
    }

    /**
     * @return PDOException
     */
    public function getPdoException(): PDOException {
        return $this->pdoException;
    }
}