<?php

namespace common\models\parser\options;

use common\base\Exception;
use common\models\db;
use common\models\parser\actions\CheckCode;
use common\models\parser\actions\InitData;
use common\models\parser\actions\InitMetricUnits;
use common\models\parser\actions\price\ApplyConditions;
use common\models\parser\actions\price\CheckPrice;
use common\models\parser\actions\price\DetectCodes;
use common\models\parser\actions\price\DetectOrInsertSupplierBrand;
use common\models\parser\actions\price\InitBasePrice;
use common\models\parser\actions\price\InitMinOrderCount;
use common\models\parser\actions\price\InsertThirdPartyCode;
use common\models\parser\actions\price\UpdatePriceFixed;
use common\models\parser\errors\PriceError;
use common\models\parser\ParserCsv;
use common\models\parser\ParserExcel;
use common\models\parser\SpreadsheetParserExcel;
use yii\db\Expression;

/**
 * Class PriceFile
 * @package common\models\parser\options
 */
class PriceFile
{
    /**
     * Returns options for price file
     *
     * @param db\PriceFile $priceFile
     * @return array
     */
    public static function getOptions($priceFile)
    {
        $option['actions'] = [
            CheckCode::class,
            DetectOrInsertSupplierBrand::class,
            ApplyConditions::class,
            InitData::class,
            CheckPrice::class,
            InitBasePrice::class,
            DetectCodes::class,
            InitMinOrderCount::class,
            InitMetricUnits::class,
            InsertThirdPartyCode::class,
            UpdatePriceFixed::class,
        ];
        $option['classError'] = PriceError::class;
        $option['file'] = [
            'path'      => $priceFile->getPathToCsv(),
            'enclosure' => '"',
        ];
        $option['cols'] = [
            'brand'          => $priceFile->template->brandCol,
            'code'           => $priceFile->template->codeCol,
            'price'          => $priceFile->template->priceCol,
            'count'          => $priceFile->template->countCol,
            'weight'         => $priceFile->template->weightCol,
            'volume'         => $priceFile->template->volumeCol,
            'length'         => $priceFile->template->lengthCol,
            'width'          => $priceFile->template->widthCol,
            'height'         => $priceFile->template->heightCol,
            'name'           => $priceFile->template->nameCol,
            'replaceCode'    => $priceFile->template->replaceCodeCol,
            'additionPrice'  => $priceFile->template->additionPriceCol,
            'robatGroup'     => $priceFile->template->robatGroupCol,
            'profit'         => $priceFile->template->profitCol,
            'minOrderCount'  => $priceFile->template->minOrderCountCol,
            'thirdPartyCode' => $priceFile->template->thirdPartyCodeCol,
            'addition1'      => $priceFile->template->additionCol1,
            'addition2'      => $priceFile->template->additionCol2,
        ];

        $option['priceFileId'] = $priceFile->id;
        $option['replaceProcessTypeId'] = $priceFile->template->replaceProcessTypeId;
        $option['createPriceTime'] = $priceFile->createPriceTime;
        $option['uploadFileStart'] = new Expression('NOW()');
        $option['supplierId'] = $priceFile->priceType->supplierId;
        $option['priceTypeId'] = $priceFile->priceTypeId;
        $option['priceTypeProfit'] = $priceFile->priceType->profit;
        $option['priceTypeName'] = $priceFile->priceType->name;
        $option['isMinOrderCount'] = $priceFile->priceType->isMinOrderCount;
        $option['typeId'] = $priceFile->priceType->typeId;
        $option['brandId'] = $priceFile->template->brandId;
        $option['firstRow'] = $priceFile->template->firstRow;
        $option['currencyCode'] = $priceFile->currencyCode;
        $option['currencyRate'] = db\Currency::getByCode($priceFile->currencyCode)['exchangeRate'];
        $option['langCode'] = $priceFile->template->langCode;
        $option['isPercentProfit'] = $priceFile->template->isPercentProfit;
        $option['purchaseProfitType'] = $priceFile->priceType->purchaseProfitTypeId;

        $option['weightUnit'] = $priceFile->template->weightUnitId;
        $option['volumeUnit'] = $priceFile->template->volumeUnitId;
        $option['sizeUnit'] = $priceFile->template->sizeUnitId;
        $option['deleteBadSymbols'] = true;

        return $option;
    }

    /**
     * Prepare file for parsing
     *
     * @param db\PriceFile $priceFile
     * @return bool
     * @throws Exception
     */
    public static function prepareFile(&$priceFile)
    {
        $ret = null;
        $pathToCsv = $priceFile->getPathToCsv();
        copy($priceFile->uploadPath, $pathToCsv);
        $sourceParser = self::getParser($priceFile->originExtension);

        return $sourceParser;
    }

    public static function getParser($extension)
    {
        switch (strtolower($extension)) {
            case db\PriceFile::EXTENSION_CSV:
            case db\PriceFile::EXTENSION_TSV:
            case db\PriceFile::EXTENSION_TXT:
                $sourceParser = ParserCsv::class;
                break;
            case db\PriceFile::EXTENSION_XLSX:
                $sourceParser = SpreadsheetParserExcel::class;
                break;
            case db\PriceFile::EXTENSION_XLS:
                $sourceParser = ParserExcel::class;
                break;
            default:
                $sourceParser = null;
        }

        return $sourceParser;
    }
}
