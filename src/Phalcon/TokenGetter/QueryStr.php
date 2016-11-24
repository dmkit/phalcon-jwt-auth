<?php

namespace Dmkit\Phalcon\TokenGetter;

use Dmkit\Phalcon\TokenGetter\Adapter;

class QueryStr extends Adapter
{
	protected $key='token';

	protected function getToken()
	{
		return $this->_Request->getQuery($this->key);
	}

	public function parse() : string
	{
		return trim($this->getToken());
	}

	public function setKey(string $key)
	{
		$this->key = $key;
	}
}