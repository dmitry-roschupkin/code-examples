<?php
/**
 * InitData.php
 */

namespace common\models\parser\actions\shoppingCart;

use common\models\db\CountType;
use common\models\db\Error;

/**
 * Class InitData
 * @package common\models\parser\actions\shoppingCart
 */
class InitData extends \common\models\parser\actions\InitData
{
    /**
     * @param array $row
     * @param int   $line
     */
    protected function applyRow(&$row, $line)
    {
        // fixedCount can be set in InitFeatures action
        if (!$this->getColumnParam($row, 'count', 'fixedCount')) {
            $count = $this->getColumn($row, 'count');
            $countColNumber = $this->getColumnNumber('count');

            if (!$countColNumber) {
                $fixedCount = -1;
                $countType = CountType::TYPE_MORE;
            } else {
                $fixedCount = $this->normalizeCount($count);

                if ($fixedCount === false) {
                    $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_COUNT);
                    $this->setCriticalError($row, $line);
                    return;
                }
                $countType = $this->getCountType($count);
            }

            $this->setColumnParam($row, 'count', 'countType', $countType);
            $this->setColumnParam($row, 'count', 'fixedCount', $fixedCount);
        }
    }
}
