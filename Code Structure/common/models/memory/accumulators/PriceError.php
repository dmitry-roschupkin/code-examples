<?php

namespace common\models\db\memory\accumulators;

use common\db\QueryAccumulator;
use common\models\db;

/**
 * Class PriceError
 */
class PriceError
{
    /** @var \common\db\QueryAccumulator|null $accumulator */
    private $accumulator = null;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->accumulator = new QueryAccumulator();
        $this->accumulator->setQuery(
            "INSERT INTO `" . db\PriceError::tableName() . "`
                (`priceFileId`,
                `incomingBrandName`,
                `incomingProductCode`,
                `price`,
                `currencyCode`,
                `count`,
                `countType`,
                `weight`,
                `volume`,
                `name`,
                `replaceCode`,
                `additionPrice`,
                `robatGroup`,
                `errorId`)
            VALUES",
            "ON DUPLICATE KEY UPDATE
                `priceFileId` = VALUES(priceFileId),
                `incomingBrandName` = VALUES(incomingBrandName),
                `incomingProductCode` = VALUES(incomingProductCode),
                `price` = VALUES(price),
                `currencyCode` = VALUES(currencyCode),
                `count` = VALUES(count),
                `countType` = VALUES(countType),
                `weight` = VALUES(weight),
                `volume` = VALUES(volume),
                `name` = VALUES(name),
                `replaceCode` = VALUES(replaceCode),
                `additionPrice` = VALUES(additionPrice),
                `robatGroup` = VALUES(robatGroup),
                `errorId` = VALUES(errorId)"
        );
    }

    /**
     * Add values to accumulator
     * @param array $priceErrorRow
     */
    public function addValues($priceErrorRow)
    {
        $this->accumulator->addValues($priceErrorRow);
    }
}
