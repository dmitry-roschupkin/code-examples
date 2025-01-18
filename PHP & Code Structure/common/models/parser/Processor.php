<?php
/**
 * SourceProcessor.php file description
 */

namespace common\models\parser;

use common\models\parser\actions\base\AbstractAction;
use common\models\parser\actions\base\ActionInterface;

/**
 * Getting all actions and run doAction method of each
 * Class Processor
 * @package common\models\parser
 */
class Processor
{
    /** @var null|AbstractAction[]  */
    public $actions = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Adding action to list
     * @param object $action Action object
     */
    public function addAction($action)
    {
        if ($action instanceof ActionInterface) {
            $this->actions[] = $action;
        }
    }

    /**
     * Make all actions to row, which are in cfg file. If one of the actions returns NULL -- exit
     * @param array $row One row with data from csv file.
     * @param int $index Row index.
     * @return mixed
     */
    public function process($row, $index)
    {
        $goToAction = null;
        /** @var $action AbstractAction */
        foreach ($this->actions as $action) {
            $row = $action->run(['row' => $row, 'line' => $index]);
            if ($action->hasCriticalError()) {
                $action->resetCriticalError();
                break;
            }
        }
    }

    /**
     * Print number of errors in each action
     *
     * @param array $array Store errors.
     * @return array
     */
    public function getErrors($array = [])
    {
        /** @var $action AbstractAction */
        foreach ($this->actions as $action) {
            $array[$action->getName()] = $action->getErrors();
        }
        return $array;
    }

    /**
     * Print detailed error string
     */
    public function showErrorsDetailed()
    {
        /*foreach ($this->actions as $action) {
            $action->showErrorsDetailed();
        }*/
    }

    /**
     * Return results for actions
     * @return array
     */
    public function getActionsResult()
    {
        $result = [];
        /** @var $action AbstractAction */
        foreach ($this->actions as $action) {
            $result[$action->getName()] = $action->getResult();
        }
        return $result;
    }

    /**
     * Destroy actions
     */
    public function destroyActions()
    {
        /** @var $action AbstractAction */
        foreach ($this->actions as $action) {
            $action->__destruct();
        }
    }
}
