<?php
/**
 * SourceProcessorActionFactory.php file description
 */

namespace common\models\parser;

use common\helpers\ArrayHelper;
use Yii;
use common\base\FactoryInterface;
use \common\models\parser\actions\base\ActionInterface;

/**
 * Source price processor factory
 * Class ActionFactory
 * @package common\models\parser
 */
class ActionFactory implements FactoryInterface
{
    /**
     * Creating object of each action
     *
     * @param array $params
     * @throws \Exception
     * @return mixed
     */
    public static function create($params = null): ?object
    {
        $action = ArrayHelper::getValue($params, 'action');
        $option = ArrayHelper::getValue($params, 'config');
        if ($action) {
            if (class_exists($action)) {
                $actionObj = new $action($option);
                if ($actionObj instanceof ActionInterface) {
                    return $actionObj;
                }
            }
        }

        throw new \Exception(
            Yii::t('app', "Can't create action. Check action name {action}", ['{action}' => $action])
        );
    }
}
