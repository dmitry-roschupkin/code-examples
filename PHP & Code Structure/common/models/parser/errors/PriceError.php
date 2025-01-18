<?php
/**
 * PriceError.php
 */

namespace common\models\parser\errors;

use common\models\parser\errors\base\AbstractError;
use common\helpers\CheckHelper;
use common\models\db\CountType;
use common\models\db\memory\accumulators;

/**
 * Class PriceParserError
 *
 * @package common\models\parser\errors
 */
class PriceError extends AbstractError
{
    /** @var \common\models\db\memory\accumulators\PriceError $errorsAccumulator */
    private $errorsAccumulator = null;

    public function init($params = null)
    {
        $this->errorsAccumulator = new accumulators\PriceError();
    }
    //TODO:audit Нет обозначения типов переменных (string, int и т.д)
    /**
     * RU:
     * Аккумулирует ошибки заливки прайслистов для записи в таблицу PriceError
     *
     * EN:
     * Accumulate upload price list errors for write into PriceError table
     *
     * @param $params
     * @return mixed|void
     */
    public function setError($params)
    {
        $row = CheckHelper::getArrayValue($params, 'row');
        /** @var \common\models\parser\actions\base\AbstractAction $action */
        $action = CheckHelper::getArrayValue($params, 'action');
        $errorId = $action->getRowParam($row, 'errorId');
        if (!$errorId) {
            return;
        }
        $this->errorsCount++;

        $code = $action->getColumn($row, 'code');
        $brand = $action->getColumn($row, 'brand');
        $weight = $action->getColumn($row, 'weight', 0);
        $volume = $action->getColumn($row, 'volume', 0);
        $name = $action->getColumn($row, 'name', '');
        $replaceCode = $action->getColumn($row, 'replaceCode', '');
        $additionPrice = $action->getColumn($row, 'additionPrice', 0);
        $robatGroup = $action->getColumn($row, 'robatGroup', '');
        $count = $action->getColumn($row, 'count', 0);
        $countType = $action->getColumnParam($row, 'count', 'countType', CountType::TYPE_EQUAL);
        $price = $action->getColumn($row, 'price', 0);
        $errorId = $action->getRowParam($row, 'errorId');

        $errorPosition = [
            'priceFileId' => $action->options['priceFileId'],
            'incomingBrandName' => $brand === null ? '' : (string)$brand,
            'incomingProductCode' => $code === null ? '' : (string)$code,
            'price' => $price === null ? '' : (string)$price,
            'currencyCode' => $action->options['currencyCode'],
            'count' => $count === null ? '' : (string)$count,
            'countType' => $countType === null ? '' : $countType,
            'weight' => $weight === null ? '' : (string)$weight,
            'volume' => $volume === null ? '' : (string)$volume,
            'name' => $name === null ? '' : (string)$name,
            'replaceCode' => $replaceCode === null ? '' : (string)$replaceCode,
            'additionPrice' => $additionPrice === null ? '' : (string)$additionPrice,
            'robatGroup' => $robatGroup === null ? '' : $robatGroup,
            'errorId' => $errorId,
        ];
        $this->errorsAccumulator->addValues($errorPosition);
    }
}
