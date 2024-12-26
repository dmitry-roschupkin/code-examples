<?php
/**
 * ShoppingCartParcer.php
 */

namespace common\models\parser;

use common\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\base\InvalidValueException;

/**
 * Class ShoppingCartParcer
 * @package common\models\parser
 */
class ShoppingCartParser extends ParserExcel
{
    const MAX_ROWS_COUNT = 50000;

    /** @var int|null $maxRowsCount */
    protected $maxRowsCount;

    /** @var bool $isUseMaxRowsError */
    protected $isUseMaxRowsError;

    /**
     * @inheritdoc
     */
    public function init($config)
    {
        $config['maxSheet'] = 1;
        $this->maxRowsCount = ArrayHelper::getValue($config, 'maxRowsCount', self::MAX_ROWS_COUNT);
        $this->isUseMaxRowsError = ArrayHelper::getValue($config, 'isUseMaxRowsError', false);
        parent::init($config);
    }

    /**
     * @param Worksheet $sheet
     * @return int
     */
    protected function getMaxRowsCount($sheet)
    {
        $highestRow = parent::getMaxRowsCount($sheet);
        $isExceeded = $highestRow > $this->maxRowsCount;
        if ($isExceeded && $this->isUseMaxRowsError) {
            $action = $this->processor->actions[count($this->processor->actions) - 1];
            $action->result['limitExceededError'] = \Yii::t('app', 'Превышен лимит {limit} строк в файле', [
                'limit' => $this->maxRowsCount,
            ]);
            throw new InvalidValueException($action->result['limitExceededError']);
        }

        return $isExceeded ? $this->maxRowsCount : $highestRow;
    }
}