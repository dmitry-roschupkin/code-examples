<?php
/**
 * UpdatePriceFixed.php
 */

namespace common\models\parser\actions\pricingPrice;

use common\db\QueryAccumulator;
use common\helpers\ArrayHelper;
use common\models\db\CountType;
use common\models\db\Currency;
use common\models\db\memory\accumulators\PriceFixed;
use common\models\parser\actions\base\AbstractAction;
use yii\db\Expression;

/**
 * Class UpdatePriceFixed
 *
 * Importing current row into 'PriceFixed' table, using accumulator
 *
 * @package common\models\parser\actions\price
 */
class UpdatePriceFixed extends AbstractAction
{
    /**
     * @var PriceFixed
     */
    private $importPriceFixed = null;

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->importPriceFixed->execute();
    }

    /**
     * Set options.
     *
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        parent::init($options);
        $this->importPriceFixed = new QueryAccumulator();
        $this->importPriceFixed->setQuery("INSERT INTO PriceFixed (`priceTypeId`, `brandId`, `productCode` , `productId`, `price`, `currencyCode`, `count`, `countType`, `updateTime`) VALUES",
            "ON DUPLICATE KEY UPDATE
                `priceTypeId` = VALUES(priceTypeId),
                `brandId` = VALUES(brandId),
                `productCode` = VALUES(productCode),
                `productId` = VALUES(productId),
                `price` = VALUES(price),
                `currencyCode` = VALUES(currencyCode),
                `count` = VALUES(count),
                `countType` = VALUES(countType),
                `updateTime` = VALUES(updateTime)");
    }

    /**
     * @param array $row
     * @param int   $line
     */
    protected function applyRow(&$row, $line)
    {
        $productId = $this->getColumn($row, 'productId');
        $brandId = $this->getColumn($row, 'brandId');
        $productCode = $this->getColumn($row, 'productCode');
        $price = $this->getColumn($row, 'price');
        $count = $this->getColumn($row, 'count');
        $countType = $count == -1 ? CountType::TYPE_MORE : CountType::TYPE_EQUAL;
        $priceTypeId = $this->getColumn($row, 'priceTypeId');
        $currencyCode = $sizeUnit = ArrayHelper::getSimpleArrayValue($this->options, 'currencyCode');;
        $updateTime = $this->options['uploadFileStart'];
        $priceFixedRow = [
            'priceTypeId'         => $priceTypeId,
            'brandId'             => $brandId,
            'productCode'         => $productCode,
            'productId'           => $productId,
            'price'               => $price,
            'currencyCode'        => $currencyCode,
            'count'               => $count,
            'countType'           => $countType,
            'updateTime'          => $updateTime,
        ];

        $this->importPriceFixed->addValues($priceFixedRow);
    }
}
