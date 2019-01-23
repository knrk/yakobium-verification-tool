<?php
namespace App\CoreModule\Model;

use App\Model\DatabaseManager;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ProductsManager extends DatabaseManager 
{
    const   TABLE_NAME = 'registered_products',
            TABLE_PRODUCTS = 'lights',            
            TABLE_OWNERS = 'owners',
            TABLE_VERIFICATIONS = 'verifications',
            COL_LIGHT_ID = 'light_id',
            COL_OWNER_ID = 'owner_id',
            COL_SERIAL_NUMBER = 'serial_number',
            ID = 'id';

    public function getProducts($productId) 
    {
        return $this->database->table(self::TABLE_NAME)->where(self::COL_SERIAL_NUMBER, $productId)->fetch();
    }

    public function getProduct($serial)
    {
        return $this->database->table(self::TABLE_PRODUCTS)->where(self::COL_SERIAL_NUMBER, $serial)->get(1);
    }

    public function getOwnerId($lightId)
    {
        return $this->database->table(self::TABLE_VERIFICATIONS)->where(self::COL_LIGHT_ID, $lightId)->fetch()->owner_id;
    }

    public function getOwner($ownerId)
    {
        return $this->database->table(self::TABLE_OWNERS)->where(self::ID, $ownerId)->fetch();
    }
}
?>