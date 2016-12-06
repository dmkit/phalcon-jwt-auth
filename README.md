# phalcon-jwt-auth

A simple JWT middleware for Phalcon Micro to handle stateless authentication.

## Installation
```
composer require dmkit/phalcon-jwt-auth
```

## Usage

### Loading the config service

in config.ini or in any config file
``` 
[jwtAuth]

; JWT Secret Key
secretKey = 923753F2317FC1EE5B52DF23951B

; JWT default Payload

;; expiry time in minutes
payload[exp] = 1440
payload[iss] = phalcon-jwt-auth

; Micro Applications do not have a controller or dispatcher
; so to know the resource being called we have to check the actual URL.

;; index
ignoreUri[] = /

;; regex pattern with http methods
ignoreUri[] = regex://
ignoreUri[] = regex:/users/:POST,PUT

;; literal strings
ignoreUri[] = /auth\/user:POST,PUT
ignoreUri[] = /auth\/application
```

in bootstrap or index file
```
use Phalcon\Mvc\Micro;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di\FactoryDefault;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;

// set default services
$di = new FactoryDefault();

/**
 * IMPORTANT:
 * You must set "config" service that will load the configuration file. 
 * If you want to change the service name you can call AuthMicro::$configDi = 'setting'.
 * It will look for "jwtAuth" config section.
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
