<?php
/**
 * ParserDB.php
 */

namespace common\models\parser;

use common\components\db\ActiveRecord;
use common\helpers\ArrayHelper;
use common\helpers\CheckHelper;
use common\models\parser\base\AbstractParser;
use common\helpers\DB;

/**
 * Class ParserDB
 * @package common\models\parser
 */
class ParserDB extends AbstractParser
{
    private $query = null;
    private $queryParams = null;

    //TODO:audit Нет обозначения типов переменных (string, int и т.д)
    /**
     * Init parser config
     *
     * @param $config
     */
    public function init($config)
    {
        $this->query = CheckHelper::getArrayValue($config, 'query');
        $this->queryParams = ArrayHelper::getValue($config, 'queryParams');
        parent::init($config);
    }

    /**
     * Function parse open csv file by path in settings and parse.
     * Then send row blocks to the process.
     *
     * @return array
     */
    public function parse()
    {
        $rows = DB::query($this->query, $this->queryParams);
        foreach ($rows as $index => $row) {
            if ($this->lastRow !== null && $index == $this->lastRow) {
                break;
            }
            $this->processor->process($row, $index + 1);
        }
        $result = $this->processor->getActionsResult();
        $this->processor->destroyActions();
        return $result;
    }
}
