<?php
/**
 * DetectBrand.php
 */
namespace common\models\parser\actions;

use common\models\db\Error;
use common\models\db\memory\Brand;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class DetectBrand
 * @package common\models\parser\actions
 */
class DetectBrand extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $brand = $this->getColumn($row, 'brand');
        $fixedBrand = $this->normalizeBrand($brand);
        if (!$fixedBrand) {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_BRAND);
            $this->setCriticalError($row, $line);
            return;
        }
        $this->setColumnParam($row, 'brand', 'fixedBrand', $fixedBrand);

        $brandId = Brand::getInstance()->getBrand($fixedBrand);

        if ($brandId) {
            $this->setColumnParam($row, 'brand', 'brandId', $brandId);
        } else {
            $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_BRAND);
            $this->setCriticalError($row, $line);
        }
    }

    /**
     * RU: Очистка бренда от лишних символов
     * EN: Clear brand
     * @param string $brand
     * @return string
     */
    protected function normalizeBrand($brand)
    {
        $brand = mb_strtoupper($brand);
        $brand = trim($brand);

        return $brand;
    }
}
