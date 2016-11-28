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
				'exp' => strtotime('+2 hours')
			];

		$this->jwt = JWT::encode($this->options, $this->secretKey);
	}

	public function testMake()
	{
		$options = array_merge($this->options, [
			'key'=>$this->secretKey,
			'exp'=>120
		]);
		$auth = new Auth($options);

		// pass exp as constructor
		$this->assertEquals($this->jwt, $auth->make());

		// pass exp as param
		$this->assertEquals($this->jwt, $auth->make(array('exp'=>120)));
	}

	public function testCheckSuccess()
	{
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($this->jwt);
		$response->method('getHeader')->willReturn('');

		$query = new QueryStr($response);
		$header = new Header($response);

		$parser = new TokenGetter($header, $query);
		$options = array_merge($this->options, [
			'key'=>$this->secretKey
		]);

		$auth = new Auth($options, $parser);

		$this->assertTrue($auth->check());

		$this->assertEquals(123, $auth->id());

		$this->assertEquals($this->options, $auth->data());
	}

	public function testCheckCallback()
	{
		$response = $this->createMock(RequestInterface::class);
		$response->method('getQuery')->willReturn($this->jwt);

		$options = [
			'key'=>$this->secretKey,
			'sub'=>123
		];

		$auth = new Auth($options, new QueryStr($response));

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

		$this->assertTrue( !$auth->check() );

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

		$options = [
			'key'=>$this->secretKey,
			'sub'=>123
		];

		$auth = new Auth($options, new QueryStr($response));

		JWT::$timestamp = strtotime('+1 week');

		$this->assertTrue( !$auth->check() );

		$expected_errors = ['Expired token'];

		$this->assertEquals($expected_errors, $auth->getMessages());
	}
	
}
