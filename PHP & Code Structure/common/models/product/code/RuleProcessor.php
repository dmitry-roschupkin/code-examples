<?php

namespace common\models\product\code;

use common\helpers\ArrayHelper;
use common\helpers\ProductHelper;
use \common\models\db\memory\ProductCodeRule;
use common\models\db\ProductCodeRuleType;
use yii\base\Exception;

/**
 * Class Processor
 *
 * @package common\components\product\code
 */
class RuleProcessor
{
    private $codeRuleConditionId = [];

    public $isSaveWrongCodes = true;

    public $isSimpleModeRuleMemory = false;

    const ERR_MSG_MISSING_SUPPLIER = 'Supplier is require argument for correction rule type.';

    /**
     * Apply all rules of specific brands to product code.
     * Rules applies one by one in defined order.
     * Each rule modify product code which is incoming param for next rule.
     *
     *
     * @param   int $brandId
     * @param   string $code
     * @param array $ruleTypes
     * @param null $sourceTypeId
     * @param          $entityId
     * @param   int $ruleId
     * @param   int $supplierId
     * @return string Modified code - all rules were successfully applied
     *          bool    false  - in case if any rule was not applied
     * @throws Exception
     */
    public function process(
        $brandId,
        $code,
        $ruleTypes = [
            ProductCodeRuleType::TYPE_CORRECTION,
            ProductCodeRuleType::TYPE_CLEAR_CODE,
            ProductCodeRuleType::TYPE_WRONG_CODE,
            ProductCodeRuleType::TYPE_PRINT
        ],
        $sourceTypeId = null,
        $entityId = null,
        $ruleId = null,
        $supplierId = null
    )

    {
        if (in_array(ProductCodeRuleType::TYPE_CORRECTION, $ruleTypes) && !$supplierId) {
            throw new Exception(self::ERR_MSG_MISSING_SUPPLIER);
        }
        /** @var ProductCodeRule $productCodeRuleMemory */
        $productCodeRuleMemory = ProductCodeRule::getInstance(null, 'common\models\db\memory\ProductCodeRule');
        $productCodeRuleMemory->isSimpleMode = $this->isSimpleModeRuleMemory;

        $rules = $productCodeRuleMemory->getRules($brandId, $supplierId);
        if (count($rules) === 0) {
            $correctCode = ProductHelper::correctCode($code);
            return [
                ProductCodeRuleType::TYPE_CORRECTION => $correctCode,
                ProductCodeRuleType::TYPE_PRINT      => $correctCode,
                ProductCodeRuleType::TYPE_CLEAR_CODE => $correctCode
            ];
        }

        $incomingProductCode = $code;
        $result = [];
        sort($ruleTypes, SORT_NUMERIC);
        foreach ($ruleTypes as $ruleTypeId) {
            $ruleTypeRules = ArrayHelper::getValue($rules, $ruleTypeId);
            if ($ruleTypeRules) {
                if ($ruleTypeId == ProductCodeRuleType::TYPE_WRONG_CODE) {
                    $res = $this->checkWrongCodeRules(
                        $ruleTypeRules,
                        $code,
                        [
                            'incomingProductCode' => $incomingProductCode,
                            'sourceTypeId'        => $sourceTypeId,
                            'entityId'            => $entityId ? $entityId : 0
                        ],
                        $ruleId
                    );

                    if (!$res) {
                        return false;
                    }
                } else {
                    $code = $this->checkRules($ruleTypeRules, $code);
                    $result[$ruleTypeId] = $code;
                }
            } else {
                if ($ruleTypeId == ProductCodeRuleType::TYPE_CLEAR_CODE) {
                    $code = ProductHelper::correctCode($code);
                }
                $result[$ruleTypeId] = $code;
            }
        }

        return $result;
    }

    /**
     * Function run all action for code by ruleType except wrong product code action.
     *
     * @param array $rules
     * @param string $code
     * @return string
     */
    private function checkRules($rules, $code)
    {
        foreach ($rules as $rule) {
            if ($this->checkRule($rule, $code)) {
                $code = ActionFactory::create($rule['productCodeActionId'])
                    ->run(['code' => $code, 'value' => $rule['productCodeActionValue']]);
            }
        }

        return $code;
    }

    /**
     * Function check is code is wrong(unsuitable not one condition) and put it to accumulator.
     *
     * @param array $rules
     * @param string $code
     * @param int $ruleId
     * @param array $wrongCodeData
     * @return string
     */
    private function checkWrongCodeRules($rules, $code, $wrongCodeData, $ruleId = null)
    {
        $lastRuleIndex = count($rules) - 1;
        foreach ($rules as $index => $rule) {
            if (!$ruleId || $rule['id'] == $ruleId) {
                $isPassRule = $this->checkRule($rule, $code);
                if ($isPassRule) {
                    break;
                }

                $isLastRule = $index == $lastRuleIndex;
                if ($ruleId || (!$isPassRule && $isLastRule)) {
                    if ($this->isSaveWrongCodes) {
                        WrongCode::getInstance(null, 'common\models\product\code\WrongCode')->run(
                            [
                                'code'  => $code,
                                'value' => [
                                    'brandId'             => $rule['brandId'],
                                    'incomingProductCode' => $wrongCodeData['incomingProductCode'],
                                    'wrongProductCode'    => $code,
                                    'sourceTypeId'        => $wrongCodeData['sourceTypeId'],
                                    'entityId'            => $wrongCodeData['entityId'],
                                    'codeRuleConditionId' => $this->codeRuleConditionId
                                ]
                            ]
                        );
                    }
                    $this->codeRuleConditionId = [];

                    return false;
                }
            }
        }
        $this->codeRuleConditionId = [];

        return $code;
    }

    /**
     * Function check conditions list for each action. And run it if condition return true.
     *
     * @param array $rule
     * @param string $code
     * @return bool
     */
    private function checkRule($rule, $code)
    {
        $isPassCondition = true;
        $conditions = (array)$rule['conditions'];

        /** @var $condition \common\models\product\code\condition\base\AbstractCondition;n */
        foreach ($conditions as $condition) {
            if (!$isPassCondition = $condition->run($code)) {
                if ($rule['ruleTypeId'] == ProductCodeRuleType::TYPE_WRONG_CODE) {
                    $this->codeRuleConditionId[] = $condition->getId();
                }
                break;
            }
        }

        return $isPassCondition;
    }
}
