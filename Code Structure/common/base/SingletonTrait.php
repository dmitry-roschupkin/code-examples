<?php

namespace common\base;

/**
 * Class SingletonTrait
 */
trait SingletonTrait
{
    protected static $instance = null;

    /**
     * @param null $params
     */
    protected function init($params = null)
    {
    }

    /**
     *  Construct method must be private in singleton
     */
    private function __construct($params = null)
    {
        $this->init($params);
    }

    private function __clone()
    {
    }

    /**
     * Static function that returns instance of the class or create a new one necessity1
     *
     * @param null $params
     * @param string $className Class namespace
     * @return mixed
     */
    final public static function getInstance($params = null, $className = null)
    {
        $class = $className;
        if (!$class) {
            $class = get_called_class();
        }
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new $class($params);
        }

        return static::$instance[$class];
    }
}
