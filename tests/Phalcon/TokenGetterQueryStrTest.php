<?php

use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;
use Phalcon\Http\RequestInterface;
use PHPUnit\Framework\TestCase;

class TokenGetterQueryStrTest extends TestCase
{
	public function testParser()
	{
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';
		
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($token);

		$query = new QueryStr($response);
		$this->assertEquals($token, $query->parse());
	}
}