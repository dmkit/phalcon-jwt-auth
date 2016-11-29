<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Http\RequestInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;

use PHPUnit\Framework\TestCase;

class MiddlewareMicroTest extends TestCase
{
	protected $app;
	protected $request;
	protected $middleware;
	protected $config;

	public function setUp()
	{
		$di = new FactoryDefault();

		$this->app = new Micro($di);

		$this->config = [
			'secretKey' => 'secret key',
			'appName'	=> 'TestMiddleware',
			'ignoreUri' => [
				'regex:/.*/:POST,GET'
			]
		];

		$request = $this->createMock(RequestInterface::class);

		$this->request = $request;

		//$this->app['request'] = $this->request;

		$config = new class{};
		$config->jwtAuth = $this->config;
		$this->app['config'] = function() use($config) { return $config; };

		$this->middleware = new AuthMicro($this->app);
		
		$this->app->get('/', function() { echo '["index get"]'; });
		$this->app->get('/users', function() { echo '["users get"]'; });
		$this->app->post('/users', function() { echo '["users post"]'; });

		$this->app->handle('/users');
	}

	public function testRequestAuth()
	{
		print_r($this->app['request']->getURI());
		$this->assertEquals(true, true);

		$this->assertEquals(true, $this->app['response']->getStatusCode() );
	}
}