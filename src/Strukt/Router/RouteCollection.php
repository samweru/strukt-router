<?php

namespace Strukt\Router;

use Strukt\Http\Exception\MethodNotAllowed as MethodNotAllowedException;

class RouteCollection{

	public function __construct(){

		$this->route_patterns = [];
	}

	public function getRoutes(){

		$properties = [];

		foreach($this->route_patterns as $pattern=>$route){

			$properties[] = array(

				"pattern"=>$pattern,
				"method"=>$route->getMethod(),
				"permission"=>$route->getName()
			);
		}

		return $properties;
	}

	public function addRoute(Route $route){

		$pattern = $route->getPattern();

		$this->route_patterns[$pattern] = $route;

		$name = $route->getName();
		if(!empty($name))
			$this->route_names[$name] = $route;
	}

	public function getByName(string $name){

		if(array_key_exists($name, $this->route_names))
			return $this->route_names[$name];

		throw new \Exception(sprintf("Route:[name:%s] does not exist!", $name));
	}

	public function matchToken(string $like){

		foreach($this->route_patterns as $pattern=>$route)
			if($route->isMatch($like))
				$routes[$pattern] = $route;

		$this->route_patterns = $routes;

		return $this;
	}

	public function getRoute($method, $uri){

		$parser = new UrlParser(array_keys($this->route_patterns));

		$pattern = $parser->whichPattern($uri);

		if(!is_null($pattern)){

			$route = $this->route_patterns[$pattern];

			$http_method = $route->getMethod();
			if($http_method != "ANY")
				if($http_method != $method)
					throw new MethodNotAllowedException();

			$params = $parser->getParams();
			if(!empty($params))
				$route->mergeParams($params);

			return $route;
		}

		return null;
	}
}