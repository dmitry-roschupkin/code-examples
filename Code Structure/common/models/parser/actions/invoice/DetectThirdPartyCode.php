<?php
/**
 * DetectThirdPartyCode.php
 */

namespace common\models\parser\actions\invoice;

use common\helpers\ArrayHelper;
use common\models\db\Error;
use common\models\db\ThirdPartyProduct;
use common\models\parser\actions\base\AbstractAction;

class DetectThirdPartyCode extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if ($this->getRowParam($row, 'productId')) {

            return;
        }
        if ($this->getColumnNumber('thirdPartyCode')) {
            $thirdPartyCode = $this->getColumn($row, 'thirdPartyCode');
            $supplierId = ArrayHelper::getValue($this->options, 'supplierId');
            $tpp = ThirdPartyProduct::getByThirdPartyCode($supplierId, $thirdPartyCode);
            if ($tpp) {
                $this->setRowParam($row, 'productId', $tpp['productId']);
                $this->setColumnParam($row, 'code', 'fixedCode', $tpp['productCode']);
                $this->setColumnParam($row, 'brand', 'fixedBrand', $tpp['brandCode']);
                $this->setColumnParam($row, 'brand', 'brandId', $tpp['brandId']);
            } else {
                $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRODUCT_CODE);
                $this->setCriticalError($row, $line);
                return;
            }
        }
    }
}