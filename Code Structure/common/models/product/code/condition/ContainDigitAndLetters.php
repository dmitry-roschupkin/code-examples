<?php
/**
 * ContainDigitAndLetters.php
 */

namespace common\models\product\code\condition;

use common\helpers\ArrayHelper;

/**
 * Class ContainDigitAndLetters
 *
 * @package common\models\product\code\condition
 */
class ContainDigitAndLetters extends base\AbstractCondition
{
    const DIGIT_SYMBOL = '0';
    const LETTER_SYMBOL = '1';

    private $isCheckDigit = true;
    private $isCheckLetter = true;

    /**
     * @param $value
     * @return mixed|void
     */
    protected function init($value)
    {
        $this->isCheckDigit = strpos(self::DIGIT_SYMBOL, $value) !== false;
        $this->isCheckLetter = strpos(self::LETTER_SYMBOL, $value) !== false;
    }

    /**
     * @param array $params
     * @return string
     */
    public function run($params = null)
    {
        if ($this->isCheckDigit === $this->isCheckLetter) {
            return true;
        }

        $code = $params;
        if ($this->isCheckDigit) {
            return is_numeric($code);
        } else {
            $codeLength = strlen($code);
            for ($i = 0; $i < $codeLength; $i++) {
                if ($code[$i] >= '0' && $code[$i] <= '9') {
                    return false;
                }
            }
        }

        return true;
    }
}
