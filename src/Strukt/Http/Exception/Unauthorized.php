<?php

namespace Strukt\Http\Exception;

use Strukt\Contract\AbstractHttpException;

class Unauthorized extends AbstractHttpException{

	public function __construct($message="Unauthorized Access!"){

		$this->message = $message;
		$this->code = 401;
	}
}