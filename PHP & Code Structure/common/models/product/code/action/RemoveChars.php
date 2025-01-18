<?php

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class    RemoveChars
 *
 * @package common\models\product\code\action
 */
class RemoveChars extends base\AbstractAction
{
    /**
     * Remove all specified chars.
     * Case insensitive.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        return str_ireplace($value, '', $code);
    }
}
