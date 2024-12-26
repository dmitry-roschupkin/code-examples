<?php

namespace common\db;

use yii\helpers\StringHelper;

/**
 * Class QueryAccumulator
 */
class QueryAccumulator
{
    /**
     * Real maximum query size is 65536 (SQL batch Size limit) * 1514
     * (minimal MTU [Maximum Transmission Unit] for ethernet).
     * We can use 65536 (SQL batch Size limit) * 64 = 4194304,
     * but some SQL servers add addition information into packet.
     * That's why we will use 4000000
     */
    const DEFAULT_MAX_QUERY_LENGTH = 4000000;

    /**
     * @var string Start of query. It can be string with insert|replace statement and indication of columns
     * which will be use in query.
     *
     * ~~~
     * 'INSERT INTO City(name) Values'
     * ~~~
     */
    private $queryBegin;

    /**
     * @var string End of query. It can be part of 'on duplicate key update' or other.
     *
     * ~~~
     * 'ON DUPLICATE KEY UPDATE name="Odessa"'
     * ~~~
     */
    private $queryEnd;

    /**
     * @var string Property contain values of query. Will be accumulating on each add iteration.
     */
    private $values;

    /**
     * @var null Contain length of query in bytes without length of values.
     */
    private $queryTemplateLength = null;

    /**
     * @var null Property accumulate length of query in bytes and has been compare with @maxQueryLength
     */
    private $queryLength = null;

    /**
     * @var int Max Limit of query in bytes. If this limit will be exceeded than will be call execute function.
     */
    private $maxQueryLength;

    /**
     * @var int Max Limit of insert values. If this limit will be exceeded than will be call execute function.
     */
    private $maxValuesCount = null;

    /**
     * @var int|null Property accumulate count of insert values and has been compare with @maxValuesCount
     */
    private $valuesCount = 0;

    /**
     * User function that will be called before executing accumulator query.
     * @var null|Callable
     */
    private $beforeExecute = null;

    /**
     * User function that will be called after executing accumulator query.
     * @var null|Callable
     */
    private $afterExecute = null;

    /**
     * Function call @setMaxQueryLength method which will be set max query length in bytes.
     *
     * @param int $maxQueryLength Max Limit of query.
     */
    public function __construct($maxQueryLength = self::DEFAULT_MAX_QUERY_LENGTH)
    {
        $this->setMaxQueryLength($maxQueryLength);
    }

    /**
     * Function check if @value contain some data than execute.
     */
    public function __destruct()
    {
        $this->execute();
    }

    /**
     * Function setting begin and end of query to the same property. Set queryLength by this query
     *
     * @param string $queryBegin
     * @param null|string $queryEnd
     */
    public function setQuery($queryBegin, $queryEnd = null)
    {
        $this->queryBegin = $queryBegin;
        if ($queryEnd && $queryEnd[0] != ' ') {
            $queryEnd = ' ' . $queryEnd;
        }
        $this->queryEnd = $queryEnd;

        $this->queryLength = StringHelper::byteLength($queryBegin) + StringHelper::byteLength($queryEnd);
        $this->queryTemplateLength = $this->queryLength;
    }

    /**
     * Setting limit for query length in bytes.
     *
     * @param int $maxQueryLength Max Limit of query.
     */
    public function setMaxQueryLength($maxQueryLength)
    {
        $this->maxQueryLength = $maxQueryLength;
    }

    /**
     * @return int
     */
    public function getMaxValuesCount()
    {
        return $this->maxValuesCount;
    }

    /**
     * @param int $maxValuesCount
     */
    public function setMaxValuesCount($maxValuesCount)
    {
        $this->maxValuesCount = $maxValuesCount;
    }

    /**
     * @return int
     */
    public function getValuesCount()
    {
        return $this->valuesCount;
    }

    /**
     * Set user function for calling it before executing accum query
     * @param $callback
     */
    public function setBeforeExecute($callback)
    {
        $this->beforeExecute = $callback;
    }

    /**
     * Set user function for calling it after executing accum query
     * @param $callback
     */
    public function setAfterExecute($callback)
    {
        $this->afterExecute = $callback;
    }

    /**
     * Check $isUseBindValuesHandle status and handle value for adding quotes or just implode array
     * add call @addStringValues function.
     *
     * @param array $value Item block of value which will be accumulate in query.
     * @param bool $isUseBindValues Flag set binding value
     */
    public function addValues($value, $isUseBindValues = true)
    {
        if (!$isUseBindValues) {
            $query = implode(',', $value);
        } else {
            $query = '';

            foreach ($value as $colValue) {
                if ($query) {
                    $query .= ',';
                }

                if ($colValue) {
                    $query .= \Yii::$app->db->quoteValue($colValue);
                } else {
                    if ($colValue === 0 || $colValue === 0.0) {
                        $query .= '0';
                    } elseif ($colValue === null) {
                        $query .= 'NULL';
                    } else {
                        $query .= "''";
                    }
                }
            }
        }

        $this->addStringValues($query);
    }

    /**
     * Add|Accumulate values to store for execute. After check if length of value string more than
     * max length limit than execute. Prepend this values has been handled.
     *
     * @param string $value String of value which will be accumulate in query.
     * @param bool $isNeedAddBrackets Add brackets around of value.
     */
    public function addStringValues($value, $isNeedAddBrackets = true)
    {
        if ($isNeedAddBrackets) {
            $value = '(' . $value . ')';
        }

        $isNeedComma = $this->values ? 1 : 0;
        $valueLength = StringHelper::byteLength($value);
        $queryLength = $this->queryLength + $valueLength + $isNeedComma;

        if ($queryLength >= $this->maxQueryLength
            || ($this->maxValuesCount !== null && $this->valuesCount >= $this->maxValuesCount)
        ) {
            $this->execute();
            $isNeedComma = 0;
            $this->valuesCount = 0;
        }

        $this->queryLength += $valueLength + $isNeedComma;
        if ($isNeedComma) {
            $this->values .= ',';
        }
        $this->values .= $value;
        $this->valuesCount++;
    }

    /**
     * Function build sql query by queryBegin,values and queryEnd.
     * Than execute it, clear values and set queryLength the same as queryTemplateLength
     * (Just length in bytes of query without values)
     *
     * @return integer|false number of rows affected by the execution or false if no items
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        if ($this->values) {

            if ($this->beforeExecute) {
                call_user_func($this->beforeExecute);
            }

            $countRow = $this->executeQuery();

            if ($this->afterExecute) {
                call_user_func($this->afterExecute);
            }

            return $countRow;
        }

        return false;
    }

    /**
     * Check if exist values for executing query.
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->values ? true : false;
    }

    /**
     * Execute accumulate query and reset store values property.
     * @return int
     * @throws \yii\db\Exception
     */
    private function executeQuery()
    {
        $sql = $this->queryBegin . $this->values . ' ' . $this->queryEnd;

        $countRow = \Yii::$app->db->createCommand($sql)->execute();

        $this->values = '';
        $this->queryLength = $this->queryTemplateLength;

        return $countRow;
    }
}
