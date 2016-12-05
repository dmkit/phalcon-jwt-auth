<?php

namespace Dmkit\Phalcon\Auth\TokenGetter;

/**
 * Dmkit\Phalcon\Auth\TokenGetter\TokenGetter.
 */
interface AdapterInterface
{
	/**
     * Gets JWT and returns it.
     *
     * @return string
     */
	public function parse(): string;
}