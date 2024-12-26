<?php

namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class CutToAction
 *
 * @package common\models\product\code
 */
class CutOffUpto12 extends base\AbstractAction
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
        return substr($code, 0, (int)$value);
    }
}
