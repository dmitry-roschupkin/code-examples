<?php
/**
 * CheckPositionSymbol.php
 */

namespace common\models\product\code\condition;

use common\helpers\ArrayHelper;
use common\helpers\MathHelper;

/**
 * Class CheckPositionSymbol
 *
 * @package common\models\product\code\condition
 */
class CheckPositionSymbol extends base\AbstractCondition
{
    const POSITION_SEPARATOR = ':';

    private $values = [];
    private $position = 0;

    /**
     * @param $value
     * @return mixed|void
     */
    protected function init($value)
    {
        $position = strpos($value, self::POSITION_SEPARATOR);
        if ($position) {
            $this->position = substr($value, 0, $position);
            $this->position = $this->position === 0 ? $this->position : $this->position - 1;
            $value = substr($value, strlen($this->position) + 1);
        }
        $this->values = explode(self::VALUE_SEPARATOR, $value);
    }

    /**
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        $code = $params;
        $symbols = str_split($code);

        foreach ($this->values as $value) {
            if (trim($value) == '' || $value === null) {
                continue;
            }
            if (strpos($value, self::RANGE_VALUE_SEPARATOR) !== false) {
                $range = explode(self::RANGE_VALUE_SEPARATOR, $value);
                $from = $range[0] !== '' ? $range[0] : null;
                $to = $range[1] !== '' ? $range[1] : null;

                if (MathHelper::inRange($symbols[$this->position], $from, $to)) {
                    return true;
                }
            } elseif (strpos(substr($code, $this->position), str_replace(' ', '', $value)) === 0) {
                return true;
            }
        }

        return false;
    }
}
