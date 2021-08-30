<?php


namespace Linuzilla\Database;


use Exception;

class StderrQueryLoggerImpl implements Interfaces\QueryLogger {
    public function logQueryBeforeAction(string $query, array $args) {
        fprintf(STDERR, "%s:%d - Query: [ %s ]\n", basename(__FILE__), __LINE__, $query);
        if (isset($args) and count($args) > 0) {
            fwrite(STDERR, print_r($args, true));
        }
    }

    public function logQuery(string $query, array $args, int $numOfRows) {
    }

    public function logUpdate(string $query, array $args, bool $success) {
    }

    public function logException(Exception $e) {
        fprintf(STDERR, "%s:%d - Exception: %s\n", basename(__FILE__), __LINE__, $e->getMessage());
    }
}