<?php

use Hiraeth\Twig\PageHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

//
// Track backwards until we discover our composer.json.
//

for (
	$root_path  = __DIR__;
	$root_path != '/' && !is_file($root_path . DIRECTORY_SEPARATOR . 'composer.json');
	$root_path  = realpath($root_path . DIRECTORY_SEPARATOR . '..')
);

$loader  = require $root_path . '/vendor/autoload.php';
$hiraeth = new Hiraeth\Application($root_path, $loader);

exit($hiraeth->run(function(PageHandler $handler, Request $request = NULL) {
	try {
		if (!$request) {
			$path    = $_SERVER['REQUEST_URI'];
			$context = [];
		} else {
			if (isset($request->getAttributes()['file'])) {
				$path = $request->getAttributes()['file'];
			} else {
				$path = $request->getUri()->getPath();
			}

			$context = [
				'request' => $request
			];
		}

		if ($template = $handler->load($path)) {
			if ($handler->isRedirect()) {
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $path . '/');
				exit();
			}

			echo $handler->render($template, $context);

		} else {
			header('HTTP/1.1 404 Not Found');
			echo 'Requested page could not be found';
		}

	} catch (Exception $e) {
		if ($this->getEnvironment('DEBUG')) {
			throw $e;
		}

		header('HTTP/1.1 500 Internal Server Error');
		echo 'Request cannot be completed at this time, please try again later.';

		return 1;
	}
}));
