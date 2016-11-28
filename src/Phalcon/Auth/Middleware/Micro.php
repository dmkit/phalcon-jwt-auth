<?php
namespace Dmkit\Phalcon\Auth\Middleware;

use Phalcon\Mvc\Micro;
use Dmkit\Phalcon\Auth\TokenGetter\TokenGetter;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\Header;
use Dmkit\Phalcon\Auth\TokenGetter\Handler\QueryStr;

class Micro
{
	protected $configkey = 'jwtAuth';
	protected $diName = 'auth';

	protected $config;
	protected $auth;

	protected $ignoreUri;

	public function __construct(Micro $app, $config=NULL)
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
			key
			exp
			alg
			leeway
			whatever
			ignoreUri[] regex:/sdasdasdasd/i:POST
			ignoreUri[] regex:/sdasdasdasd/i:POST
		*/

		$diConfig = $app->getDI()->getConfig();

		if(!$config and !$diConfig) {
			throw new InvalidArgumentException('missing DI config jwtAuth and config param');
		}

		$this->config = $config ?? $diConfig->$this->configkey;

		// let's be friendly and type cast if not array
		if( !is_array($this->config) ) {
			$this->config = (array) $this->config;			
		}

		if(isset($this->config['ignoreUri'])) {
			$this->ignoreUri = $this->config['ignoreUri'];
			unset($this->config['ignoreUri']);
		}

		$this->app = $app;

		// let's set the DI
		$this->setDi();
	}

	protected function setDi()
	{
		$self = $this;
		$this->app->getDI()->setShared($this->diName, function() use($self) {

		});
	}

	public function getIgnoreUris($ignoreUri)
	{
		if(!$ignoreUri) {
			return [];
		}

		$uris = [];
		foreach($ignoreUri as $uri) {
			if(strpos($uri, 'regex:') === false) {
				$type = 'str';
			} else {
				$type = 'regex';
				$uri = str_replace('regex:', '', $uri);
			}

			list($pattern, $methods) = explode(':', $uri);
			$uris[] = [
				'type' => $type,
				'patter' => $pattern,
				'methods' => ( !$methods ? false : explode(',', $methods) )
			];
		}

		reutrn $uris;
	}

	public function isIgnoredUri()
	{
		$ignored = $this->getIgnoreUris( $this->ignoreUri );
		// access request object
		$request = $this->app->getDI()->get('request');

		// url
		$uri = $request->getURI();
		// http method
		$method = $request->getMethod();
		
		
	}
}