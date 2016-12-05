<?php

namespace Dmkit\Phalcon\Auth\TokenGetter\Handler;

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Adapter;

/**
 * Dmkit\Phalcon\Auth\TokenGetter\Handle\Header.
 */
class Header extends Adapter
{
	// header key
	protected $key='Authorization';

	// header value prefix
	protected $prefix='Bearer';
	

	/**
     * Gets the token from the headers
     *
     * @return string
     */
	public function parse() : string
	{
		$raw_token = $this->_Request->getHeader($this->key);

		if(!$raw_token) {
			return '';
		}

		return trim( str_ireplace($this->prefix, '', $raw_token));
	}

	/**
     * Sets the header value prefix
     *
     */
	public function  setPrefix(string $prefix)
	{
		$this->prefix = $prefix;
	}
}