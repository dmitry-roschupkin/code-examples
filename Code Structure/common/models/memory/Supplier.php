<?php

namespace common\models\db\memory;

use common\base\SingletonInterface;
use common\base\SingletonTrait;
use common\helpers\ArrayHelper;

/**
 * Class Supplier
 */
class Supplier implements SingletonInterface
{
    use SingletonTrait;

    private $suppliers = [];

    /**
     * Return item information from table Supplier by $id.
     * @param null $id
     * @return array
     */
    public function getSupplier($id = null)
    {
        if (!$this->suppliers) {
            $this->readSuppliers();
        }

        if ($id) {
            return $this->suppliers[$id];
        }

        return $this->suppliers;
    }

    /**
     * Read all Supplier in $this->suppliers
     */
    private function readSuppliers()
    {
        //TODO: Need use cache there
        $suppliersArray = \common\models\db\Supplier::find()->asArray()->all();
        $this->suppliers = ArrayHelper::index($suppliersArray, 'id');
    }
}
