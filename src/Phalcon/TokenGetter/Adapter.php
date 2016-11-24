<?php

namespace Dmkit\Phalcon\TokenGetter;

use Phalcon\Http\RequestInterface;
use Dmkit\Phalcon\TokenGetter\AdapterInterface;

abstract class Adapter implements AdapterInterface
{

	protected $_Request;

	public function __construct(RequestInterface $request)
	{
		$this->_Request = $request;
	}

	public function exists() : bool
	{
		return !!$this->getToken();
	}

	abstract public function parse(): string;
	abstract protected function getToken();
}