<?php

namespace Dmkit\Phalcon\Auth\TokenGetter\Handler;

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Adapter;

/**
 * Dmkit\Phalcon\Auth\TokenGetter\Handle\QueryStr.
 */
class QueryStr extends Adapter
{
    // Query string key
    protected $key='_token';

    /**
     * Gets the token from the query strings
     *
     * @return string
     */
    public function parse() : string
    {
        return trim(($this->_Request->getQuery($this->key) ?? ''));
    }
}
