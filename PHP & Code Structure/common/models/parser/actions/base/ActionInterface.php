<?php
/**
 * ISourceProcessorAction.php file description
 */

namespace common\models\parser\actions\base;

/**
 * Interface for all price processor actions
 */
interface ActionInterface extends \common\base\ActionInterface
{
    /**
     * Return count of errors in action
     *
     * @return mixed
     */
    public function getErrors();

    /**
     * Return true if action has critical errors
     *
     * @return mixed
     */
    public function hasCriticalError();

    /**
     * Return true if action has critical errors
     *
     * @return mixed
     */
    public function resetCriticalError();
}
