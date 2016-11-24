<?php

namespace Dmkit\Phalcon\TokenGetter;

interface AdapterInterface
{
	public function parse(): string;
	public function exists(): bool;
}