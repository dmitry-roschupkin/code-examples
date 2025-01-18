<?php
/**
 * WrongCode.php
 */

namespace common\models\product\code;

use common\base\ActionInterface;
use common\base\SingletonInterface;
use common\base\SingletonTrait;
use common\models\db\memory\accumulators\WrongProductCode;
use common\models\db\memory\accumulators\WrongProductCodeCondition;
use yii\db\Expression;
use common\models\db;
use common\helpers\ArrayHelper;

/**
 * Class WrongCode
 * @package common\models\product\code\action
 */
class WrongCode implements ActionInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * Need for singleton initialize optimization.
     */
    const WRONG_PRODUCT_CODE_NAMESPACE = 'common\models\db\memory\accumulators\WrongProductCode';
    const WRONG_PRODUCT_CODE_CONDITION_NAMESPACE = 'common\models\db\memory\accumulators\WrongProductCodeCondition';

    /**
     * Get name of action class.
     *
     * @return string
     */
    public static function getName()
    {
        return 'WrongCode';
    }

    /**
     * Add information about code accumulatively to table WrongProductCode if condition for this action returns false.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        WrongProductCode::getInstance(null, self::WRONG_PRODUCT_CODE_NAMESPACE)->addValues([
            $value['brandId'],
            $value['incomingProductCode'],
            $value['wrongProductCode'],
            $value['sourceTypeId'],
            $value['entityId'],
            new Expression("NOW()"),
            0
        ]);

        $wrongProductCodeCondition = WrongProductCodeCondition::getInstance(
            null,
            self::WRONG_PRODUCT_CODE_CONDITION_NAMESPACE
        );

        foreach ($value['codeRuleConditionId'] as $codeRuleConditionId) {
            $wrongProductCodeCondition->addValues([
                $value['brandId'],
                $value['incomingProductCode'],
                $codeRuleConditionId
            ]);
        }

        return $code;
    }

    /**
     * Execute WrongProductCode accumulator query when object destructed.
     */
    public function __destruct()
    {
        WrongProductCode::getInstance(null, self::WRONG_PRODUCT_CODE_NAMESPACE)->execute();
        WrongProductCodeCondition::getInstance(null, self::WRONG_PRODUCT_CODE_CONDITION_NAMESPACE)->execute();
    }
}
