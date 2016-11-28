<?php

namespace Dmkit\Phalcon\Auth;

use Firebase\JWT\JWT;
use Dmkit\Phalcon\Auth\Adapter;
use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

class Auth extends Adapter
{
	// callback on check
	protected $_onCheckCb = [];

	public function make(array $payload, string $key)  : string
	{
		return $this->encode($payload, $key);
	}

	public function onCheck(callable $callback)
	{
		$this->_onCheckCb[] = $callback;
	}

	public function check(TokenGetter $parser, string $key)  : bool
	{
		$token = $parser->parse();

		if(!$token) {
			$this->appendMessage('missing token');
			return false;
		}

		$payload = $this->decode($token, $key);
		if(!$payload) {
			return false;
		}

		$this->payload = $payload;

		// if any of the callback return false, this will immediately return false
		foreach($this->_onCheckCb as $callback) {
			if( $callback($this) === false ) {
				return false;
			}
		}

		return true;
	}
}