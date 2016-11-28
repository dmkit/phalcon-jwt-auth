<?php

namespace Dmkit\Phalcon\Auth\TokenGetter\Handler;

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Adapter;

class Header extends Adapter
{
	protected $key='Authorization';
	protected $prefix='Bearer';
	
	public function parse() : string
	{
		$raw_token = $this->_Request->getHeader($this->key);

		if(!$raw_token) {
			return '';
		}

		return trim( str_ireplace($this->prefix, '', $raw_token));
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