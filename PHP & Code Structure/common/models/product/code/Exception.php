<?php

namespace common\models\product\code;

/**
 * Class    Exception
 *
 * @package common\components\product\code
 */
class Exception extends \yii\base\Exception
{
    const CONDITION_UNDEFINED_ID = 'The condition with {ID} is undefined.';

    const CONDITION_UNACCEPTABLE_PARENT =
        'The class `{CLASS_NAME}` must have `{PARENT}` in parent tree.';

    const ACTION_UNDEFINED_ID = 'The action with {ID} is undefined.';

    const ACTION_UNACCEPTABLE_PARENT =
        'The class `{CLASS_NAME}` must have `{PARENT}` in parent tree.';

    /**
     * Prepare exception message.
     *
     * @param string $message
     * @param null $id
     * @param null $class
     * @param null $parent
     */
    public function __construct($message, $id = null, $class = null, $parent = null)
    {
        $message = str_replace(
            ['{ID}', '{CLASS_NAME}', '{PARENT}'],
            [$id, $class, $parent],
            $message
        );

        parent::__construct($message);
    }
}
