<?php

namespace common\models\db\memory;

use common\base\SingletonInterface;
use common\base\SingletonTrait;
use common\models\db;
use common\helpers\ArrayHelper;

/**
 * Class Brand
 */
class Brand implements SingletonInterface
{
    use SingletonTrait;

    /** @var null $brands */
    private $brands = null;

    /**
     * Return brand by name or one bran if $name !== null
     *
     * @param null $name
     * @param null $case
     * @return null
     */
    public function getBrand($name = null)
    {
        if (!$this->brands) {
            $this->readBrands();
        }

        if ($name) {
            return ArrayHelper::getValue($this->brands, mb_strtoupper($name));
        }

        return $this->brands;
    }

    /**
     * @return mixed
     */
    public function getAnyBrand()
    {
        $brands = $this->getBrand();

        return current($brands);
    }

    /**
     * Read all brands to $this->brands
     * @param $case
     */
    private function readBrands()
    {
        //TODO: Need use cache there
        $brandsArray = db\Brand::find()->asArray()->all();
        $this->brands = ArrayHelper::map($brandsArray, 'code', 'id');
        $this->brands = array_change_key_case($this->brands, CASE_UPPER);
    }
}
