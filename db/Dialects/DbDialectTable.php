<?php


namespace Linuzilla\Database\Dialects;


class DbDialectTable {
    const TABLE = 'table';
    const VIEW = 'view';

    public $tableName;
    public $tableType;

    /**
     * DbDialectTable constructor.
     * @param $tableName
     */
    public function __construct($tableName) {
        $this->tableName = $tableName;
    }
}