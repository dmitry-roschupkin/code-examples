<?php

namespace common\models\product\code\condition\base;

use common\base\ActionInterface;

/**
 * Class AbstractCondition
 *
 * @package common\models\product\code
 */
abstract class AbstractCondition implements ActionInterface
{
    const VALUE_SEPARATOR = ',';
    const RANGE_VALUE_SEPARATOR = '-';

    /**
     * @param $value
     */
    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->init($value);
    }

    abstract protected function init($value);

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name of condition class.
     *
     * @return string
     */
    public static function getName()
    {
        return get_called_class();
    }
}
