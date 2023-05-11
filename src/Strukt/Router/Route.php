<?php 

namespace Strukt\Router;

use Strukt\Event;
use Strukt\Core\TokenQuery as TokQ;

class Route{

	private $method;
	private $pattern;
	private $params;
	private $event;
	private $name;
	private $tokens;

	public function __construct(string $pattern, 
								\Closure $callable, 
								string $method = "GET", 
								string $name = null,
								string $tokens = null){

		$this->method = $method;

		$this->pattern = $pattern;

		$this->name = $name;

		$this->params = [];

		$this->event = Event::create($callable);

		$this->tokens = $tokens;
	}

	public function getName(){

		return $this->name;
	}

	public function getMethod(){

		return $this->method;
	}

	public function getPattern(){

		return $this->pattern;
	}

	public function getEvent(){

		return $this->event;
	}

	public function getTokens(){

		return $this->tokens;
	}

	public function getTokenQuery(){

		if(!is_null($this->tokens))
			return new TokQ($this->tokens);

		return null;
	}

	/**
	* Merge request params
	* 
	* Example: /user/{id:int}/group/{gid:int} will merge params [id, gid]
	*/
	public function mergeParams(array $params){

		$this->params = array_merge($params, $this->params);	

		return $this;
	}

	/**
	* Merge request params
	* 
	* Example: For (Request $request) will set [param as Object:$request and name:'request']
	*/
	public function setParam($name, $param){

		$this->params[$name] = $param;

		return $this;
	}

	/**
	* Add request param
	* 
	* Example: /user/{id:int} will add param [id]
	*/
	public function addParam($param){

		$this->params[] = $param;

		return $this;
	}

	public function exec(){

		if(!empty($this->params))
			return $this->event->applyArgs($this->params)->exec();
		
		return $this->event->exec();
	}

	/**
	* Partial token matcher
	*/
	/**public function isMatch(string $like){

		return !empty(array_filter($this->tokens, function($v) use($like){

			return preg_match("/^".$like."/", $v);
		}));
	}*/
}