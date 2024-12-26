<?php
/**
 * CheckCode.php
 */

namespace common\models\parser\actions\source;

use common\helpers\ProductHelper;
use common\models\db\Error;
use common\models\db\ProductCodeRuleType;
use common\models\parser\errors\SourceError;
use common\models\product\code\RuleProcessor;

/**
 * Class CheckCode
 * @package common\models\parser\actions\source
 */
class CheckCode extends \common\models\parser\actions\CheckCode
{
    /** @var RuleProcessor */
    protected $ruleProcessor = null;

    /**
     * Set options.
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        parent::init($options);

        $this->ruleProcessor = new RuleProcessor();
        $this->ruleProcessor->isSaveWrongCodes = false;
        $this->ruleProcessor->isSimpleModeRuleMemory = true;
    }

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        parent::applyRow($row, $line);
        if (!$this->criticalError) {
            $code = $this->getColumn($row, 'code');

            if (strlen($code) <= 3) {
                $this->setRowParam($row, 'errorId', Error::ERR_CODE_WRONG_PRODUCT_CODE);
                $this->setCriticalError($row, $line);
                return;
            } else {
                $brandId = $this->getColumnParam($row, 'brand', 'brandId');
                $clearCode = ProductHelper::correctCode($code);
                $codes = $this->ruleProcessor->process(
                    $brandId,
                    $clearCode,
                    [ProductCodeRuleType::TYPE_WRONG_CODE]
                );

                if ($codes !== false) {
                    $this->setColumnParam($row, 'code', 'clearCode', $clearCode);
                } else {
                    $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_CODE_RULE));
                    $this->setCriticalError($row, $line);
                    return;
                }
            }
        }
    }
}
