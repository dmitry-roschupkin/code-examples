<?php
/**
 * DetectCountry.php
 */

namespace common\models\parser\actions\invoice;

use common\helpers\ArrayHelper;
use common\models\db\Country;
use common\models\parser\actions\base\AbstractAction;

class DetectCountry extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $country = $this->getColumn($row, 'country');

        $country = self::correctCountry($country);

        $code = self::getCountryCode($country);

        if ($code) {
            $this->setColumnParam($row, 'country', 'countryCode', $code);
        }
    }

    public static function correctCountry($country)
    {
        $country = mb_strtoupper($country);
        $country = preg_replace("/[^A-ZА-Я]/i", "", $country);

        return $country;
    }

    public static function getCountryCode($country)
    {
        $countries = Country::getIndexedCountries();

        foreach ($countries as $code => $values) {
            foreach ($values as $value) {
                if (self::correctCountry($value) == $country) {
                    return $code;
                }
            }
        }

        return null;
    }
}