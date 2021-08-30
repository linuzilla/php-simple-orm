<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Dialects\DatabaseDialect;

interface DialectProvider {
    public function dialect(): DatabaseDialect;
}