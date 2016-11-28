<?php

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Phalcon\Http\RequestInterface;
use PHPUnit\Framework\TestCase;

class TokenGetterHeaderTest extends TestCase
{
	public function testParser()
	{
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';
		
		$response = $this->createMock(RequestInterface::class);
		$response->method('getHeader')->willReturn('Bearer '.$token);

		$header = new Header($response);
		$this->assertEquals($token, $header->parse());
	}
}
