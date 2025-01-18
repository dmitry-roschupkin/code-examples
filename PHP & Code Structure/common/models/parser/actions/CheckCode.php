<?php
/**
 * CheckCode.php
 */

namespace common\models\parser\actions;

use common\helpers\ProductHelper;
use common\models\db\Error;
use common\models\parser\actions\base\AbstractAction;

/**
 * Check is code exist and set in to upper register
 *
 * Class CheckCode
 * @package common\models\parser\actions\price
 */
class CheckCode extends AbstractAction
{
    /**
     * Set options.
     *
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        parent::init($options);
        $this->result['rowsTotal'] = 0;
    }

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $this->result['rowsTotal']++;

        $code = $this->getColumn($row, 'code');

        if ($this->getRowParam($row, 'productId')) {
            if ($code && strlen($code) && $this->getColumnNumber('code')) {
                if ($this->getColumnParam($row, 'code', 'fixedCode') != ProductHelper::correctCode($code)) {
                    $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRODUCT_CODE);
                    $this->setCriticalError($row, $line);
                }
            }

            return;
        }


        if (!$code || !strlen($code)) {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRODUCT_CODE);
            $this->setCriticalError($row, $line);
            return;
        }
    }
}
