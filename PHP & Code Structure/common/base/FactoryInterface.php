<?php

namespace common\base;

/**
 * Interface FactoryInterface
 */
interface FactoryInterface
{
    /**
     * @param null $params
     * @return object|null
     */
    public static function create($params = null): ?object;
}
