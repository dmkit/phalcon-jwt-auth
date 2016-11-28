<?php

namespace Dmkit\Phalcon\Auth;

use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

interface AdapterInterface
{
	// return token
	public function make(array $options) : string; 

	public function check(TokenGetter $parser) : bool;
}