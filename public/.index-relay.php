<?php

//
// Track backwards until we discover our composer.json.
//

use Relay\Runner;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

for (
	$root_path  = __DIR__;
	$root_path != '/' && !is_file($root_path . DIRECTORY_SEPARATOR . 'composer.json');
	$root_path  = realpath($root_path . DIRECTORY_SEPARATOR . '..')
);

$loader  = require $root_path . '/vendor/autoload.php';
$hiraeth = new Hiraeth\Application($root_path, $loader);

exit($hiraeth->run(function(Runner $runner, Request $request, Response $response) {
	try {
		$runner($request, $response);

	} catch (Exception $e) {
		if ($this->getEnvironment('DEBUG')) {
			throw $e;
		}

		header('HTTP/1.1 500 Internal Server Error');
		echo 'Request cannot be completed at this time, please try again later.';

		return 1;
	}
}));
