<?php

namespace Dmkit\Phalcon\TokenGetter;

use Dmkit\Phalcon\TokenGetter\Adapter;

class Header extends Adapter
{
	protected $key='Authorization';
	protected $prefix='Bearer';

	protected function getToken()
	{
		return $this->_Request->getHeader($this->key);
	}

	public function parse() : string
	{
		return trim( str_ireplace($this->prefix, '', $this->getToken()));
	}

	public function setKey(string $key)
	{
		$this->key = $key;
	}

	public function  setPrefix(string $prefix)
	{
		$this->prefix = $prefix;
	}
}