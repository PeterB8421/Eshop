<?php

declare(strict_types = 1);

namespace App\Model;

use Nette;

/**
 * Eshop management (not really).
 */
final class OrderManager {

    const
            TABLE_NAME = 'order',
            COLUMN_ID = 'id',
            COLUMN_NOTE = 'note',
            COLUMN_PAY_TYPE = 'payType',
            COLUMN_DELIVERY = 'deliveryType',
            COLUMN_PR_ID = 'product_id',
            COLUMN_USR_ID = 'user_id',
            COLUMN_CREATED = 'created';

    private $database;

    function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    function getAll($order) {
        return $this->database->table(self::TABLE_NAME)->order($order)->fetchAll();
    }
    
    function getAllByUser($id, $order){
        return $this->database->table(self::TABLE_NAME)->where('user_id',$id)->order($order)->fetchAll();
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
