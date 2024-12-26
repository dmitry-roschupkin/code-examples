<?php
/**
 * SourceParser.php file description
 */

namespace common\models\parser;

use common\helpers\CsvHelper;
use common\models\parser\base\AbstractFileParser;
use Yii;
use common\helpers\ArrayHelper;

/**
 * Class Parser
 * @package common\models\parser
 */
class ParserCsv extends AbstractFileParser
{
    /** @var string $delimiter */
    protected $delimiter = CsvHelper::CSV_ASCII_DELIMITER_COMMA;

    /** @var string $enclosure */
    protected $enclosure = CsvHelper::CSV_ENCLOSURE_DOUBLE_QUOTES;


    /**
     * Function parse open csv file by path in settings and parse.
     * Then send row blocks to the process.
     *
     * @return array
     */
    public function parse()
    {
        if (file_exists($this->filePath)) {
            $csvHandle = fopen($this->filePath, "r");
            $index = 1;

            while (!feof($csvHandle)) {
                $row = fgetcsv(
                    $csvHandle,
                    $this->maxLineLength,
                    chr($this->delimiter),
                    $this->enclosure
                );
                if ($this->lastRow !== null && $index == $this->lastRow) {
                    break;
                }

                if ($row && $index >= $this->firstRow
                    && !(count($row) == 1 && ($row[0] === null || $row[0] === "\f"))) {
                    $this->processor->process($row, $index);
                }
                $index++;
            }

            fclose($csvHandle);
        }

        $result = $this->processor->getActionsResult();
        $this->processor->destroyActions();
        return $result;
    }

    /**
     * Set file for parse
     *
     * @param $file array
     */
    public function setFile($file)
    {
        parent::setFile($file);

        $this->maxLineLength = ArrayHelper::getValue($file, 'maxLineLength', $this->maxLineLength);
        $this->delimiter     = ArrayHelper::getValue($file, 'delimiter', $this->delimiter);
        $this->enclosure     = ArrayHelper::getValue($file, 'enclosure', $this->enclosure);
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
}
