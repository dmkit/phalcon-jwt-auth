<?php

use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;
use Dmkit\Phalcon\Auth\TokenGetter\TokenGetter;
use Phalcon\Http\RequestInterface;
use PHPUnit\Framework\TestCase;

class TokenGetterTest extends TestCase
{
	public function testParserSingle()
	{
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';
		
		$response = $this->createMock(RequestInterface::class);
		$response->method('getHeader')->willReturn('Bearer '.$token);

		$header = new Header($response);

		$tokenGetter = new TokenGetter($header);
		$this->assertEquals($token, $tokenGetter->parse());
	}

	public function testParserMulti()
	{
		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ';
		
		$response = $this->createMock(RequestInterface::class);
		$response->method('getHeader')->willReturn(''); // return empty on first attemp
		$response->method('getQuery')->willReturn($token);

		$header = new Header($response);
		$query = new QueryStr($response);

		$tokenGetter = new TokenGetter($header, $query);
		$this->assertEquals($token, $tokenGetter->parse());
	}
}
