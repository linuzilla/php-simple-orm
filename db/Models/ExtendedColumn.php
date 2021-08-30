<?php


namespace Linuzilla\Database\Models;


use Linuzilla\Database\Dialects\DatabaseDialect;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;

/**
 * Class ExtendedColumn
 * @package Linuzilla\Database\Models
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class ExtendedColumn {
    public string $aliasName;
    public string $columnName;
    public string $fullColumnName;

    /**
     * TableAliasAndColumn constructor.
     */
    public function __construct(string $columnName, string $defaultAlias) {
        if (preg_match("/^([a-zA-Z][a-zA-Z0-9]*)" . Qn::delimiter() . "(.*)$/", $columnName, $matches)) {
            $this->aliasName = $matches[1];
            $this->columnName = $matches[2];
        } else {
            $this->aliasName = $defaultAlias;
            $this->columnName = $columnName;
        }
        $this->fullColumnName = empty($this->aliasName) ? $columnName : Qn::q($this->aliasName, $columnName);
    }

    /**
     * @param DatabaseDialect $dialect
     * @return string
     */
    public function quote(DatabaseDialect $dialect): string {
        if (empty($this->aliasName)) {
            return $dialect->quote($this->columnName);
        } else {
            return $this->aliasName . "." . $dialect->quote($this->columnName);
        }
    }

    /**
     * @param ColumnsAndDialectProvider $provider
     * @return string
     */
    public function q(ColumnsAndDialectProvider $provider): string {
        if (empty($this->aliasName)) {
            return $provider->dialect()->quote($this->columnName);
        } else {
            return $this->aliasName . "." . $provider->dialect()->quote($this->columnName);
        }
    }

    /**
     * @param ColumnsAndDialectProvider $provider
     * @return string
     */
    public function as(ColumnsAndDialectProvider $provider): string {
        if (empty($this->aliasName)) {
            return $provider->dialect()->quote($this->columnName);
        } else {
            return sprintf("%s.%s AS %s",
                $this->aliasName, $provider->dialect()->quote($this->columnName),
                $provider->dialect()->quote(Qn::q($this->aliasName, $this->columnName)));
        }
    }
}