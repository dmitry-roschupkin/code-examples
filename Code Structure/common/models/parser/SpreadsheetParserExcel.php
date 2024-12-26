<?php
/**
 * ParserExcel.php
 */

namespace common\models\parser;

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;
use common\base\Exception;
use common\models\parser\base\AbstractFileParser;
use yii\log\Logger;

/**
 * Class ParserExcel
 * @package common\models\parser
 */
class SpreadsheetParserExcel extends AbstractFileParser
{
    /** @inheritdoc */
    public function init($config)
    {
        parent::init($config);
    }

    /**
     * Function parse open Excel file by path in settings and parse.
     * Then send row blocks to the process.
     *
     * @return array
     */
    public function parse()
    {
        if (file_exists($this->filePath)) {
            try {
                $this->parseXlsx($this->filePath);
            } catch (Exception $e) {
                \Yii::$app->getLog()->getLogger()->log($e->getMessage(), Logger::LEVEL_ERROR);
            }
        }

        $result = $this->processor->getActionsResult();
        $this->processor->destroyActions();

        return $result;
    }

    /**
     * XLSX parser with akeneo lib
     *
     * @param $filePath
     *
     * @return void
     */
    public function parseXlsx($filePath)
    {
        $workbook = SpreadsheetParser::open($filePath); //, "xlsx");

        $indexRow = 1;

        if ($workbook) {
          //  foreach ($workbook->getWorksheets() as $sheetIndex => $sheetName) {
                foreach ($workbook->createRowIterator(0) as $i => $row) {
                    foreach ($row as $k => $v) {
                        if ($v instanceof \DateTime) {
                            $row[$k] = $v->format('Y-m-d');
                        }
                    }

                    $this->processor->process($row, $i);

                    $indexRow++;

                    if ($this->lastRow && $indexRow >= $this->lastRow) {
                        return;
                    }
                }
           // }
        }
    }
}
