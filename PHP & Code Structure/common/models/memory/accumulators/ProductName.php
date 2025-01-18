<?php

namespace common\models\db\memory\accumulators;

use common\models\db;
use common\models\db\memory\accumulators\productSource\OverwriteProductSource;
use yii\db\Expression;
use common\models\productSource\base\ProductSourceStorage;

/**
 * Class ProductName
 */
class ProductName extends OverwriteProductSource
{
    public $sourceEntityTypeId = db\SourceEntityType::TYPE_NAME;

    /**
     * Set information about source and main tables for ProductName.
     * @param \common\models\productSource\base\ProductSourceStorage|null $productStorage
     * @param \common\models\productSource\base\ProductSourceStorage|null $productSourceStorage
     * @return mixed
     */
    public function init($productStorage = null, $productSourceStorage = null)
    {
        $productStorage = new ProductSourceStorage(db\ProductName::tableName());
        $productStorage->setSpecificColumns(['name']);

        $productSourceStorage = new ProductSourceStorage(db\ProductNameFromSource::tableName());
        $productSourceStorage->setSpecificColumns(['languageCode', 'name']);
        $productSourceStorage->setColumns(
            [
                'productId',
                'sourceId',
                'languageCode',
                'name',
                'changeTime',
                'isMain'
            ]
        );
        $productSourceStorage->setDuplicateKeyUpdateColumns(
            [
                'name'       => 'VALUES(name)',
                'changeTime' => 'NOW()'
            ]
        );

        return parent::init($productStorage, $productSourceStorage);
    }

    /**
     * Add item value to source
     * @param int $productId
     * @param int $sourceId
     * @param string $languageCode
     * @param string $name
     * @param bool $isAccumulate
     * @return bool
     */
    public function addValue($productId, $sourceId, $languageCode, $name, $isAccumulate = false)
    {
        $name = db\ProductName::prepare($name);
        if (!$name) {
            return false;
        }

        return parent::addArrayValue(
            [
                'productId'    => $productId,
                'sourceId'     => $sourceId,
                'languageCode' => "'$languageCode'",
                'name'         => \Yii::$app->getDb()->quoteValue($name),
                'changeTime'   => new Expression('NOW()'),
                'isMain'       => 0
            ],
            $isAccumulate
        );
    }
}
