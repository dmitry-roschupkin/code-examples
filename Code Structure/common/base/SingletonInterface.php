<?php

namespace common\base;

/**
 * Interface SingletonInterface
 */
interface SingletonInterface
{
    /**
     * Static function that returns instance of the class or create a new one necessity
     * @param null $params
     * @return mixed
     */
    public static function getInstance($params = null);
}
