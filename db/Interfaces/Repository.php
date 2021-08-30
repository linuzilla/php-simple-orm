<?php


namespace Linuzilla\Database\Interfaces;


use Linuzilla\Database\Attributes\Expose;
use Linuzilla\Database\Clauses\WhereClause;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Entities\BaseEntity;

/**
 * Interface Repository
 * @package Linuzilla\Database\Interfaces
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jun 24 06:57:57 UTC 2021
 */
interface Repository {
    /**
     * @return WhereClause
     */
    public function all(): WhereClause;

    /**
     * @param BaseEntity|array $condition
     * @return WhereClause
     */
    public function where(BaseEntity|array $condition): WhereClause;

    /**
     * INSERT IGNORE INTO
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    public function saveOrIgnore(BaseEntity|array $entity): BaseEntity|false;

    /**
     * INSERT INTO ...
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    public function save(BaseEntity|array $entity): BaseEntity|false;

    /**
     * REPLACE INTO ...
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    public function saveOrOverwrite(BaseEntity|array $entity): BaseEntity|false;

    /**
     * INSERT INTO ... ON DUPLICATE KEY UPDATE
     *
     * @param BaseEntity|array $entity
     * @return BaseEntity|false
     * @throws DbException
     */
    public function saveOrUpdate(BaseEntity|array $entity): BaseEntity|false;


    /**
     * @param BaseEntity|array $entity
     * @param array $updateFields
     * @return BaseEntity|false
     */
    public function update(BaseEntity|array $entity, array $updateFields = []): BaseEntity|false;

    /**
     * @param BaseEntity|array $entity
     * @return bool
     * @throws DbException
     */
    public function delete(BaseEntity|array $entity): bool;

    /**
     * @param int|string|array $id
     * @return bool
     * @throws DbException
     */
    public function deleteById(int|string|array $id): bool;

    /**
     * @param BaseEntity|array $entity
     * @return array|false
     * @throws DbException
     */
    #[Expose]
    public function find(BaseEntity|array $entity): array|false;

    /**
     * @param int|string|array $id
     * @return BaseEntity|false
     * @throws DbException
     */
    #[Expose]
    public function findById(int|string|array $id): BaseEntity|false;
}