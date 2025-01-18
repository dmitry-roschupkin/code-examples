<?php
/**
 * CheckReplaceCode.php
 */

namespace common\models\parser\actions\source;

use common\helpers\ProductHelper;
use common\models\db\Error;
use common\models\db\ProductCodeRuleType;
use common\models\db\SourceEntityType;
use common\models\parser\errors\SourceError;

/**
 * Class CheckReplaceCode
 * @package common\models\parser\actions\source
 */
class CheckReplaceCode extends CheckCode
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        parent::applyRow($row, $line);
        if (!$this->criticalError) {
            $code = $this->getColumn($row, 'replaceCode');

            if (strlen($code) <= 3) {
                $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_CROSS_CODE_REPLACE));
                $this->setCriticalError($row, $line);
                return;
            } else {
                $brandId = $this->getColumnParam($row, 'brand', 'replaceBrandId');
                $clearCode = ProductHelper::correctCode($code);
                $codes = $this->ruleProcessor->process(
                    $brandId,
                    $clearCode,
                    [ProductCodeRuleType::TYPE_WRONG_CODE]
                );

                if ($codes !== false) {
                    $this->setColumnParam($row, 'code', 'replaceClearCode', $clearCode);
                } else {
                    if ($this->options['entityTypeId'] == SourceEntityType::TYPE_CROSS) {
                        $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_CROSS_CODE_RULE));
                    } else {
                        $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_CODE_RULE));
                    }
                    $this->setCriticalError($row, $line);
                    return;
                }
            }
        }
    }
}
