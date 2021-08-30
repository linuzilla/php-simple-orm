<?php


namespace Linuzilla\Database\Criterion;


/**
 * Class QueryWithArgs
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 */
class QueryWithArgs {
    public string $query;
    public array $args;

    /**
     * QueryWithArgs constructor.
     * @param string $query
     * @param array $args
     */
    public function __construct(string $query, array $args) {
        $this->query = $query;
        $this->args = $args;
    }
}