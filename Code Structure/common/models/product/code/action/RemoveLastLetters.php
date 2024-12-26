<?php
/**
 * RemoveLastLetters.php
 */

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class RemoveLastLetters
 * @package common\models\product\code\action
 */
class RemoveLastLetters extends base\AbstractAction
{
    /**
     * Action remove from code last letters which count in value.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        return substr($code, 0, strlen($code) -$value);
    }
}
