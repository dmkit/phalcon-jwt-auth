<?php

namespace Dmkit\Phalcon\Auth\TokenGetter\Handler;

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Adapter;

class QueryStr extends Adapter
{
	protected $key='token';

	public $name='QueryStr';

	public function parse() : string
	{
		return trim( ($this->_Request->getQuery($this->key) ?? '') );
	}

	public function setKey(string $key)
	{
		$this->key = $key;
	}
}