<?php

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class    CutOffUptoParentheses
 *
 * @package common\models\product\code\action
 */
class CutOffUptoParentheses extends base\AbstractAction
{
    /**
     * Cut off string upto '(' or ')' char
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        $chars = explode(',', $value);
        $position = [];

        foreach ($chars as $char) {
            $position[] = strpos($code, $char);
        }

        if (!empty($position)) {
            return substr($code, 0, min($position));
        } else {
            return $code;
        }
    }
}
