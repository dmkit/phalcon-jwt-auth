<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;
use Phalcon\Http\RequestInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Firebase\JWT\JWT;

use PHPUnit\Framework\TestCase;

class MiddlewareMicroTest extends TestCase
{
	protected $app;
	protected $middleware;
	protected $config;

	public function setUp()
	{
		$di = new FactoryDefault();

		$this->app = new Micro($di);

		$this->config = [
			'secretKey' => 'secret key',
			'payload' 	=> [
				"sub" 	=> "1234567890",
				"name" 	=> "John Doe",
				"admin"	=> true
			],
			'ignoreUri' => [
				'regex:/members:PUT'
			]
		];

		$config = new class{};
		$config->jwtAuth = $this->config;

		//  let's setup the DI config here
		$this->app['config'] = function() use($config) { return $config; };

		$this->middleware = new AuthMicro($this->app);

		$app = $this->app;

		$this->app->get('/', function() use($app) {
			$response = $app["response"];
			$response->setStatusCode(200);
			$response->setContentType("application/json");
			$response->setContent(json_encode(['index get']));
			$response->send();
		});

		$this->app->get('/members', function() use($app) {
			$response = $app["response"];
			$response->setStatusCode(200);
			$response->setContentType("application/json");
			$response->setContent(json_encode(['members get']));
			$response->send();
		});

		$this->app->post('/members', function() use($app) {
			$response = $app["response"];
			$response->setStatusCode(200);
			$response->setContentType("application/json");
			$response->setContent(json_encode(['members post']));
			$response->send();
		});

		$this->app->put('/members', function() use($app) {
			$response = $app["response"];
			$response->setStatusCode(200);
			$response->setContentType("application/json");
			$response->setContent(json_encode(['members put']));
			$response->send();
		});

		$this->app->options('/members', function() use($app) {
			$response = $app["response"];
			$response->setStatusCode(204);
			$response->setContentType("application/json");
			$response->setContent(json_encode(['members option']));
			$response->send();
		});

	}

	public function testLookForTokenFail()
	{
		//  override for testing
		$_SERVER['REQUEST_URI'] = '/members';

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(401,  $this->app['response']->getStatusCode());
		$this->assertEquals('["missing token"]',  $this->app['response']->getContent());
	}

	public function testIgnoreOptionMethod()
	{
		//  override for testing
		$_SERVER['REQUEST_URI'] = '/members';
		$_SERVER["REQUEST_METHOD"] = "OPTIONS";

		$this->middleware->setIgnoreOptionsMethod();

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(204,  $this->app['response']->getStatusCode());
	}

	public function testIgnoreUri()
	{
		$_SERVER['REQUEST_URI'] = '/members';
		$_SERVER["REQUEST_METHOD"] = "PUT";

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(200,  $this->app['response']->getStatusCode());
		$this->assertEquals('["members put"]',  $this->app['response']->getContent());
	}

	public function testIgnoreUriWithToken()
	{
		$_SERVER['REQUEST_URI'] = '/members';
		$_SERVER["REQUEST_METHOD"] = "PUT";

		$payload = $this->config['payload'];

		$jwt = JWT::encode($payload, $this->config['secretKey']);

		$_GET['_token'] = $jwt;

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(200,  $this->app['response']->getStatusCode());
		$this->assertEquals('["members put"]',  $this->app['response']->getContent());
		$this->assertEquals($payload['sub'],  $this->app['auth']->id());
	}

	public function testPassedExpiredToken()
	{
		$_SERVER['REQUEST_URI'] = '/members';
		$_SERVER["REQUEST_METHOD"] = "POST";

		$payload = $this->config['payload'];
		// let's expired the token
		$payload['exp'] = -20;
		$jwt = JWT::encode($payload, $this->config['secretKey']);

		$_GET['_token'] = $jwt;

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(401,  $this->app['response']->getStatusCode());
		$this->assertEquals('["Expired token"]',  $this->app['response']->getContent());
	}

	public function testPasssedValidToken()
	{
		$_SERVER['REQUEST_URI'] = '/members';
		$_SERVER["REQUEST_METHOD"] = "POST";

		$payload = $this->config['payload'];
		// let's expired the token
		$jwt = JWT::encode($payload, $this->config['secretKey']);

		$_GET['_token'] = $jwt;

		// call this on test methods instead
		$this->app->handle('/members');

		$this->assertEquals(200,  $this->app['response']->getStatusCode());
		$this->assertEquals('["members post"]',  $this->app['response']->getContent());

		// make sure data is correct
		$this->assertEquals($payload,  $this->app['auth']->data());
	}

}