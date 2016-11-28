<?php

namespace Dmkit\Phalcon\Auth;

use Dmkit\Phalcon\Auth\AdapterInterface;
use Firebase\JWT\JWT;
use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

abstract class Adapter implements AdapterInterface
{
	// payloads or data
	protected $options = [];

	// window time for jwt to expire
	protected $leeway;

	protected $errorMsgs = [];

	public function minToSec($mins)
	{
		return (60 * $mins);
	}

	public function leeway($mins) 
	{
		$this->leeway = $this->minToSec($mins);
	}

	protected function decodeJwt(string $token, $key, $alg)
	{
		try {
			if($this->leeway) {
				JWT::$leeway = $this->leeway;
			}
			$data = (array) JWT::decode($token, $key, ( is_array($alg) ? $alg : [$alg] ));
			return $data;
		} catch(\Exception $e) {
			$this->appendMessage($e->getMessage());
			return false;
		}
	}

	protected function encodeJwt($data, $key, $alg)
	{
		return JWT::encode($data, $key, ( is_array($alg) ? $alg : [$alg] ));
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