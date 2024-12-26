<?php
/**
 * DummyParseError.php
 */

namespace common\models\parser\errors;

use common\models\parser\errors\base\AbstractError;

/**
 * Class DummyParseError
 *
 * @package common\models\parser\errors
 */
class DummyError extends AbstractError
{
    public function init($params = null)
    {
    }

    public function setError($params)
    {
    }
}
