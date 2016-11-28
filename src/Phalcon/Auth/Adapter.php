<?php

namespace Dmkit\Phalcon\Auth;

use Dmkit\Phalcon\Auth\AdapterInterface;
use Firebase\JWT\JWT;
use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

abstract class Adapter implements AdapterInterface
{
	// payloads or data
	protected $payload = [];

	// window time for jwt to expire
	protected $leeway;
	
	// supported algs are on JWT::$supported_algs
	protected $algo = 'HS256';

	protected $errorMsgs = [];

	public function minToSec(int $mins)
	{
		return (60 * $mins);
	}

	public function setLeeway(int $mins) 
	{
		$this->leeway = $this->minToSec($mins);
	}

	public function setAlgo(string $alg) {
		$this->algo = $alg;
	}

	protected function decode($token, $key)
	{
		try {
			if($this->leeway) {
				JWT::$leeway = $this->leeway;
			}

			$payload = (array) JWT::decode($token, $key, [$this->algo]);
			
			return $payload;

		} catch(\Exception $e) {
			$this->appendMessage($e->getMessage());
			return false;

		}
	}

	protected function encode($payload, $key)
	{
		if( isset($payload['exp']) ) {
			$payload['exp'] = time() + $this->minToSec($payload['exp']);
		}
		return JWT::encode($payload, $key, $this->algo);
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
		return $this->payload['sub'] ?? $this->payload['id'];
	}

	public function data()
	{
		return $this->payload;
	}
}