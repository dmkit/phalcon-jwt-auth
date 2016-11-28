<?php

namespace Dmkit\Phalcon\Auth\TokenGetter;

use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface;

class TokenGetter implements AdapterInterface
{
	protected $getters = [];

	public function __construct(AdapterInterface ...$getters)
	{
		$this->getters = $getters;
	}

	public function parse() : string
	{
		foreach($this->getters as $getter) 
		{
			$token = $getter->parse();
			if($token) {
				return $token;
			}
		}
		return '';
	}
}