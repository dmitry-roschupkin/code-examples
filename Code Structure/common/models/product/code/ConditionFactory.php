<?php
/**
 * ConditionFactory.php
 */

namespace common\models\product\code;

use common\base\FactoryInterface;
use common\models\db\ProductCodeCondition;

/**
 * Class ConditionFactory
 * @package common\models\product\code
 */
class ConditionFactory implements FactoryInterface
{
    private static $map = [
        ProductCodeCondition::CONDITION_CONTAIN_DIGIT_AND_LETTERS => 'ContainDigitAndLetters',
        ProductCodeCondition::CONDITION_LENGTH_EQUAL              => 'LengthEqual',
        ProductCodeCondition::CONDITION_START_FROM                => 'CheckPositionSymbol',
        ProductCodeCondition::CONDITION_SYMBOL_POSITION           => 'CheckPositionSymbol',
        ProductCodeCondition::CONDITION_END_WITH_XXX              => 'EndWithXXX',
    ];

    /**
     * Return instance of condition object which return is need use action.
     *
     * @param null $params
     * @return \common\models\product\code\condition\base\AbstractCondition mixed
     * @throws \Exception
     */
    public static function create($params = null): ?object
    {
        $id = $params['id'];
        $value = $params['value'];
        $conditionId = $params['conditionId'];

        if (isset(static::$map[$id])) {
            $className = __NAMESPACE__ . '\condition\\' . static::$map[$id];
            return new $className($conditionId, $value);

        } else {
            throw new \Exception(Exception::CONDITION_UNDEFINED_ID, $id);
        }
    }
}
