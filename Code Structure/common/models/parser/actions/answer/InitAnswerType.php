<?php
/**
 * InitAnswerType.php
 */
namespace common\models\parser\actions\answer;

use common\helpers\ArrayHelper;
use common\models\db\Error;
use common\models\db\SupplierAnswer;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class InitAnswerType
 * @package common\models\parser\actions\answer
 */
class InitAnswerType extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $supplierAnswer = $this->getColumn($row, 'supplierAnswer');
        $redemptionSign = ArrayHelper::getValue($this->options, 'supplierRedemptionSign');
        $refuseSign = ArrayHelper::getValue($this->options, 'supplierRefuseSign');
        $answerType = ArrayHelper::getValue($this->options, 'answerType');

        $isRedemptionType = $answerType == SupplierAnswer::ANSWER_TYPE_REDEMPTION;
        $isRefuseType = $answerType == SupplierAnswer::ANSWER_TYPE_REFUSE;
        $isCombinedType = $answerType == SupplierAnswer::ANSWER_TYPE_COMBINED;

        if (in_array($supplierAnswer, explode(',', $redemptionSign)) && ($isRedemptionType || $isCombinedType)) {
            $this->setColumnParam($row, 'answer', 'isRefuse', 0);
        } elseif (in_array($supplierAnswer, explode(',', $refuseSign)) && ($isRefuseType || $isCombinedType)) {
            $this->setColumnParam($row, 'answer', 'isRefuse', 1);
        } else {
            $this->setColumnParam($row, 'answer', 'isRefuse', null);
        }

        $this->setColumnParam($row, 'answer', 'answerType', $answerType);
    }
}
