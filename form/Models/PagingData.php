<?php


namespace Linuzilla\Form\Models;


class PagingData {
    public ?object $bean = null;
    public int $startFrom;
    public int $entriesPerPage = 50;

    /**
     * PagingData constructor.
     * @param object|null $bean
     */
    public function __construct(object $bean = null) {
        $this->bean = $bean;
    }
}