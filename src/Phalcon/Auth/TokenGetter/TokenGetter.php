<?php

namespace Dmkit\Phalcon\Auth\TokenGetter;

use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface;

/**
 * Dmkit\Phalcon\Auth\TokenGetter\TokenGetter.
 */
class TokenGetter implements AdapterInterface
{
	// TokenGetters
	protected $getters = [];

	/**
     * Sets getters.
     *
     * @param Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface $getters
     */
	public function __construct(AdapterInterface ...$getters)
	{
		$this->getters = $getters;
	}

	/**
     * Calls the getters parser and returns the token
     *
     * @return string
     */
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