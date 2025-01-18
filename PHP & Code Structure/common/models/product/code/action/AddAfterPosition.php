<?php
/**
 * AddAfterPosition.php
 */
namespace common\models\product\code\action;

use common\helpers\ArrayHelper;

/**
 * Class AddAfterPosition
 * @package common\models\product\code\action
 */
class AddAfterPosition extends base\AbstractAction
{
    /**
     * Add value after position. (ex: 5:-, 10:- ...)
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = ArrayHelper::getValue($params, 'code');
        $value = ArrayHelper::getValue($params, 'value');
        $value = rtrim($value, ',');

        $values = explode(',', $value);
        // sorting
        if (count($values) > 1) {
            foreach ($values as $value) {
                $v = explode(':', $value);
                $tempValues[$v[0]] = $value;
            }
            ksort($tempValues);
            $values = $tempValues;
        }

        $codeLength = strlen($code);
        $preValueLength = 0;
        foreach ($values as $value) {
            $value = explode(':', $value);
            if ($value[0] < $codeLength) {
                $position = $value[0] + $preValueLength;
                $value = $value[1];
                $code = substr($code, 0, $position) . $value . substr($code, $position);
                $preValueLength += strlen($value);
            }
        }

        return $code;
    }
}
