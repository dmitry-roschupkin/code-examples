<?php
/**
 * RemoveFirstLetters.php
 */

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class RemoveFirstLetters
 * @package common\models\product\code\action
 */
class RemoveFirstLetters extends base\AbstractAction
{
    /**
     * Action remove from code first letters which count in value.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        return substr($code, $value);
    }
}
