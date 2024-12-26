<?php
/**
 * InitData.php
 */

namespace common\models\parser\actions;

use common\helpers\ArrayHelper;
use common\models\db\CountType;
use common\models\db\Currency;
use common\models\db\Error;
use common\models\db\PriceTypeType;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class PrepareData
 *
 * Prepare inner data - clear wrong symbols
 * Save good data to row params
 *
 * @package common\models\parser\actions
 */
class InitData extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $typeId = ArrayHelper::getSimpleArrayValue($this->options, 'typeId', PriceTypeType::TYPE_DEFAULT);
        if ($typeId == PriceTypeType::TYPE_MASTER_AVAILABLE) {
            $this->setColumnParam($row, 'price', 'fixedPrice', 0);
        } else {
            $price = $this->getColumn($row, 'price');
            $fixedPrice = $this->normalizePrice($price);

            if ($this->getColumnNumber('price') != 0) {
                if (!$fixedPrice) {
                    $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRICE);
                    $this->setCriticalError($row, $line);

                    return;
                }
            }

            $additionPrice = $this->getColumn($row, 'additionPrice', 0);
            $fixedAdditionPrice = $this->normalizePrice($additionPrice);

            $this->setColumnParam($row, 'price', 'fixedPrice', $fixedPrice + $fixedAdditionPrice);
            $this->setColumnParam($row, 'price', 'fixedAdditionPrice', $fixedAdditionPrice);
        }

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
                $countType = self::getCountType($count);
            }

            $this->setColumnParam($row, 'count', 'countType', $countType);
            $this->setColumnParam($row, 'count', 'fixedCount', $fixedCount);
        }
    }

    /**
     * Clear price
     * @param string $price
     * @return float
     */
    private function normalizePrice($price)
    {
        $price = preg_replace('/[^0-9,.]/', '', $price);
        $price = str_replace(',', '.', $price);
        $price = floatval($price);

        return $price;
    }

    /**
     * Clear count
     * @param string $count
     * @return int|bool
     */
    protected function normalizeCount($count)
    {
        $count = str_replace(',', '.', $count);
        $count = preg_replace('/[<>\s]/', '', $count);
        $intCount = intval($count) . '';

        if ($count == $intCount) {
            return $count;
        } else {
            return false;
        }
    }

    /**
     * Detect count type (equal, more, less)
     * @param $count
     * @return int
     */
    public static function getCountType($count)
    {
        if (strpos($count, '>') !== false || strpos($count, '+') !== false) {
            return CountType::TYPE_MORE;
        } elseif (strpos($count, '<') !== false) {
            return CountType::TYPE_LESS;
        }
        return CountType::TYPE_EQUAL;
    }
}
