<?php
/**
 * InvoiceFile.php
 */

namespace common\models\parser\options;

use common\models\db\IncomingProductTemplate;
use common\models\db\Supplier;
use common\models\db\SupplierContract;
use common\models\parser\actions\CheckCode;
use common\models\parser\actions\DetectBrandByCode;
use common\models\parser\actions\DetectInvoiceProduct;
use common\models\parser\actions\DetectSupplierBrand;
use common\models\parser\actions\DetectSupplierOrder;
use common\models\parser\actions\DetectSupplierOrderPosition;
use common\models\parser\actions\InitData;
use common\models\parser\actions\InitMetricUnits;
use common\models\parser\actions\invoice\DetectCountry;
use common\models\parser\actions\invoice\DetectThirdPartyCode;
use common\models\parser\actions\invoice\FormInvoiceRow;
use common\models\parser\errors\InvoiceError;

/**
 * Class PriceFile
 * @package common\models\parser\options
 */
class InvoiceFile
{
    /**
     * Returns options for invoice file
     * @param $file
     * @param $supplierId
     * @param $invoiceId
     * @return array|bool
     */
    public static function getOptions($file, $supplierId, $invoiceId)
    {
        $tpl = IncomingProductTemplate::getSupplierInvoiceTemplate($supplierId);
        if (!$tpl) {
            return false;
        }
        $option['actions'] = [
            DetectSupplierOrderPosition::class,
            DetectThirdPartyCode::class,
            CheckCode::class,
            InitData::class,
            DetectBrandByCode::class,
            DetectSupplierBrand::class,
            DetectInvoiceProduct::class,
            DetectCountry::class,
            InitMetricUnits::class,
            DetectSupplierOrder::class,
            FormInvoiceRow::class,
        ];
        $option['classError'] = InvoiceError::class;
        $option['file'] = [
            'path' => $file,
        ];

        $option['firstRow'] = $tpl['firstRow'];
        $option['cols'] = [
            'brand'                   => $tpl['brandCol'],
            'code'                    => $tpl['codeCol'],
            'price'                   => $tpl['priceCol'],
            'count'                   => $tpl['countCol'],
            'weight'                  => $tpl['weightCol'],
            'volume'                  => $tpl['volumeCol'],
            'length'                  => $tpl['lengthCol'],
            'width'                   => $tpl['widthCol'],
            'height'                  => $tpl['heightCol'],
            'name'                    => $tpl['nameCol'],
            'replaceCode'             => $tpl['replaceCodeCol'],
            'additionPrice'           => $tpl['additionPriceCol'],
            'robatGroup'              => $tpl['robatGroupCol'],
            'supplierOrderPositionId' => $tpl['supplierOrderPositionCol'],
            'supplierOrderNumber'     => $tpl['supplierOrderNumberCol'],
            'boxNumber'               => $tpl['boxNumberCol'],
            'customCode'              => $tpl['customsCodeCol'],
            'country'                 => $tpl['countryCol'],
            'thirdPartyCode'          => $tpl['thirdPartyCodeCol'],
        ];
        $option['supplierId'] = $supplierId;
        $option['isAllowReplace'] = \common\models\db\Supplier::findOne($supplierId)->isAllowReplace;
        $option['invoiceId'] = $invoiceId;

        $option['langCode'] = $tpl['langCode'];
        $option['weightUnit'] = $tpl['weightUnitId'];
        $option['volumeUnit'] = $tpl['volumeUnitId'];
        $option['sizeUnit'] = $tpl['sizeUnitId'];

        $option['currencyCode'] = Supplier::findOne($supplierId)->getCurrencyCode();

        $option['typeId'] = null;

        return $option;
    }
}
