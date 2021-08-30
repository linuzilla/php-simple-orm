<?php


namespace Linuzilla\Database\Dialects;


class DbDialectColumn {
    public string $columnName;
    public string $dataType;
    public string $columnType;
    public string $phpColumnType;
    public bool $nullable;
    public bool $autoIncrement;
    public bool $updateTimeStamp;

    /**
     * DbDialectColumn constructor.
     * @param string $columnName
     */
    public function __construct(string $columnName) {
        $this->columnName = $columnName;
    }
}