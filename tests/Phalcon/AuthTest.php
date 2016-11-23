<?php

use Dmkit\Phalcon\Auth;

class AuthTest extends PHPUnit_Framework_TestCase
{
	public function testTest()
	{
		$this->assertEquals(Auth::test(), 'b');
	}
}
