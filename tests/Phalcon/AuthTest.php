<?php

use Dmkit\Phalcon\Auth\Auth;
use Dmkit\Phalcon\Auth\TokenGetter\TokenGetter;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;
use Phalcon\Http\RequestInterface;
use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;

class AuthTest extends TestCase
{

	protected $parser;
	protected $jwt;

	protected $secretKey;

	protected $options;

	protected function setUp()
	{
		$this->secretKey = 'secret key';

		$this->options = [
				'sub' => 123,
				'exp' => 120
			];

		$options = $this->options;
		$options['exp'] = strtotime('+2 hours');

		$this->jwt = JWT::encode($options, $this->secretKey);
	}

	public function testMake()
	{
		$auth = new Auth;

		// pass exp as constructor
		$token = $auth->make($this->options, $this->secretKey);
		$this->assertEquals($this->jwt, $token);
	}

	public function testWithEmptyAuth()
	{
		$auth = new Auth;
		$auth->id();
		$this->assertEquals(NULL, $auth->id());
	}

	public function testCheckSuccess()
	{
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($this->jwt);
		$response->method('getHeader')->willReturn('');

		$query = new QueryStr($response);
		$header = new Header($response);

		$parser = new TokenGetter($header, $query);

		$auth = new Auth;

		$this->assertTrue($auth->check($parser, $this->secretKey));

		$this->assertEquals(123, $auth->id());

		$options = $this->options;
		$options['exp'] = strtotime('+2 hours');

		$this->assertEquals($options, $auth->data());
		$this->assertEquals($options['sub'], $auth->data('sub'));
	}

	public function testCheckCallback()
	{
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($this->jwt);


		$auth = new Auth;

		$auth->onCheck(function($auth) {
			$auth->appendMessage('callback 1');
		});

		$auth->onCheck(function($auth) {
			$auth->appendMessage('callback 2');
			return false;
		});

		$auth->onCheck(function($auth) {
			$auth->appendMessage('callback 3');
		});

		$this->assertTrue( !$auth->check(new QueryStr($response), $this->secretKey) );

		// makse sure callback were properly called
		$expected_errors = [
			'callback 1', 'callback 2'
		];
		$this->assertEquals($expected_errors, $auth->getMessages());
	}


	public function testCheckFail()
	{
		// let's expired the jwt
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($this->jwt);

		$auth = new Auth;

		JWT::$timestamp = strtotime('+1 week');

		$this->assertTrue( !$auth->check(new QueryStr($response), $this->secretKey) );

		$expected_errors = ['Expired token'];

		$this->assertEquals($expected_errors, $auth->getMessages());
	}

}
