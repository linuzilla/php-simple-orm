<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Models\ExtendedColumn;

interface ColumnsAndDialectProvider extends DialectProvider {
    /**
     * @return string[]
     */
    public function getColumnNames(): array;

    /**
     * @return ExtendedColumn[]
     */
    public function getExtendedColumns(): array;
}