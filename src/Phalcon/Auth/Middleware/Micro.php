<?php
namespace Dmkit\Phalcon\Auth\Middleware;

use Phalcon\Mvc\Micro as MvcMicro;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Dmkit\Phalcon\Auth\Auth;
use Dmkit\Phalcon\Auth\TokenGetter\TokenGetter;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;

/**
 * Dmkit\Phalcon\Auth\Middleware\Micro.
 * The concept of controllers doesn't exist in Micro apps
 * so checking of URLS and methods have to be done on the Request level.
 */
class Micro
{
	// config key
	public static $configDi = 'config';

	// config section key
	public static $configSection = 'jwtAuth';

	// DI name
	public static $diName = 'auth';

	// JWT payload
	protected $payload;

	// ignored urls
	protected $ignoreUri;

	// JWT secret key
	protected $secretKey;

	// Auth Object
	protected $auth;

	// Unauthorize calllback
	protected $_onUnauthorized;

	/**
     * Sets app and config.
     *
     * @param Phalcon\Mvc\Micro $app
     * @param array|config $config
     *
     */
	public function __construct(MvcMicro $app, array $config=NULL)
	{
		/**
		 * example of config:
		 * [jwtAuth]
		 * secretKey = nSrL7k4/7NcW|AN
		 * payload[exp] = 120
		 * payload[iss] = phalcon-jwt-auth 
		 * payload[sub] = 123 
		 * payload[name] = John Doe 
		 * payload[role] = admin 
		 * ignoreUri[] = regex:/register/:POST
		 * ignoreUri[] = /register
		 */

		if(!$config && !$app->getDI()->has(self::$configDi)) {
			throw new \InvalidArgumentException('missing DI config jwtAuth and config param');
		}

		if(!$config && !isset($app[self::$configDi]->{self::$configSection})) {
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
		$this->setEventChecker();
	}

	/**
     * Sets DI
     *
     */
	protected function setDi()
	{
		$this->app[self::$diName] = $this;
	}

	/**
     * Sets event authentication.
     *
     */
	protected function setEventChecker()
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

	/**
     * Checks the uri and method if it has a match in the passed self::$ignoreUris.
     *
     * @param string $uri
     * @param string $method HTTP METHODS
     *
     * @return bool
     */
	protected function hasMatchIgnoreUri($uri, $method)
	{
		foreach($this->ignoreUri as $uri) {
			if(strpos($uri, 'regex:') === false) {
				$type = 'str';
			} else {
				$type = 'regex';
				$uri = str_replace('regex:', '', $uri);
			}

			list($pattern, $methods) = ( strpos($uri, ':') === false ? [$uri, false] : explode(':', $uri ) );
			$methods = ( !$methods || empty($methods) ? false : explode(',', $methods) );

			$match = ( $type == 'str' ? $uri == $pattern : preg_match($pattern, $uri) );
			if( $match && (!$methods || in_array($method, $methods)) ) {
				return true;
			}
		}

		return false;
	}

	/**
     * Checks if the URI and HTTP METHOD can bypass the authentication.
     *
     * @return bool
     */
	public function isIgnoreUri()
	{
		if(!$this->ignoreUri) {
			return false;
		}

		// access request object
		$request = $this->app['request'];

		// url
		$uri = $request->getURI();

		// http method
		$method = $request->getMethod();

		return $this->hasMatchIgnoreUri($uri, $method);
	}

	/**
     * Authenticates.
     *
     * @return bool
     */
	public function check()
	{
		$request = $this->app['request'];
		$getter = new TokenGetter( new Header($request), new  QueryStr($request));
		return $this->auth->check($getter, $this->secretKey);
	}

	/**
     * Authenticates.
     *
     * @return bool
     */
	public function make($data)
	{
		$payload = array_merge($this->payload, $data);
		return $this->auth->make($payload, $this->secretKey);
	}

	/**
     * Adds a callback to the Check call
     *
     * @param callable $callback
     */
	public function onCheck($callback) 
	{
		$this->auth->onCheck($callback);
	}

	/**
     * Sets the unauthorized return
     *
     * @param callable $callback
     */
	public function onUnauthorized(callable $callback)
	{
		$this->_onUnauthorized = $callback;
	}

	/**
     * Calls the unauthorized function / callback
     *
     * @return bool return false to cancel the router
     */
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

	/**
     * Returns error messages
     *
     * @return array
     */
	public function getMessages()
	{
		return $this->auth->getMessages(); 
	}

	/**
     * Returns JWT payload sub or payload id.
     *
     * @return string
     */
	public function id()
	{
		return $this->auth->id();
	}

	/**
     * Returns payload or value of payload key.
     *
     * @param array $payload
     * @param string $key
     *
     * @return array|string
     */
	public function data($field=NULL)
	{
		return $this->auth->data($field);
	}
}