<?php
/**
 * InvoiceParserError.php
 */

namespace common\models\parser\errors;

use common\models\db\EntityGroup;
use common\models\parser\errors\base\AbstractError;
use common\helpers\CheckHelper;
use common\models\db\Error;
use common\helpers\ArrayHelper;

/**
 * Class InvoiceError
 * @package common\models\parser\errors
 */
class InvoiceError extends AbstractError
{
    public function init($params = null)
    {
    }

    /**
     * RU:
     * Аккумулирует ошибки заливки прайслистов для записи в таблицу PriceError
     *
     * EN:
     * Accumulate upload price list errors for write into PriceError table
     *
     * @param array $params
     */
    public function setError($params)
    {
        $row = CheckHelper::getArrayValue($params, 'row');
        $action = CheckHelper::getArrayValue($params, 'action');
        $rowNum = CheckHelper::getArrayValue($params, 'rowNum');
        $this->errorsCount++;

        $allErrors = (new Error())->getAllErrors();

        $errorId = $action->getRowParam($row, 'errorId', []);
        $errorsNames[] = ArrayHelper::getValue($allErrors, $errorId);

        $formRow = [
            'rowNum'         => $rowNum,
            'brand'          => $action->getColumnParam($row, 'brand', 'fixedBrand'),
            'brandId'        => $action->getColumnParam($row, 'brand', 'brandId'),
            'count'          => $action->getColumnParam($row, 'count', 'fixedCount'),
            'price'          => $action->getColumnParam($row, 'price', 'fixedPrice'),
            'code'           => $action->getColumn($row, 'code'),
            'productId'      => $action->getRowParam($row, 'productId'),
            'name'           => $action->getColumn($row, 'name'),
            'replaceCode'    => $action->getColumn($row, 'replaceCode'),
            'thirdPartyCode' => $action->getColumn($row, 'thirdPartyCode'),
            'errors'         => $errorsNames,
        ];

        $action->result['errors'][] = $formRow;
    }
}
