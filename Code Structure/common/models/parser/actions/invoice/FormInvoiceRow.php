<?php
/**
 * FormInvoiceRow.php
 */

namespace common\models\parser\actions\invoice;

use common\helpers\ArrayHelper;
use common\helpers\MathHelper;
use common\models\db\Error;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class FormInvoiceRow
 *
 * @package common\models\parser\actions\invoice
 */
class FormInvoiceRow extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $supplierOrderPositionId = (int)$this->getColumn($row, 'supplierOrderPositionId');
        if ($supplierOrderPositionId) {
            $supplierOrderPositionId = MathHelper::removeControlDigit($supplierOrderPositionId);
        }

        $count = $this->getColumnParam($row, 'count', 'fixedCount');
        $price = $this->getColumnParam($row, 'price', 'fixedPrice');
        $formRow = [
            'rowNum'                  => $line,
            'brand'                   => $this->getColumnParam($row, 'brand', 'fixedBrand'),
            'brandId'                 => $this->getColumnParam($row, 'brand', 'brandId'),
            'count'                   => $count,
            'price'                   => $price,
            'amount'                  => $price * $count,
            'code'                    => $this->getColumnParam($row, 'code', 'fixedCode'),
            'replaceCode'             => $this->options['isAllowReplace'] ? $this->getColumn($row, 'replaceCode') : null,
            'productId'               => $this->getRowParam($row, 'productId'),
            'productName'             => $this->getColumn($row, 'name'),
            'supplierOrderPositionId' => $supplierOrderPositionId,
            'weight'                  => $this->getColumnParam($row, 'weight', 'fixedWeight'),
            'countryCode'             => $this->getColumnParam($row, 'country', 'countryCode'),
            'invoiceId'               => ArrayHelper::getValue($this->options, 'invoiceId'),
            'supplierOrderId'         => $this->getColumnParam($row, 'supplierOrderNumber', 'supplierOrderId'),
            'thirdPartyCode'          => $this->getColumn($row, 'thirdPartyCode'),
        ];
        $boxNumber = $this->getColumn($row, 'boxNumber');
        if ($boxNumber) {
            $formRow['boxNumber'] = $boxNumber;
        }
        $customCode = $this->getColumn($row, 'customCode');
        if ($customCode) {
            $formRow['customCode'] = str_replace(' ', '', (string)$customCode);
        }
        $this->result['normalRows'][] = $formRow;
    }
}
