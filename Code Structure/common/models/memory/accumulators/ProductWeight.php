<?php

namespace common\models\db\memory\accumulators;

use common\models\db\memory\accumulators;
use common\models\db;
use common\models\db\memory\accumulators\productSource\OverwriteProductSource;
use yii\db\Expression;
use common\models\productSource\base\ProductSourceStorage;

/**
 * Class Weight
 */
class ProductWeight extends OverwriteProductSource
{
    public $sourceEntityTypeId = db\SourceEntityType::TYPE_WEIGHT;

    /**
     * Set information about source and main tables for ProductApply.
     * @param \common\models\productSource\base\ProductSourceStorage|null $productStorage
     * @param \common\models\productSource\base\ProductSourceStorage|null $productSourceStorage
     * @return mixed
     */
    public function init($productStorage = null, $productSourceStorage = null)
    {
        $productStorage = new ProductSourceStorage(db\Product::tableName());
        $productStorage->setSpecificColumns(['weight']);

        $productSourceStorage = new ProductSourceStorage(db\ProductWeightFromSource::tableName());
        $productSourceStorage->setSpecificColumns(['weight']);
        $productSourceStorage->setColumns([
            'productId',
            'sourceId',
            'weight',
            'changeTime',
            'isMain'
        ]);
        $productSourceStorage->setDuplicateKeyUpdateColumns(
            [
                'weight'     => 'VALUES(weight)',
                'changeTime' => 'NOW()'
            ]
        );

        return parent::init($productStorage, $productSourceStorage);
    }

    /**
     * Add item value to source
     * @param integer $productId
     * @param integer $sourceId
     * @param float $weight
     * @param bool $isAccumulate
     * @return bool
     */
    public function addValue($productId, $sourceId, $weight, $isAccumulate = false)
    {
        $weight = str_replace(',', '.', $weight);
        if ($weight > 0) {
            return parent::addArrayValue(
                [
                    'productId'  => $productId,
                    'sourceId'   => $sourceId,
                    'weight'     => $weight,
                    'changeTime' => new Expression('NOW()'),
                    'isMain'     => 0
                ],
                $isAccumulate
            );
        }

        return false;
    }
}
