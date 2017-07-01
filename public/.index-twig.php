<?php

use Hiraeth\Twig\RequestResolver;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface as Stream;

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

exit($hiraeth->run(function(RequestResolver $resolver, Request $request = NULL, Response $response = NULL, Stream $stream = NULL) {
	if (!$request) {
		$result = $resolver();

	} elseif ($response) {
		if ($stream) {
			$result = $resolver($request, $response->withBody($stream));

		} elseif ($response->getBody()) {
			$result = $resolver($request, $response);

		} else {
			$result = $resolver($request);
		}

	} else {
		$result = $resolver($request);
	}

	if ($result instanceof Response) {
		$result->getBody()->rewind();

		header(sprintf(
			'HTTP/%s %s %s',
			$result->getProtocolVersion(),
			$result->getStatusCode(),
			$result->getReasonPhrase()
		));

		foreach ($result->getHeaders() as $name => $values) {
			$name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));

			foreach ($values as $value) {
				header(sprintf('%s: %s', $name, $value), FALSE);
			}
		}

		while (!$result->getBody()->eof()) {
			echo $result->getBody()->read(8192);
		}

		return [
			200 => 0,
			301 => 3,
			404 => 4,
			500 => 5,
		][$result->getStatusCode()];

	} else {
		return $result;
	}
}));
