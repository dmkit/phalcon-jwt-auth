<?php
namespace Dmkit\Phalcon\Auth;

user Phalcon\Mvc\Micro;

class MicroMiddleware
{
	public function __construct(Micro $app)
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
	}
}