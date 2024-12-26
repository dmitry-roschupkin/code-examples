<?php
/**
 * AnswerError.php
 */

namespace common\models\parser\errors;

use common\helpers\ArrayHelper;
use common\helpers\CheckHelper;
use common\models\db\Error;
use common\models\parser\errors\base\AbstractError;

/**
 * Class AnswerError
 * @package common\models\parser\errors
 */
class AnswerError extends AbstractError
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
        $this->errorsCount++;

        $allErrors = (new Error())->getAllErrors();
        $errorId = $action->getRowParam($row, 'errorId', []);
        $errorsNames[] = ArrayHelper::getValue($allErrors, $errorId);
        $formRow = [
            'rowNum'      => $rowNum,
            'brand'       => $action->getColumnParam($row, 'brand', 'fixedBrand'),
            'brandId'     => $action->getColumnParam($row, 'brand', 'brandId'),
            'count'       => $action->getColumnParam($row, 'count', 'fixedCount'),
            'price'       => $action->getColumnParam($row, 'price', 'fixedPrice'),
            'code'        => $action->getColumn($row, 'code'),
            'productId'   => $action->getRowParam($row, 'productId'),
            'name'        => $action->getColumn($row, 'name'),
            'errors'      => $errorsNames,
        ];

        $action->result['errors'][] = $formRow;
    }
}
