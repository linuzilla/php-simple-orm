<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Clauses\LimitClause;

/**
 * Interface LimitAndFetchable
 * @package Linuzilla\Database\Interfaces
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Mon 19 Jul 2021 11:55:25 PM UTC
 */
interface LimitAndFetchable extends Fetchable {
    public function limit(int $offset, int $rowCount = 0): LimitClause;
}