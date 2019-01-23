<?php
namespace App\CoreModule\Model;

use App\Model\DatabaseManager;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ProductsManager extends DatabaseManager 
{
    const   TABLE_NAME = 'registered_products',
            SERIAL_NUMBER = 'serial_number';

    public function getProduct($productId) 
    {
        return $this->database->table(self::TABLE_NAME)->where(self::SERIAL_NUMBER, $productId)->fetch();
    }
}
?>