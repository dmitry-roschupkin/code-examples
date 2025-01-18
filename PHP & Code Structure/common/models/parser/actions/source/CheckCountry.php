<?php
/**
 * CheckCountry.php
 */

namespace common\models\parser\actions\source;

use common\models\db\Country;
use common\models\parser\actions\base\AbstractAction;
use common\models\parser\errors\SourceError;

/**
 * Class CheckCountry
 * @package common\models\parser\actions\source
 */
class CheckCountry extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $country = $this->getColumn($row, 'country');
        $countryList = Country::getListForDropDown();
        if (!isset($countryList[$country])) {
            $this->setRowParam($row, 'error', \Yii::t('app', SourceError::ERR_COUNTRY));
            $this->setCriticalError($row, $line);

            return;
        }

        $this->setColumnParam($row, 'country', 'country', $country);
    }
}
