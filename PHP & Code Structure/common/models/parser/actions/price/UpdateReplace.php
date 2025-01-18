<?php
/**
 * UpdateSource.php
 */

namespace common\models\parser\actions\price;

use common\models\db\memory\accumulators\ProductCross;
use common\models\db\ProductCrossType;
use common\models\db\Source;
use common\models\db\SourceType;
use common\models\parser\actions\base\AbstractAction;

/**
 * Class UpdateSource
 * @package common\models\parser\actions\price
 */
class UpdateReplace extends AbstractAction
{
    /** @var  ProductCross $pr */
    private $pc;

    /** @var  int $sourceId */
    private $sourceId;

    /**
     * Set options.
     *
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        $this->pc = new ProductCross();

        if (!$this->sourceId = Source::getIdByEntity(SourceType::TYPE_PRICE, $options['priceTypeId'])) {
            $this->sourceId = Source::addSourceByPriceType($options['priceTypeId']);
        }

        parent::init($options);
    }

    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $replace = $this->getRowParam($row, 'replaceProductId');
        if (!$replace) {
            return $row;
        }

        $existInPriceFixed = $this->getRowParam($row, 'existInPriceFixed', false);

        if (!$existInPriceFixed) {
            $productId = $this->getRowParam($row, 'productId');
            $this->pc->addValue($productId, $replace, $this->sourceId, ProductCrossType::TYPE_NOT_DIRECTED);
        }
    }
}
