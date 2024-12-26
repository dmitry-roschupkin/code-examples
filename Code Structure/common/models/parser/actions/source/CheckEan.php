<?php
/**
 * CheckEan.php
 */

namespace common\models\parser\actions\source;

use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckEan
 * @package common\models\parser\actions\source
 */
class CheckEan extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $ean = $this->getColumn($row, 'ean');
        if (!$ean) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_EAN));
            $this->setCriticalError($row, $line);

            return;
        }

        $this->setColumnParam($row, 'ean', 'ean', $ean);
    }
}
