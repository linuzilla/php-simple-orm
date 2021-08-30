<?php


namespace Linuzilla\Database\Repositories;


use Linuzilla\Database\Interfaces\DialectProvider;
use Linuzilla\Database\Interfaces\QueryLogger;
use PDO;

interface DataSource extends DialectProvider {
    public function pdo(): PDO;

    public function logger(): QueryLogger;
}