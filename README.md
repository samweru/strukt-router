Strukt Router
=============

[![Build Status](https://travis-ci.org/pitsolu/strukt-router.svg?branch=master)](https://packagist.org/packages/strukt/router)
[![Latest Stable Version](https://poser.pugx.org/strukt/router/v/stable)](https://packagist.org/packages/strukt/router)
[![Total Downloads](https://poser.pugx.org/strukt/router/downloads)](https://packagist.org/packages/strukt/router)
[![Latest Unstable Version](https://poser.pugx.org/strukt/router/v/unstable)](https://packagist.org/packages/strukt/router)
[![License](https://poser.pugx.org/strukt/router/license)](https://packagist.org/packages/strukt/router)

## Usage

### Composer

Create `composer.json` script with contents below then run `composer update`

```js
{
    "require":{

        "strukt/router":"dev-master"
    },
    "minimum-stability":"dev"
}
```

After installation run  `composer exec static` to get `public\` directory.

```
    public/
    ├── errors
    │   ├── 403.html
    │   ├── 404.html
    │   ├── 405.html
    │   └── 500.html
    └── static
        ├── css
        │   └── style.css
        ├── index.html
        └── js
            └── script.js
```

## Get Started

```php
use Strukt\Http\Response;
use Strukt\Http\Request;
use Strukt\Http\RedirectResponse;
use Strukt\Http\JsonResponse;
use Strukt\Http\Session;

use Strukt\Router\Middleware\ExceptionHandler;
use Strukt\Router\Middleware\Authentication; 
use Strukt\Router\Middleware\Authorization;
use Strukt\Middleware\Asset::class;
use Strukt\Router\Middleware\Session as SessionMiddleware;
use Strukt\Router\Middleware\Router as RouterMiddleware;

use Strukt\Provider\Router as RouterProvider;

use Strukt\Env;

require "vendor/autoload.php";

Env::set("root_dir", getcwd());
Env::set("rel_static_dir", "/public/static");
Env::set("is_dev", true);

$app = new Strukt\Router\Kernel(Request::createFromGlobals());
$app->inject("app.dep.author", function(){

    return array(

        "permissions" => array(

            // "show_secrets"
        )
    );
});

$app->inject("app.dep.authentic", function(Session $session){

    $user = new Strukt\User();
    $user->setUsername($session->get("username"));

    return $user;
});

$app->inject("app.dep.session", function(){

    return new Session;
});

$app->providers(array(

    RouterProvider::class
));

$app->middlewares(array(

    ExceptionHandler::class,
    SessionMiddleware::class,
    Authorization::class,
    Authentication::class,
    Asset::class,
    RouterMiddleware::class
));

$app->map("/", function(){

    return "Strukt Works!";
});

$app->map("/user", function(Request $request){

    $id = $request->query->get("id");

    return new Response(sprintf("User id[%s].", $id), 200);
});

$app->map("GET","/hello/{to:alpha}", function($to){

    return new Response("Hello $to");
});

$response = $app->run();

exit($response->getContent());
```
### Mapping Classes

```php
$app->map("POST","/login", "App\Controller\UserController@login");

```
### Apache

`.htaccess` file:

```
DirectoryIndex index.php

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```

Cheers!