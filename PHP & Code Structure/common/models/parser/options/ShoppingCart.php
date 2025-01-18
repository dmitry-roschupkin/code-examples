<?php
/**
 * ShoppingCart.php
 */

namespace common\models\parser\options;

use common\helpers\ArrayHelper;
use common\models\db\Client;
use common\models\parser\actions\CheckCode;
use common\models\parser\actions\DetectBrand;
use common\models\parser\actions\shoppingCart\CheckLiquid;
use common\models\parser\actions\shoppingCart\CheckPlanCost;
use common\models\parser\actions\shoppingCart\CheckPrice;
use common\models\parser\actions\shoppingCart\DetectProduct;
use common\models\parser\actions\shoppingCart\FormShoppingCartRow;
use common\models\parser\actions\shoppingCart\InitData;
use common\models\parser\errors\ShoppingCartError;
use Yii;

/**
 * Class ShoppingCart
 * @package common\models\parser\options
 */
class ShoppingCart
{
    /**
     * @param string $file
     * @param int $clientId
     * @param bool $isVirtualSupplierOrder
     * @param bool $isAutomatic
     * @return array|bool
     */
    public static function getOptions($file, $clientId, $isVirtualSupplierOrder, $isAutomatic)
    {
        $isStock = (Client::isStock($clientId) || $clientId == Client::CLIENT_SYSTEM_ID);
        $template = $isStock ? 'stockOrder.template' : 'shoppingCartTemplate.import';
        $tpl = ArrayHelper::getValue(Yii::$app->params, $template);
        if (!$tpl) {
            return false;
        }
        $option['actions'] = [
            CheckCode::class,
            InitData::class,
            DetectBrand::class,
            DetectProduct::class,
        ];
        if ($isStock) {
            $option['actions'] = ArrayHelper::merge($option['actions'], [
                CheckPrice::class,
                CheckPlanCost::class,
                CheckLiquid::class
            ]);
        }
        $option['actions'] = ArrayHelper::merge($option['actions'], [FormShoppingCartRow::class]);

        $option['classError'] = ShoppingCartError::class;
        $option['file'] = [
            'path' => $file,
        ];

        $option['firstRow'] = $tpl['firstRow'];
        $option['cols'] = [
            'brand'            => $tpl['brandCol'],
            'code'             => $tpl['codeCol'],
            'name'             => $tpl['nameCol'],
            'price'            => $tpl['priceCol'],
            'count'            => $tpl['countCol'],
            'currency'         => $tpl['currencyCol'],
            'waitDays'         => $tpl['waitDaysCol'],
            'reference'        => ArrayHelper::getValue($tpl, 'referenceCol'),
            'stickerText'      => ArrayHelper::getValue($tpl, 'stickerTextCol'),
            'priceTypeOuterId' => $tpl['priceTypeOuterIdCol'],
            'comment'          => ArrayHelper::getValue($tpl, 'commentCol'),
            'planCost'         => $isAutomatic ? ArrayHelper::getValue($tpl, 'planCostCol') : null
        ];

        $option['clientId'] = $clientId;
        $option['brandId'] = $tpl['brandCol'];
        $option['isUseMaxRowsError'] = $isStock;

        return $option;
    }
}
