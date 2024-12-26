<?php
/**
 * AbstractActionError.php
 */

namespace common\models\parser\errors\base;

/**
 * Class AbstractError
 * @package common\models\parser\errors
 */
abstract class AbstractError
{
    /** @var int $errorsCount */
    protected $errorsCount = 0;

    /**
     * @param null|array $params
     * @return mixed
     */
    abstract public function init($params = null);

    /**
     * Set error row
     * @param array $params
     * @return mixed
     */
    abstract public function setError($params);

    /**
     * Get errors count
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->errorsCount;
    }
}
