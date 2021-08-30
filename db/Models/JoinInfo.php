<?php


namespace Linuzilla\Database\Models;


use Linuzilla\Database\Repositories\BaseRepository;

/**
 * Class JoinInfo
 * @package Linuzilla\Database\Models
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jul 22 03:13:33 UTC 2021
 */
class JoinInfo {
    const INIT = 0;
    const JOIN = 1;
    const LEFT_JOIN = 2;


    /**
     * JoinInfo constructor.
     * @param BaseRepository $repos
     * @param string $alias
     * @param array $condition
     * @param int $joinType
     */
    public function __construct(
        public BaseRepository $repos,
        public string $alias,
        public array $condition,
        public int $joinType) {
    }
}