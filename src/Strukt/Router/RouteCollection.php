<?php

namespace Strukt\Router;

use Strukt\Http\Exception\MethodNotAllowed as MethodNotAllowedException;
use Strukt\Core\TokenQuery as TokQ;

class RouteCollection{

	private $route_patterns;
	private $route_matches;

	public function __construct(){

		$this->route_patterns = [];
		$this->route_matches = [];
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

	public function withToken(string $token){

		list($key, $val) = explode(":", $token);

		$routes = [];
		foreach($this->route_patterns as $pattern=>$route){

			$tokq = $route->getTokenQuery();
			if(!is_null($tokq)){

				if($tokq->has($key)){

					$found = false;
					$item = $tokq->get($key);
					if(is_array($item))
						if(in_array($val, $item))
							$found = true;

					if(is_string($item))
						if($item == $val)
							$found = true;

					if($found)
						$routes[$pattern] = $route;
				}
			}
		}

		$this->route_matches = $routes;

		return $this;
	}

	public function getMatches(){

		$properties = [];

		foreach($this->route_matches as $pattern=>$route){

			$properties[] = array(

				"pattern"=>$pattern,
				"method"=>$route->getMethod(),
				"permission"=>$route->getName(),
				"tokens"=>$route->getTokens()
			);
		}

		return $properties;
	}

	public function getRoute($method, $uri){

		$routes = $this->route_patterns;

		if(!empty($this->route_matches)){

			$routes = $this->route_matches;
			$this->route_matches = [];
		}

		$parser = new UrlParser(array_keys($routes));

		$pattern = $parser->whichPattern($uri);

		if(!is_null($pattern)){

			$route = $routes[$pattern];

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