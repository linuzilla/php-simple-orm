<?php


namespace Linuzilla\Database\Interfaces;


use Exception;

/**
 * Interface QueryLogger
 * @package Linuzilla\Database\Interfaces
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Mon 19 Jul 2021 11:57:19 PM UTC
 */
interface QueryLogger {
    public function logQueryBeforeAction(string $query, array $args);

    public function logQuery(string $query, array $args, int $numOfRows);

    public function logUpdate(string $query, array $args, bool $success);

    public function logException(Exception $e);
}