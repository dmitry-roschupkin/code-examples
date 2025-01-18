<?php
/**
 * SourceParser.php file description
 */

namespace common\models\parser\base;

use common\helpers\ArrayHelper;
use common\models\parser\ActionFactory;
use common\models\parser\Processor;
use Yii;

/**
 * Class Parser
 * @package common\models\parser\base
 */
abstract class AbstractParser
{
    /** @var  $processor Processor */
    protected $processor;

    protected $lastRow = null;

    /**
     * constructor
     *
     * @param null $config
     *
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->init($config);
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {

    }

    /**
     * Init parser config
     *
     * @param $config
     *
     * @throws \Exception
     */
    public function init($config)
    {
        $this->lastRow = ArrayHelper::getValue($config, 'lastRow');

        $actions = ArrayHelper::getValue($config, 'actions', []);
        $processor = new Processor();

        foreach ($actions as $action) {
            $processor->addAction(
                ActionFactory::create(['action' => $action, 'config' => $config])
            );
        }

        $this->setProcessor($processor);
    }

    /**
     * Setter of processor
     *
     * @param $processor Processor
     * @return void,
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * Function parse open csv file by path in settings and parse. Then send row blocks to the process.
     *
     * @return array
     */
    abstract public function parse();
}
