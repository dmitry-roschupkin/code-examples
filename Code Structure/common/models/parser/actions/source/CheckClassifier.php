<?php
/**
 * CheckClassifier.php
 */

namespace common\models\parser\actions\source;

use common\helpers\ArrayHelper;
use common\models\db\Classifier;
use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckClassifier
 * @package common\models\parser\actions\source
 */
class CheckClassifier extends AbstractAction
{
    private $classifiers = [];

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if (!$this->classifiers) {
            $this->classifiers = Classifier::find()->asArray()->all();
            $this->classifiers = ArrayHelper::map($this->classifiers, 'id', 'id');
        }

        $classifierId = $this->getColumn($row, 'classifierId');
        if (!isset($this->classifiers[$classifierId])) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_CLASSIFIER));
            $this->setCriticalError($row, $line);

            return;
        }

        $this->setColumnParam($row, 'classifierId', 'classifierId', $classifierId);
    }
}
