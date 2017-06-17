<?php

namespace Hiraeth\App;

use Hiraeth;
use Twig;

/**
 *
 */
class RequestProvider
{
	/**
	 *
	 */
	static public function getInterfaces()
	{
		return [
			'Psr\Http\Message\ServerRequestInterface'
		];
	}


	/**
	 *
	 */
	protected $app = NULL;


	/**
	 *
	 */
	protected $twig = NULL;

	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app, Twig\Environment $twig)
	{
		$this->app  = $app;
		$this->twig = $twig;
	}


	/**
	 *
	 */
	public function __invoke($request, Hiraeth\Broker $broker)
	{
		ob_start();
		register_shutdown_function([$this, 'handle']);

		$path   = $request->getUri()->getPath();
		$parts  = explode('/', $path);
		$target = implode('/', array_slice($parts, 0, 2));
		$slug   = implode('/', array_slice($parts, 2));

		if (isset($request->getQueryParams()['action'])) {
			$action = '/' . $request->getQueryParams()['action'];
		} elseif ($slug) {
			$action = '/read';
		} elseif (substr($request->getUri()->getPath(), -1) == '/') {
			$action = '/index';
		} else {
			$action = NULL;
		}

		return $this->request = $request
			->withAttribute('file', $target . $action)
			->withAttribute('slug', $slug ?: NULL);
	}


	/**
	 *
	 */
	public function handle()
	{
		$code = http_response_code();

		if ($code < 400) {
			echo ob_get_clean();
			exit(0);
		}

		if (ob_get_length()) {
			ob_end_clean();
		}

		$error_template = '@pages/errors/' . $code . '.html';

		if ($template = $this->twig->resolveTemplate($error_template)) {
			echo $template->render(array('request' => $this->request));
		} else {
			echo "The page could not be displayed at this time.";
		}

		exit(1);
	}
}
