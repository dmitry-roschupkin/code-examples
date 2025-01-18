<?php
/**
 * ActionFactory.php
 */
namespace common\models\product\code;

use common\base\FactoryInterface;
use common\helpers\ArrayHelper;
use common\models\db\ProductCodeAction;

/**
 * Class ActionFactory
 * @package common\models\product\code
 */
class ActionFactory implements FactoryInterface
{
    private static $map = [
        ProductCodeAction::ACTION_ADD_TO_END           => 'AddToEnd',
        ProductCodeAction::ACTION_REMOVE_FIRST_LETTERS => 'RemoveFirstLetters',
        ProductCodeAction::ACTION_REMOVE_LAST_LETTERS  => 'RemoveLastLetters',
        ProductCodeAction::ACTION_ADD_AFTER_POSITION   => 'AddAfterPosition'
    ];

    private static $actions;

    /**
     * Return instance of object for do some action with code by brandId
     *
     * @param null $params
     * @return mixed|\common\models\product\code\action\base\AbstractAction
     * @throws \Exception
     */
    public static function create($params = null): ?object
    {
        if (isset(static::$map[$params])) {
            if (ArrayHelper::getValue(self::$actions, $params)) {
                return self::$actions[$params];
            } else {
                $className = __NAMESPACE__ . '\action\\' .
                    static::$map[$params];
                self::$actions[$params] = new $className();

                return self::$actions[$params];
            }

        } else {
            throw new \Exception(Exception::ACTION_UNDEFINED_ID, $params);
        }
    }
}
