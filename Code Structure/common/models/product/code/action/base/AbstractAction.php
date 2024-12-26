<?php

namespace common\models\product\code\action\base;

use common\base\ActionInterface;

/**
 * Class AbstractAction
 *
 * @package common\models\product\code
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * Get name of action class.
     *
     * @return string
     */
    public static function getName()
    {
        return get_called_class();
    }
}
