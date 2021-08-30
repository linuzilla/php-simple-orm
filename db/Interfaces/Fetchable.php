<?php


namespace Linuzilla\Database\Interfaces;


/**
 * Interface Fetchable
 * @package Linuzilla\Database\Interfaces
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Mon 19 Jul 2021 11:55:25 PM UTC
 */
interface Fetchable {
    public function fetch(callable $lambda): int;

    public function fetchAll(): array;

    public function select(array $fields, callable $lambda): int;

    public function selectAll(array $fields): array;

    public function count(string $field = "*"): int;
}