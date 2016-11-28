<?php

namespace Dmkit\Phalcon\Auth\TokenGetter;

interface AdapterInterface
{
	public function parse(): string;
}