<?php
/**
 * FormAnswerRow.php
 */
namespace common\models\parser\actions\answer;

use common\helpers\ArrayHelper;
use common\helpers\MathHelper;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class FormAnswerRow
 * @package common\models\parser\actions\invoice
 */
class FormAnswerRow extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $formRow = [
            'rowNum'                  => $line,
            'brand'                   => $this->getColumnParam($row, 'brand', 'fixedBrand'),
            'code'                    => $this->getColumnParam($row, 'code', 'fixedCode'),
            'name'                    => $this->getColumn($row, 'name'),
            'isRefuse'                => $this->getColumnParam($row, 'answer', 'isRefuse'),
            'answerType'              => $this->getColumnParam($row, 'answer', 'answerType'),
            'supplierAnswerId'        => ArrayHelper::getValue($this->options, 'answerId'),
            'supplierOrderPositionId' => $this->getColumnParam($row, 'supplierOrderPositionId', 'supplierOrderPositionId'),
            'count'                   => $this->getColumnParam($row, 'count', 'fixedCount'),
            'price'                   => $this->getColumnParam($row, 'price', 'fixedPrice'),
            'productId'               => $this->getRowParam($row, 'productId'),
            'supplierOrderId'         => $this->getColumnParam($row, 'supplierOrderNumber', 'supplierOrderId'),
        ];

        $this->result['normalRows'][] = $formRow;
    }
}
