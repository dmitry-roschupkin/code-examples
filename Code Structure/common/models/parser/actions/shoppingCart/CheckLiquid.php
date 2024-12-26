<?php

namespace common\models\parser\actions\shoppingCart;

use common\helpers\ArrayHelper;
use common\models\db\Client;
use common\models\db\ProductType;
use common\models\db\ProductTypeProduct;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class CheckLiquid
 * @package common\models\parser\actions\shoppingCart
 */
class CheckLiquid extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $clientId = ArrayHelper::getValue($this->options, 'clientId');
        $productId = $this->getRowParam($row, 'productId');
        $isLiquid = ProductTypeProduct::findOne(['productId' => $productId, 'typeId' => ProductType::STATUS_LIQUID]);

        if ($clientId == Client::CLIENT_SYSTEM_ID) {
            return;
        }

        if ($clientId == Client::CLIENT_STOCK_ID && $isLiquid) {
            $this->setRowParam($row, 'error', \Yii::t('app', 'Не верный клиент для масла'));
            $this->setCriticalError($row, $line);

            return;
        }

        if ($clientId == Client::CLIENT_STOCK_OIL_ID && $isLiquid == null) {
            $this->setRowParam($row, 'error', \Yii::t('app', 'Продукт не является маслом'));
            $this->setCriticalError($row, $line);

            return;
        }
    }
}
