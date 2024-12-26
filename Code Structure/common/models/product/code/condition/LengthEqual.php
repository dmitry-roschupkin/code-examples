<?php
/**
 * LengthEqual.php
 */

namespace common\models\product\code\condition;

use common\helpers\ArrayHelper;
use common\helpers\MathHelper;

/**
 * Class LengthEqual
 *
 * @package common\models\product\code\condition
 */
class LengthEqual extends base\AbstractCondition
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
     * Check is $code length equal value
     *
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = $params;
        foreach ($this->values as $value) {
            $codeLength = strlen($code);
            if (strpos($value, self::RANGE_VALUE_SEPARATOR) === false) {
                if ($codeLength == $value) {
                    return true;
                }
            } else {
                $range = explode(self::RANGE_VALUE_SEPARATOR, $value);
                $from = $range[0] !== '' ? $range[0] : null;
                $to = $range[1] !== '' ? $range[1] : null;

                if (MathHelper::inRange($codeLength, $from, $to)) {
                    return true;
                }
            }
        }

        return false;
    }
}
