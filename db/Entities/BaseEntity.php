<?php


namespace Linuzilla\Database\Entities;


use Linuzilla\Database\Attributes\AutoIncrement;
use Linuzilla\Database\Attributes\ColumnNames;
use Linuzilla\Database\Attributes\Entity;
use Linuzilla\Database\Attributes\PrimaryKey;
use ReflectionClass;

class BaseEntity {
    /**
     * @return ReflectionClass
     */
    public function __getReflectionClass(): ReflectionClass {
        return new ReflectionClass($this);
    }

    /**
     * @param ReflectionClass|null $ref
     * @return string
     */
    public function __getTableName(?ReflectionClass $ref = null): string {
        $r = $ref ?? new ReflectionClass($this);

        $attrs = $r->getAttributes(Entity::class);

        if (count($attrs) == 1) {
            return $attrs[0]->getArguments()['name'];
        }
        return "";
    }

    /**
     * @param ReflectionClass|null $ref
     * @return array
     */
    public function __getColumnNames(?ReflectionClass $ref = null): array {
        $r = !is_null($ref) ? $ref : new ReflectionClass($this);
        $attrs = $r->getAttributes(ColumnNames::class);

        if (count($attrs) == 1) {
            return $attrs[0]->getArguments()[0];
        }
        return [];
    }


    /**
     * @param ReflectionClass|null $ref
     * @return array
     */
    public function __getPrimaryKeys(?ReflectionClass $ref = null): array {
        $r = !is_null($ref) ? $ref : new ReflectionClass($this);
        $attrs = $r->getAttributes(PrimaryKey::class);

        if (count($attrs) == 1) {
            return $attrs[0]->getArguments()[0];
        }
        return [];
    }

    /**
     * @param ReflectionClass|null $ref
     * @return array
     */
    public function __getAutoIncrement(?ReflectionClass $ref = null): array {
        $r = !is_null($ref) ? $ref : new ReflectionClass($this);
        $attrs = $r->getAttributes(AutoIncrement::class);

        if (count($attrs) == 1) {
            return $attrs[0]->getArguments()[0];
        }
        return [];
    }
}