<?php

namespace Dmkit\Phalcon\Auth;

use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

interface AdapterInterface
{
	// return token
	public function make(array $payload, string $key) : string; 

	public function check(TokenGetter $parser, string $key) : bool;
}