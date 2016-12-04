<?php
namespace Dmkit\Phalcon\Auth\Middleware;

use Phalcon\Mvc\Micro as MvcMicro;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Dmkit\Phalcon\Auth\Auth;
use Dmkit\Phalcon\Auth\TokenGetter\TokenGetter;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;

class Micro
{
	public static $configDi = 'config';
	public static $configSection = 'jwtAuth';

	public static $diName = 'auth';

	protected $payload;
	protected $ignoreUri;
	protected $secretKey;


	protected $auth;
	protected $_onUnauthorized;

	public function __construct(MvcMicro $app, array $config=NULL)
	{
		/*
		pass $app instance
		look for DI config
		add static overrider function for
			before - to parse routes and return header 401
			DI config - change key
			additional validation of Auth::check
			for default parser
		construct AUth
		construct Parser
		*/

		/*
			config - [jwtAuth]
			secretKey
			payload[exp] 123
			payload[iis] abc 
			ignoreUri[] regex:/sdasdasdasd/i:POST
			ignoreUri[] /sdasdasdasd/dadasd:POST
		*/

		if(!$config && !$app->getDI()->has(self::$configDi)) {
			throw new \InvalidArgumentException('missing DI config jwtAuth and config param');
		}

		$this->config = $config ?? $app[self::$configDi]->{self::$configSection};

		if( !is_array($this->config) ) {
			$this->config = (array) $this->config;			
		}

		if(isset($this->config['ignoreUri'])) {
			$this->ignoreUri = $this->config['ignoreUri'];
		}

		// secret key is required
		if(!isset($this->config['secretKey'])) {
			throw new \InvalidArgumentException('missing jwt secret key');
		}

		$this->secretKey = $this->config['secretKey'];
		$this->payload = $this->config['payload'] ?? [];

		$this->app = $app;
		$this->auth = new Auth;

		$this->setDi();
		$this->setBeforeRoute();
	}

	protected function setDi()
	{
		$this->app[self::$diName] = $this;
	}

	protected function setBeforeRoute()
	{
		$diName = self::$diName;

		$eventsManager = new EventsManager();
		$eventsManager->attach(
		    "micro:beforeExecuteRoute",
		    function (Event $event, $app) use($diName) {
		    	$auth = $app[$diName];

		        if($auth->isIgnoreUri()) {
		        	return true;
		        }

		        if($auth->check()) {
		        	return true;
		        }

		        return $auth->unauthorized();
		    }
		);

		$this->app->setEventsManager($eventsManager);
	}

	protected function getIgnoreUris()
	{
		if(!$this->ignoreUri) {
			return [];
		}

		$uris = [];
		foreach($this->ignoreUri as $uri) {
			if(strpos($uri, 'regex:') === false) {
				$type = 'str';
			} else {
				$type = 'regex';
				$uri = str_replace('regex:', '', $uri);
			}

			list($pattern, $methods) = ( strpos($uri, ':') === false ? [$uri, false] : explode(':', $uri ) );
			$uris[] = [
				'type' => $type,
				'pattern' => $pattern,
				'methods' => ( !$methods || empty($methods) ? false : explode(',', $methods) )
			];
		}

		return $uris;
	}

	public function isIgnoreUri()
	{
		if(!$this->ignoreUri) {
			return false;
		}

		$ignoreRules = $this->getIgnoreUris();
		// access request object
		$request = $this->app['request'];

		// url
		$uri = $request->getURI();

		// http method
		$method = $request->getMethod();
		
		foreach($ignoreRules as $rule) {
			$match = ( $rule['type'] == 'str' ? $uri == $rule['pattern'] : preg_match($rule['pattern'], $uri) );
			if( $match && (!$rule['methods'] || in_array($method, $rule['methods'])) ) {
				return true;
			}
		}

		return false;
	}

	public function check()
	{
		$request = $this->app['request'];
		$getter = new TokenGetter( new Header($request), new  QueryStr($request));
		return $this->auth->check($getter, $this->secretKey);
	}

	public function make($data)
	{
		$payload = array_merge($this->payload, $data);
		return $this->auth->make($payload, $this->secretKey);
	}

	public function onCheck($callback) 
	{
		$this->auth->onCheck($callback);
	}

	public function onUnauthorized(callable $callback)
	{
		$this->_onUnauthorized = $callback;
	}

	public function unauthorized() {
		if($this->_onUnauthorized) {
			return $this->_onUnauthorized($this, $this->app["response"]);
		}

		$response = $this->app["response"];
		$response->setStatusCode(401, 'Unauthorized');
		$response->setContentType("application/json");
		$response->setContent(json_encode([$this->getMessages()[0]]));
		$response->send();
		return false;
	}

	public function getMessages()
	{
		return $this->auth->getMessages(); 
	}

	public function id()
	{
		return $this->auth->id();
	}

	public function data()
	{
		return $this->auth->data();
	}
}