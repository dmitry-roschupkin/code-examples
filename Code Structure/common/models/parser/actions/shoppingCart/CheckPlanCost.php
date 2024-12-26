<?php

namespace common\models\parser\actions\shoppingCart;

use common\helpers\ArrayHelper;
use common\models\db\Client;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class CheckPlanCost
 * @package common\models\parser\actions\shoppingCart
 */
class CheckPlanCost extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $clientId = ArrayHelper::getValue($this->options, 'clientId');
        $planCost = $this->getColumn($row, 'planCost');

        if ($clientId != Client::CLIENT_STOCK_ID) {
            $this->setColumn($row, 'planCost', 0);

            return;
        }

        if ($planCost < 0) {
            $this->setRowParam($row, 'error', \Yii::t('app', 'Плановая с/с < 0'));
            $this->setCriticalError($row, $line);

            return;
        }
    }
}
