<?php
/**
 * CheckWeight.php
 */
namespace common\models\parser\actions\source;

use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckWeight
 * @package common\models\parser\actions\source
 */
class CheckWeight extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $weight = $this->getColumn($row, 'weight');
        $weight = $this->normalizeWeight($weight);

        if ($weight <= 0) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_WEIGHT));
            $this->setCriticalError($row, $line);
            return;
        }

        $this->setColumnParam($row, 'weight', 'weight', $weight);
    }

    /**
     * Handle weight
     * @param mixed $weight
     * @return string
     */
    private function normalizeWeight($weight)
    {
        return (float)str_replace(',', '.', trim($weight));
    }
}
