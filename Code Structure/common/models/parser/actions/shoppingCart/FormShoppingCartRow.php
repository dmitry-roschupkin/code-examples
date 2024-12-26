<?php
/**
 * ShoppingCart.php
 */

namespace common\models\parser\actions\shoppingCart;

use common\helpers\ArrayHelper;
use common\helpers\DateTimeHelper;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class FormShoppingCartRow
 * @package common\models\parser\actions\shoppingCart
 */
class FormShoppingCartRow extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $price = $this->getColumnParam($row, 'price', 'fixedPrice');
        $purchPrice = $this->getColumnParam($row, 'price', 'purchPrice');
        $formRow = [
            'rowNum'           => $line,
            'clientId'         => ArrayHelper::getValue($this->options, 'clientId'),
            'productId'        => $this->getRowParam($row, 'productId'),
            'brandCode'        => $this->getColumnParam($row, 'brand', 'fixedBrand'),
            'brandId'          => $this->getColumnParam($row, 'brand', 'brandId'),
            'productCode'      => $this->getColumn($row, 'code'),
            'productName'      => $this->getColumn($row, 'name'),
            'price'            => $price,
            'purchPrice'       => $purchPrice,
            'planCost'         => $this->getColumn($row, 'planCost'),
            'maxPrice'         => $price,
            'count'            => $this->getColumnParam($row, 'count', 'fixedCount'),
            'priceTypeOuterId' => $this->getRowParam($row, 'priceTypeOuterId'),
            'createTime'       => DateTimeHelper::getNowString(),
            'expectedFinishTime'       => $this->getRowParam($row, 'expectedFinishTime', DateTimeHelper::getNowString()),
            'waitDays'         => $this->getColumn($row, 'waitDays', 0),
            'reference'        => $this->getColumn($row, 'reference', ''),
            'stickerText'      => $this->getColumn($row, 'comment', ''),
            'comment'          => '',
        ];
        $this->result['normalRows'][] = $formRow;
    }
}
