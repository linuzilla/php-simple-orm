<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Clauses\ComplexLimitClause;

interface ComplexLimitAndFetchable extends ComplexFetchable {
    public function limit(int $offset, int $rowCount = 0): ComplexLimitClause;
}