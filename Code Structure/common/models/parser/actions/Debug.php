<?php

namespace common\models\parser\actions;

use common\helpers\ArrayHelper;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class Debug
 * @package common\models\parser\actions
 */
class Debug extends AbstractAction
{
    private $count = '0';
    private $max = '10';

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if ($this->count < $this->max) {
            $row['LINE'] = $line;
            print_r($row);
            usleep(400000);
            $this->count++;
        }
    }
}
