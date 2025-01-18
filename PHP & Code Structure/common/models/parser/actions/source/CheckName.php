<?php
/**
 * CheckName.php
 */
namespace common\models\parser\actions\source;

use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckName
 * @package common\models\parser\actions\source
 */
class CheckName extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $name = $this->getColumn($row, 'name');
        $name = $this->normalizeName($name);

        if (strlen($name) < 3) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_NAME));
            $this->setCriticalError($row, $line);
            return;
        }

        $this->setColumnParam($row, 'name', 'name', $name);
        $this->setColumnParam($row, 'langCode', 'langCode', $this->options['langCode']);
    }

    /**
     * Handle name
     * @param mixed $name
     * @return string
     */
    private function normalizeName($name)
    {
        $name = trim($name);

        if (strlen($name) == 0) {
            return '';
        }

        $name = trim($name);
        $name = preg_replace('/[\s]+/', ' ', $name);
        $name = str_replace(' ,', ',', $name);

        if (strlen($name) == 0) {
            return '';
        }

        return $name;
    }
}
