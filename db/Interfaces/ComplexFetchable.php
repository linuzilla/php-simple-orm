<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\DbException;

interface ComplexFetchable {
    /**
     * @param callable $lambda
     * @return int
     * @throws DbException
     */
    public function fetch(callable $lambda): int;

    /**
     * @return array
     * @throws DbException
     */
    public function fetchAll(): array;

    /**
     * @param string[] $fields
     * @param callable $lambda
     * @return int
     * @throws DbException
     */
    public function select(array $fields, callable $lambda): int;

    /**
     * @param array $fields
     * @return array
     * @throws DbException
     */
    public function selectAll(array $fields): array;

    /**
     * @param string $field
     * @return int
     * @throws DbException
     */
    public function count(string $field = "*"): int;
}