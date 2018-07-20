<?php 

namespace Strukt\Router;

use Strukt\Event\Event;

class Route{

	private $matcher;
	private $callable;
	private $params;
	private $event;
	private $props;

	public function __construct($tpl_url, \Closure $callable, $group = null, $name = null){

		$this->matcher = new Matcher($tpl_url);

		$this->event = Event::newEvent($callable);

		$this->params = [];

		$this->props = array(

			"tpl_url"=>$tpl_url,
			"group"=>$group,
			"name"=>$name
		);
	}

	public function getProperties(){

		return $this->props;
	}

	public function isMatch($url){

		return $this->matcher->isMatch($url);
	}

	public function getEvent(){

		return $this->event;
	}

	public function setParam($name, $param){

		$this->params[$name] = $param;

		return $this;
	}

	public function addParam($param){

		$this->params[] = $param;

		return $this;
	}

	public function getParams(){

		return $this->matcher->getParams();
	}

	public function exec(){

		$params = array_merge($this->matcher->getParams(), $this->params);

		if(!empty($params))
			$response = $this->event->applyArgs($params)->exec();
		else
			$response = $this->event->exec();

		return $response;
	}
}