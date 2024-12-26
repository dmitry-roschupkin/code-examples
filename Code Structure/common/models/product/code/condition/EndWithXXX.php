<?php

namespace common\models\product\code\condition;

use common\helpers\ArrayHelper;
use common\helpers\MathHelper;

/**
 * Class    EndWithXXX
 *
 * @package common\models\product\code\condition
 */
class EndWithXXX extends base\AbstractCondition
{
    private $values = [];

    /**
     * @param $value
     * @return mixed|void
     */
    protected function init($value)
    {
        $this->values = explode(self::VALUE_SEPARATOR, $value);
    }

    /**
     * Check if $code ends with 'XXX'.
     * Case insensitive.
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = $params;

        foreach ($this->values as $value) {
            if (strpos($value, self::RANGE_VALUE_SEPARATOR) !== false) {
                $range = explode(self::RANGE_VALUE_SEPARATOR, $value);
                $from = $range[0] !== '' ? $range[0] : null;
                $to = $range[1] !== '' ? $range[1] : null;
                $lastSymbol = substr($code, -1);
                if (MathHelper::inRange($lastSymbol, $from, $to)) {
                    return true;
                }
            } else {
                if (substr($code, -strlen($value)) == $value) {
                    return true;
                }
            }
        }

        return false;
    }
}
