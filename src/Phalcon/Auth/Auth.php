<?php

namespace Dmkit\Phalcon\Auth;

use Firebase\JWT\JWT;
use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

class Auth
{
	protected $options = [];

	protected $key;
	protected $alg;

	protected $parser;

	// callback on check
	protected $_onCheckCb = [];

	protected $errorMsgs = [];

	public function __construct(array $options, TokenGetter $parser=NULL)
	{
		if( empty($options['key']) ) {
			throw new InvalidArgumentException('option [key] cannot be empty');
		}

		$this->key = $options['key'];
		unset($options['key']);

		$this->alg = $options['alg'] ?? 'HS256';
		unset($options['alg']);

		$this->options = $options;

		if($parser) {
			$this->parser = $parser;
		}
	}

	public function make(array $options=NULL)
	{
		if($options) {
			$this->options = array_merge($this->options, $options);
		}
		
		return JWT::encode($this->options, $this->key, $this->alg);
	}

	public function onCheck(callable $callback)
	{
		$this->_onCheckCb[] = $callback;
	}

	public function check(TokenGetter $parser=NULL)
	{
		if(!$this->parser && !$parser) {
			throw new InvalidArgumentException('missing token getter');
		}

		$getter = $parser ?? $this->parser;

		$token = $getter->parse();

		try {
			$options = (array) JWT::decode($token, $this->key, [$this->alg]);
			$this->options = $options;
		} catch(\Exception $e) {
			$this->appendMessage($e->getMessage());
			return false;
		} 	

		// if any of the callback return false, this will immediately return false
		foreach($this->_onCheckCb as $callback) {
			if( $callback($this) === false ) {
				return false;
			}
		}

		return true;
	}

	public function appendMessage(string $msg) 
	{
		$this->errorMsgs[] = $msg;
	}

	public function getMessages()
	{
		return $this->errorMsgs;
	}

	public function id()
	{
		return $this->options['sub'] ?? $this->options['id'];
	}

	public function data()
	{
		return $this->options;
	}
}