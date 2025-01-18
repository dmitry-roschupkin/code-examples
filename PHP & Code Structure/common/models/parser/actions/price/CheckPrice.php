<?php
/**
 * CheckPrice.php
 */

namespace common\models\parser\actions\price;

use common\models\db\Currency;
use common\models\db\Error;
use common\models\db\PriceTypeType;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class CheckPrice
 * @package common\models\parser\actions\price
 */
class CheckPrice extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if ($this->options['typeId'] == PriceTypeType::TYPE_MASTER_AVAILABLE) {
            return;
        }

        $fixedPrice = $this->getColumnParam($row, 'price', 'fixedPrice');
        if ($this->options['currencyCode'] != Currency::CODE_USD && $fixedPrice !== null) {
            if ($fixedPrice / $this->options['currencyRate'] < 0.01) {
                $this->setRowParam($row, 'errorId', Error::ERR_CODE_LOW_PRICE);
                $this->setCriticalError($row, $line);
            }
        }
    }
}