<?php

namespace Dmkit\Phalcon\TokenGetter;

interface AdapterInterface
{
	public function parse(): string;
}