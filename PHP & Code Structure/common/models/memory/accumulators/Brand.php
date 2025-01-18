<?php

namespace common\models\db\memory\accumulators;

use common\db\QueryAccumulator;
use common\models\db;

/**
 * Class Brand
 */
class Brand
{
    /** @var \common\db\QueryAccumulator|null $accumulator */
    private $accumulator = null;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->accumulator = new QueryAccumulator();
        $this->accumulator->setQuery(
            "INSERT INTO `" . db\Brand::tableName() . "`
                (`code`,
                `name`,
                `isOrigin`)
            VALUES",
            "ON DUPLICATE KEY UPDATE
                `code` = VALUES(code),
                `name` = VALUES(name),
                `isOrigin` = VALUES(isOrigin)"
        );
    }

    /**
     * Add values to accumulator
     *
     * @param array $row
     */
    public function addValues($row)
    {
        $this->accumulator->addValues($row);
    }

    /**
     * Runs accumulator execution
     */
    public function execute()
    {
        return $this->accumulator->execute();
    }
}
