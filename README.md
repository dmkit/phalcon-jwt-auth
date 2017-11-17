# phalcon-jwt-auth

A simple JWT middleware for Phalcon Micro to handle stateless authentication.

## Installation
```bash
$ composer require dmkit/phalcon-jwt-auth
```
or in your composer.json
```json
{
    "require": {
		"dmkit/phalcon-jwt-auth" : "dev-master"
    }
}

```
then run
```bash
$ composer update
```

## Usage

### Configuration - Loading the config service

in config.ini or in any config file
```ini
[jwtAuth]

; JWT Secret Key
secretKey = 923753F2317FC1EE5B52DF23951B

; JWT default Payload

;; expiry time in minutes
payload[exp] = 1440
payload[iss] = phalcon-jwt-auth

; Micro Applications do not have a controller or dispatcher
; so to know the resource being called we have to check the actual URL.

; If you want to disable the middleware on certain routes or resource:
;; index
ignoreUri[] = /

;; regex pattern with http methods
ignoreUri[] = regex:/application/
ignoreUri[] = regex:/users/:POST,PUT

;; literal strings
ignoreUri[] = /auth/user:POST,PUT
ignoreUri[] = /auth/application
```

in bootstrap or index file
```php
use Phalcon\Mvc\Micro;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di\FactoryDefault;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;

// set default services
$di = new FactoryDefault();

/**
 * IMPORTANT:
 * You must set "config" service that will load the configuration file.
 */
$config = new ConfigIni( APP_PATH . "app/config/config.ini");
$di->set(
    "config",
    function () use($config) {
        return $config;
    }
);

$app = new Micro($di);

// AUTH MICRO
$auth = new AuthMicro($app);

$app->handle();
```

### Configuration - Don't want to use a config file? then pass the config instead
in bootstrap or index file
```php
use Phalcon\Mvc\Micro;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di\FactoryDefault;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;

// set default services
$di = new FactoryDefault();

$app = new Micro($di);

// SETUP THE CONFIG
$authConfig = [
    'secretKey' => '923753F2317FC1EE5B52DF23951B1',
    'payload' => [
            'exp' => 1440,
            'iss' => 'phalcon-jwt-auth'
        ],
     'ignoreUri' => [
            '/',
            'regex:/application/',
            'regex:/users/:POST,PUT',
            '/auth/user:POST,PUT',
            '/auth/application'
        ]
];

// AUTH MICRO
$auth = new AuthMicro($app, $authConfig);

$app->handle();
```

### Authentication
To make authenticated requests via http, you will need to set an authorization headers as follows:
```
Authorization: Bearer {yourtokenhere}
```
or pass the token as a query string
```
?_token={yourtokenhere}
```

### Callbacks

By default if the authentication fails, the middleware will stop the execution of routes and will immediately return a response of 401 Unauthorized. If you want to add your own handler:
```php
$auth->onUnauthorized(function($authMicro, $app) {

    $response = $app["response"];
    $response->setStatusCode(401, 'Unauthorized');
    $response->setContentType("application/json");

    // to get the error messages
    $response->setContent(json_encode([$authMicro->getMessages()[0]]));
    $response->send();

    // return false to stop the execution
    return false;
});
```

If you want an additional checking on the authentication, like intentionally expiring a token based on the payload issued date, you may do so:
```php
$auth->onCheck(function($auth) {
 // to get the payload
 $data = $auth->data();

 if($data['iat'] <= strtotime('-1 day')) ) {
    // return false to invalidate the authentication
    return false;
 }

});
```

### The Auth service

You can access the middleware by calling the "auth" service.
```php
print_r( $app['auth']->data() );

print_r( $app->getDI()->get('auth')->data('email') );

// in your contoller
print_r( $this->auth->data() );
```
If you want to change the service name:
```php
AuthMicro::$diName = 'jwtAuth';
```

### Creating a token

In your controller or route handler
```php
$payload = [
    'sub'   => $user->id,
    'email' => $user->email,
    'username' =>  $user->username,
    'role'  => 'admin',
    'iat' => time(),
];
$token = $this->auth->make($payload);
```

### Accessing the authenticated user / data
In your controller or route handler
```php
echo $this->auth->id(); // will look for sub or id payload

echo $this->auth->data(); // return all payload

echo $this->auth->data('email');
```


### Extending
If you want to add your own middleware or play around:
```php
Dmkit\Phalcon\Auth\Auth.php and its adapters - does all the authentication

Dmkit\Phalcon\Auth\TokenGetter\TokenGetter.php and its adapters - does the parsing or getting of token
```

### JWT
Phalcon JWT Auth uses the Firebase JWT library. To learn more about it and JSON Web Tokens in general, visit: https://github.com/firebase/php-jwt
https://jwt.io/introduction/

### Tests
Install PHPUnit https://phpunit.de/getting-started.html
```php
$ phpunit --configuration phpunit.xml.dist
PHPUnit 5.6.5 by Sebastian Bergmann and contributors.

......["missing token"].["members option"].["members put"].["members put"].["Expired token"].["members post"]....                                                   15 / 15 (100%)

Time: 73 ms, Memory: 10.00MB

OK (15 tests, 27 assertions)

```
