<?php
/**
 * AddToEnd.php
 */

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class AddToEnd
 * @package common\models\product\code\action
 */
class AddToEnd extends base\AbstractAction
{
    /**
     * Cuts off string upto 12 chars.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        return $code . $value;
    }
}
