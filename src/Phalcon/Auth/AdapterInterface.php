<?php

namespace Dmkit\Phalcon\Auth;

use Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface as TokenGetter;

/**
 * Dmkit\Phalcon\Auth\AdapterInterface.
 */
interface AdapterInterface
{
	/**
     * Encodes array into JWT.
     *
     * @param array $payload
     * @param string $key
     *
     * @return string
     */
	public function make(array $payload, string $key) : string; 

	/**
     * Checks and validates JWT.
     *
     * @param Dmkit\Phalcon\Auth\TokenGetter\AdapterInterface $parser
     * @param string $key
     *
     * @return bool
     */
	public function check(TokenGetter $parser, string $key) : bool;
}