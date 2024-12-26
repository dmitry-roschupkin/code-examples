<?php
/**
 * ParserArray.php
 */

namespace common\models\parser;

use common\helpers\CheckHelper;
use common\models\parser\base\AbstractParser;

/**
 * Class ParserArray
 * @package common\models\parser
 */
class ParserArray extends AbstractParser
{
    //TODO:audit класс ParserArray не используется
    private $array = null;

    //TODO:audit Нет текстового описания функции init
    //TODO:audit Нет описания возвращаемых данных
    /**
     * @param $config
     */
    public function init($config)
    {
        $this->array = CheckHelper::getArrayValue($config, 'array');
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
        foreach ($this->array as $index => $row) {
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
