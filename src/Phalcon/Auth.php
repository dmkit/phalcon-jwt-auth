<?php

namespace Dmkit\Phalcon;

use Firebase\JWT\JWT;
use Dmkit\Phalcon\TokenGetter\AdapterInterface as TokenGetter;

class Auth
{
	protected $options = [];

	protected $key;
	protected $alg;

	protected $parser;

	// callback on check
	protected $_onCheck = [];

	public function __construct(array $options, TokenGetter $parser=NULL)
	{
		if( empty($options['key']) ) {
			throw new InvalidArgumentException('option [key] may not be empty');
		}

		$this->key = $options['key'];
		unset($options['key']);

		$this->alg = [ $options['alg'] ?? 'HS256' ];
		unset($options['alg']);

		$this->options = $options;
	}

	public function make(array $options=NULL)
	{
		return JWT::encode($token, $this->key);
	}

	public function onCheck(callable $callback)
	{
		$this->_onCheck[] = $callback;
	}

	public function check(TokenGetter $parser=NULL)
	{
		
	}
}