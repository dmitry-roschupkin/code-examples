<?php
/**
 * ReadRows.php
 */
namespace common\models\parser\actions;

use common\models\parser\actions\base\AbstractAction;
use common\helpers\ArrayHelper;

/**
 * Class ReadRows
 * @package common\models\parser\actions
 */
class ReadRows extends AbstractAction
{
    /**
     * @param array $row
     * @param int   $line
     */
    protected function applyRow(&$row, $line)
    {
        $this->result['rows'][] = $row;
    }
}