<?php
/**
 * UpdateComparePrice.php
 */

namespace common\models\parser\actions\comparePrice;

use common\db\QueryAccumulator;
use common\helpers\ProductHelper;
use common\models\db\Error;
use common\models\parser\actions\base\AbstractAction;

class UpdateComparePrice extends AbstractAction
{
    /** @var QueryAccumulator */
    private $accumulator = null;

    /**
     * Set options.
     *
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        parent::init($options);
        $this->result['wrongBrand'] = 0;
        $this->accumulator = new QueryAccumulator();
        $this->accumulator->setQuery(
            "INSERT INTO ComparePrice
                (
                `groupId`, 
                `supplier`,
                `brand`, 
                `code`,
                `price`)
            VALUES",
            "ON DUPLICATE KEY UPDATE
                `groupId` = VALUES(groupId),
                `supplier` = VALUES(supplier),
                `brand` = VALUES(brand),
                `code` = VALUES(code),
                `price` = VALUES(price)"
        );
    }

    /**
     * destruct
     */
    public function __destruct()
    {
        $this->accumulator->execute();
    }

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $code = $this->getColumn($row, 'code');
        if (!$code || !strlen(trim($code))) {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRODUCT_CODE);
            $this->setCriticalError($row, $line);

            return;
        }

        $brand = $this->getColumn($row, 'brand');
        if (!$brand || !strlen(trim($brand))) {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_BRAND);
            $this->setCriticalError($row, $line);

            return;
        }

        $price = $this->getColumn($row, 'price');
        $price = preg_replace('/[^0-9,.]/', '', $price);
        $price = str_replace(',', '.', $price);
        $price = floatval($price);

        if (!$price) {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRICE);
            $this->setCriticalError($row, $line);

            return;
        }

        $code = ProductHelper::correctCode($code);
        $brand = trim($brand);
        $brand = strtoupper($brand);

        $this->accumulator->addValues([
            'groupId'  => $this->options['groupId'],
            'supplier' => $this->options['supplier'],
            'brand'    => $brand,
            'code'     => $code,
            'price'    => $price
        ]);
    }
}