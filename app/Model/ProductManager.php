<?php

declare(strict_types = 1);

namespace App\Model;

use Nette;

/**
 * Eshop management (not really).
 */
final class ProductManager {

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
    
    function getByID($id){
        return $this->database->table(self::TABLE_NAME)->get($id);
    }
    
    function createRecord($data){
        $this->database->table(self::TABLE_NAME)->insert($data);
    }
    
    function updateRecord($id, $data){
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID,$id)->update($data);
    }
    
    function deleteRecord($id){
        $this->database->table(self::TABLE_NAME)->where('id',$id)->delete();
    }

}
