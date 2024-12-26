<?php
/**
 * InitBasePrice.php
 */

namespace common\models\parser\actions\price;

use common\helpers\ArrayHelper;
use common\models\db\PriceTypeType;
use common\models\db\PurchaseProfitType;
use common\models\parser\actions\base\AbstractAction;
use common\models\db\memory;
use common\models\db\memory\PriceTypeBrandProfit;

/**
 * Class InitBasePrice
 * @package common\models\parser\actions\price
 */
class InitBasePrice extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if ($this->options['typeId'] == PriceTypeType::TYPE_MASTER_AVAILABLE) {
            return;
        }
        $price = $this->getColumnParam($row, 'price', 'fixedPrice');
        $priceTypeId = ArrayHelper::getValue($this->options, 'priceTypeId');

        $purchaseProfitType = ArrayHelper::getValue($this->options, 'purchaseProfitType');

        switch ($purchaseProfitType) {
            case PurchaseProfitType::TYPE_RG:
                $profit = $this->getColumn($row, 'profit');
                $robatGroup = $this->getColumn($row, 'robatGroup');
                $rg = null;
                $rg = memory\PriceTypeRobatGroup::getInstance()->getRobatGroup($priceTypeId, $robatGroup);

                if ($profit || $robatGroup && $rg = memory\PriceTypeRobatGroup::getInstance()->getRobatGroup($priceTypeId, $robatGroup)) {
                    $this->calculateRabatGroupPrice($row, $price, $profit, $rg);
                }
                break;

            case PurchaseProfitType::TYPE_BRAND:
                $brandId = $this->getColumnParam($row, 'brand', 'brandId');
                $priceTypeBrandProfitMem = PriceTypeBrandProfit::getInstance();
                $priceTypeBrandProfit = $priceTypeBrandProfitMem->getPriceTypeBrandProfit($priceTypeId, $brandId);
                if ($priceTypeBrandProfit) {
                    $fixedPrice = $price * $priceTypeBrandProfit;
                    $this->setColumnParam($row, 'price', 'fixedPrice', $fixedPrice);
                }
                break;

            case PurchaseProfitType::TYPE_PRICE_TYPE:
                $priceTypeProfit = ArrayHelper::getValue($this->options, 'priceTypeProfit');
                $fixedPrice = $price * $priceTypeProfit;
                $this->setColumnParam($row, 'price', 'fixedPrice', $fixedPrice);
                break;
        }
    }

    private function calculateRabatGroupPrice(&$row, $price, $profit, $robatGroup)
    {
        if ($profit) {
            $profit = floatval($profit);
            $isPercentProfit = ArrayHelper::getValue($this->options, 'isPercentProfit');
            if ($isPercentProfit == 1) {
                $profit = 1 - ($profit / 100);
            }
            $fixedPrice = $price * $profit;
        } else {
            $rgProfit = ArrayHelper::getValue($robatGroup, 'profit');
            $this->setColumnParam($row, 'robatGroup', 'robatGroupId', ArrayHelper::getValue($robatGroup, 'id'));
            $fixedPrice = $price * $rgProfit;
        }

        $this->setColumnParam($row, 'price', 'fixedPrice', $fixedPrice);
    }
}
