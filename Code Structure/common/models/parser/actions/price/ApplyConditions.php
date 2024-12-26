<?php
/**
 * InitFeatures.php
 */

namespace common\models\parser\actions\price;

use common\models\parser\actions\base\AbstractAction;
use common\models\supplier\SupplierFactory;

/**
 * Class InitFeatures
 * @package common\models\parser\actions\price
 */
class ApplyConditions extends AbstractAction
{
    /** @var object Supplier */
    private static $supplier = null;

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        if (!self::$supplier) {
            self::$supplier = SupplierFactory::create($this->options);
        }
        $row = self::$supplier->applyCondition($this, compact('row', 'line'));
    }
}
