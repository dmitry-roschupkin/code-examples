<?php
/**
 * AnswerFile.php
 */

namespace common\models\parser\options;

use common\models\db\IncomingProductTemplate;
use common\models\db\Supplier;
use common\models\db\SupplierAnswer;
use common\models\parser\actions\answer\FormAnswerRow;
use common\models\parser\actions\answer\InitAnswerType;
use common\models\parser\actions\CheckCode;
use common\models\parser\actions\DetectBrandByCode;
use common\models\parser\actions\DetectSupplierBrand;
use common\models\parser\actions\DetectInvoiceProduct;
use common\models\parser\actions\DetectSupplierOrder;
use common\models\parser\actions\DetectSupplierOrderPosition;
use common\models\parser\actions\InitData;
use common\models\parser\actions\invoice\DetectThirdPartyCode;
use common\models\parser\errors\AnswerError;

/**
 * Class AnswerFile
 * @package common\models\parser\options
 */
class AnswerFile
{
    /**
     * Returns options for answer file
     *
     * @param string         $file
     * @param SupplierAnswer $supplierAnswer
     * @return array|bool
     */
    public static function getOptions($file, $supplierAnswer)
    {
        $tpl = null;
        if ($supplierAnswer->templateId) {
            $tpl = IncomingProductTemplate::findOne($supplierAnswer->templateId);
        }
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
            InitAnswerType::class,
            DetectSupplierOrder::class,
            FormAnswerRow::class,
        ];

        $option['classError'] = AnswerError::class;
        $option['file'] = [
            'path' => $file,
        ];

        $option['firstRow'] = $tpl['firstRow'];
        $option['cols'] = [
            'brand'                   => $tpl['brandCol'],
            'code'                    => $tpl['codeCol'],
            'price'                   => $tpl['priceCol'],
            'count'                   => $tpl['countCol'],
            'name'                    => $tpl['nameCol'],
            'supplierOrderPositionId' => $tpl['supplierOrderPositionCol'],
            'supplierOrderNumber'     => $tpl['supplierOrderNumberCol'],
            'supplierAnswer'          => $tpl['supplierAnswerCol'],
            'thirdPartyCode'          => $tpl['thirdPartyCodeCol'],
        ];
        $option['supplierId'] = $supplierAnswer->supplierId;
        $option['answerId'] = $supplierAnswer->id;
        $option['supplierRedemptionSign'] = $tpl['supplierRedemptionSign'];
        $option['supplierRefuseSign'] = $tpl['supplierRefuseSign'];
        $option['answerType'] = $supplierAnswer->answerType;

        $option['langCode'] = $tpl['langCode'];

        $option['currencyCode'] = Supplier::findOne($supplierAnswer->supplierId)->getCurrencyCode();

        $option['typeId'] = null;

        return $option;
    }
}
