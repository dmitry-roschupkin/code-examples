<?php
/**
 * AbstractAction.php file description
 */

namespace common\models\parser\actions\base;

use common\models\parser\errors\base\AbstractError;
use common\models\parser\errors\DummyError;
use common\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Class AbstractAction
 *
 * Base class for parser actions
 *
 * @package common\models\parser\actions\base
 */
abstract class AbstractAction implements ActionInterface
{
    /** @var array $options */
    public $options = null;

    /** @var $errorClassObject AbstractError */
    protected $errorClassObject = null;

    /** @var bool $criticalError */
    protected $criticalError = false;

    /** @var array $errors */
    protected $errors = [];

    /** @var array $result */
    public $result = null;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->init($options);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
    }

    /**
     * Set options.
     * @param null $options Options of price list
     */
    public function init($options = null)
    {
        $this->options = $options;
        $errorClass = ArrayHelper::getValue($this->options, 'classError');
        if ($errorClass) {
            $this->errorClassObject = new $errorClass;
        } else {
            $this->errorClassObject = new DummyError();
        }
        $this->errorClassObject->init();
    }

    /**
     * Set critical error flag to true
     * @param array $row
     * @param $line
     */
    public function setCriticalError($row, $line)
    {
        $this->criticalError = true;
        $this->errorClassObject->setError(['action' => $this, 'row' => $row, 'rowNum' => $line]);
    }

    /**
     * Return critical error flag
     * @return bool
     */
    public function hasCriticalError()
    {
        return $this->criticalError;
    }

    /**
     * Set critical error flag to false
     * @return mixed|void
     */
    public function resetCriticalError()
    {
        $this->criticalError = false;
    }

    /**
     * Return array of errors
     *
     * @return array|mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns column value from config
     * @param string $columnName
     * @param mixed $default
     * @return mixed
     */
    public function getColumnNumber($columnName, $default = null)
    {
        return isset($this->options['cols'][$columnName]) ? $this->options['cols'][$columnName] : $default;
    }

    /**
     * Returns column value from config
     * @param array $row
     * @param string $columnName
     * @param mixed $default
     * @return bool|mixed
     */
    public function getColumn($row, $columnName, $default = null)
    {
        if (isset($this->options['cols'][$columnName]) && isset($row[intval($this->options['cols'][$columnName]) - 1])) {
            return $row[$this->options['cols'][$columnName] - 1];
        }
        $columnNumber = $this->getColumnNumber($columnName, $default);
        if ($columnNumber) {
            if (is_numeric($columnNumber)) {
                $columnNumber -= 1;
            }
            $res = isset($row[$columnNumber]) ? $row[$columnNumber] : $default;
        } else {
            $res = $default;
        }
        return $res;
    }

    /**
     * Return array of column params
     * @param array $row
     * @param string $columnName
     * @param mixed $default
     * @return mixed
     */
    public function getAllColumnParams($row, $columnName, $default = null)
    {
        return $this->getColumnParam($row, $columnName, null, $default);
    }

    /**
     * gets column param that was set by setColumnParam
     * @param array $row
     * @param string $columnName
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    public function getColumnParam($row, $columnName, $paramName, $default = null)
    {
        if (isset($row['columnParams'])) {

            $row = $row['columnParams'];
            $value = isset($row[$columnName]) ? $row[$columnName] : $default;

            $result = isset($value[$paramName]) ? $value[$paramName] : $default;
            return $result;
        }
        return $default;
    }

    /**
     * Set column param
     * @param array $row
     * @param string $columnName
     * @param string $paramName
     * @param mixed $value
     */
    public function setColumnParam(&$row, $columnName, $paramName, $value)
    {
        $row['columnParams'][$columnName][$paramName] = $value;
    }

    /**
     * Get row param
     * @param array $row
     * @param string $paramName
     * @param mixed $default
     * @return mixed|null
     */
    public function getRowParam($row, $paramName, $default = null)
    {
        if (isset($row['rowParams'])) {
            $value = ArrayHelper::getSimpleArrayValue($row['rowParams'], $paramName, $default);
            return $value;
        }
        return $default;
    }

    /**
     * Set row param
     * @param array $row
     * @param string $paramName
     * @param mixed $value
     */
    public function setRowParam(&$row, $paramName, $value)
    {
        $row['rowParams'][$paramName] = $value;
    }

    /**
     * Set new value in the row column
     * @param array $row
     * @param string $columnName
     * @param mixed $value
     */
    public function setColumn(&$row, $columnName, $value)
    {
        $columnNumber = $this->getColumnNumber($columnName, false);
        if ($columnNumber) {
            $row[$columnNumber - 1] = $value;
        }
    }

    /**
     * Return action's results
     *
     * @return null|array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public static function getName()
    {
        $name = substr(strrchr(get_called_class(), "\\"), 1);
        return $name;
    }

    /**
     * @inheritdoc
     */
    public function run($params = null)
    {
        $row = ArrayHelper::getValue($params, 'row');
        $line = ArrayHelper::getValue($params, 'line');
        $this->applyRow($row, $line);
        return $row;
    }

    /**
     * @param array $row
     * @param int $line
     */
    abstract protected function applyRow(&$row, $line);
}
