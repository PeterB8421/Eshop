<?php

declare(strict_types = 1);

namespace App\Model;

use Nette;

/**
 * Eshop management (not really).
 */
final class EshopManager {

    const
            TABLE_NAME = 'product',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'name',
            COLUMN_CATEGORY = 'category',
            COLUMN_DESCRIPTION = 'description',
            COLUMN_PHOTO = 'photo',
            COLUMN_PRICE = 'price';

    private $database;

    function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    function getAll($order) {
        return $this->database->table(self::TABLE_NAME)->order($order)->fetchAll();
    }

}
