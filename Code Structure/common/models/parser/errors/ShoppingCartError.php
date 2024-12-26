<?php
/**
 * ShoppingCartError.php
 */
namespace common\models\parser\errors;

use common\helpers\ArrayHelper;
use common\helpers\CheckHelper;
use common\models\db\Error;
use common\models\parser\errors\base\AbstractError;

/**
 * Class ShoppingCartError
 * @package common\models\parser\errors
 */
class ShoppingCartError extends AbstractError
{
    /**
     * @inheritdoc
     */
    public function init($params = null)
    {
        // TODO: Implement init() method.
    }

    /**
     * @inheritdoc
     */
    public function setError($params)
    {
        $row = CheckHelper::getArrayValue($params, 'row');
        $action = CheckHelper::getArrayValue($params, 'action');
        $rowNum = CheckHelper::getArrayValue($params, 'rowNum');
        $error = $action->getRowParam($row, 'error');
        $this->errorsCount++;
        $allErrors = (new Error())->getAllErrors();
        $errorId = $action->getRowParam($row, 'errorId', []);
        $errorsNames[] = ArrayHelper::getValue($allErrors, $errorId, $error);
        $formRow = [
            'rowNum'      => $rowNum,
            'brandCode'   => $action->getColumnParam($row, 'brand', 'fixedBrand'),
            'productCode' => $action->getColumn($row, 'code'),
            'productName' => $action->getColumn($row, 'name'),
            'count'       => $action->getColumnParam($row, 'count', 'fixedCount'),
            'price'       => $action->getColumn($row, 'price'),
            'productId'   => $action->getRowParam($row, 'productId'),
            'errors'      => $errorsNames,
        ];
        $action->result['errors'][] = $formRow;
    }
}
