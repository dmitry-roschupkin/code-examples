<?php
/**
 * CheckVolume.php
 */
namespace common\models\parser\actions\source;

use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckVolume
 * @package common\models\parser\actions\source
 */
class CheckVolume extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $volume = $this->getColumn($row, 'volume');
        $volume = $this->normalizeVolume($volume);

        if ($volume <= 0) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_VOLUME));
            $this->setCriticalError($row, $line);
            return;
        }

        $this->setColumnParam($row, 'volume', 'volume', $volume);
    }

    /**
     * Handle volume
     * @param mixed $volume
     * @return string
     */
    private function normalizeVolume($volume)
    {
        return (float)str_replace(',', '.', trim($volume));
    }
}
