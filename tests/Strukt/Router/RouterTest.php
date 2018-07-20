<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterTest extends PHPUnit_Framework_TestCase{

	private $router;
	private $servReqMock;
	private $uriMock;

	public function setUp(){

		$registry = Strukt\Core\Registry::getInstance();

		foreach(["Ok"=>200,"Redirected"=>302] as $msg=>$code)
			if(!$registry->exists(sprintf("Response.%s", $msg)))
				$registry->set(sprintf("Response.%s", $msg), new Strukt\Event\Event(function() use($code){

					$res = new Zend\Diactoros\Response();
					$res = $res->withStatus($code);

					return $res;
				}));

		foreach(["NotFound"=>404,
				 	"MethodNotFound"=>405,
				 	"Forbidden"=>403,
					"ServerError"=>500] as $msg=>$code)
			if(!$registry->exists(sprintf("Response.%s", $msg)))
				$registry->set(sprintf("Response.%s", $msg), new Strukt\Event\Event(function() use($code){

					$res = new Zend\Diactoros\Response();
					$res = $res->withStatus($code);
					$res->getBody()->write(\Strukt\Fs::cat(sprintf("public/errors/%d.html", $code)));

					return $res;
				}));

		$this->servReqMock = $this->createMock(Psr\Http\Message\ServerRequestInterface::class);
		$this->uriMock = $this->createMock(Psr\Http\Message\UriInterface::class);

		$this->attrBag = array();

		$this->servReqMock
			->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($this->uriMock));

        $this->servReqMock
			->expects($this->any())
            ->method('withAttribute')
            ->will($this->returnCallback(function($key, $value){

            	$this->attrBag[$key] = $value;

            	return $this->servReqMock;
            }));   

        $this->servReqMock
			->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function($key){

            	return $this->attrBag[$key];
            }));   

		$this->router = new Strukt\Router\Router($this->servReqMock, $allowed = array());

		$this->router->get("/", function(){

			return "Hello World";
		});

		$this->router->try("POST", "/login/{username:alpha}", function(RequestInterface $req, 																	ResponseInterface $res){

			$username = $req->getAttribute('username');
			$password = $req->getAttribute('password');

			$digest = sha1($username.$password);

		    $res->getBody()->write($digest);

		    return $res;
		});
	}

	public function execReq($method, $path, $reqBody = null){

		if(!is_null($reqBody))
			if(!empty($reqParams = json_decode($reqBody)))
				foreach ($reqParams as $key => $val)
					$this->attrBag[$key] = $val;

		$this->uriMock->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($path));

		$this->servReqMock->expects($this->any())
			->method('getMethod')
			->will($this->returnValue($method));

	}

	public function testIndexRoute(){

		$this->execReq("GET", "/");

		$resp = $this->router->dispatch();

		$this->assertEquals("Hello World", $resp->getBody());
	}

	public function testReqRes(){

		$params = json_encode(array("password"=>"p@55w0rd"));

		$this->execReq("POST", "/login/paul", $params);

		$resp = $this->router->dispatch();

		$this->assertEquals($resp->getBody(), sha1("paulp@55w0rd"));
	}
}