<?php

namespace common\base;

/**
 * Interface ActionInterface
 */
interface ActionInterface
{
    /**
     * Returns name of each action
     * @return mixed
     */
    public static function getName();

    /**
     * @param null $params
     * @return mixed | bool
     */
    public function run($params = null);
}
