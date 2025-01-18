<?php
/**
 * ParserExcel.php
 */

namespace common\models\parser;

use common\helpers\ArrayHelper;
use common\models\parser\base\AbstractFileParser;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\base\InvalidValueException;
use yii\log\Logger;

/**
 * Class ParserExcel
 * @package common\models\parser
 */
class ParserExcel extends AbstractFileParser
{
    protected $maxSheet = null;

    /** @inheritdoc */
    public function init($config)
    {
        $this->maxSheet = ArrayHelper::getValue($config, 'maxSheet');
        parent::init($config);
    }

    /**
     * Function parse open Excel file by path in settings and parse.
     * Then send row blocks to the process.
     *
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function parse()
    {
        if (file_exists($this->filePath)) {
            /** @var BaseReader $objPHPExcel */
            $objPHPExcel = IOFactory::createReaderForFile($this->filePath);
            $objPHPExcel->setReadDataOnly(true);

            if ($this->lastRow) {
                $chunkFilter = new ChunkReadFilter();
                $objPHPExcel->setReadFilter($chunkFilter);
                $chunkFilter->setRows($this->firstRow, $this->lastRow);
            }

            $spreadsheet = $objPHPExcel->load($this->filePath);

            //$this->maxSheet = $this->maxSheet ?: $spreadsheet->getSheetCount();
            //for ($i = 0; $i < $this->maxSheet; $i++) {
                $this->parseSheet($spreadsheet->getSheet(0));
            //}
        }

        $result = $this->processor->getActionsResult();
        $this->processor->destroyActions();

        return $result;
    }

    /**
     * @param Worksheet $worksheet
     */
    protected function parseSheet($worksheet)
    {
        try {
            $highestRow = $this->getMaxRowsCount($worksheet);
            $highestColumn = $worksheet->getHighestColumn();
            $index = 1;
            for ($row = 1; $row <= $highestRow; $row++) {
                if ($this->lastRow !== null && $index == $this->lastRow) {
                    break;
                }
                if ($row >= $this->firstRow) {
                    $tmp = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                    $this->processor->process(ArrayHelper::getValue($tmp, 0), $index);
                }
                $index++;
            }
        } catch (InvalidValueException $exception) {
            \Yii::$app->getLog()->getLogger()->log($exception->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /**
     * @param Worksheet $worksheet
     *
     * @return string
     */
    protected function getMaxRowsCount($worksheet)
    {
        return $worksheet->getHighestDataRow();
    }
}
