<?php

namespace Hiraeth\App;

use Hiraeth;

class TwigProvider
{
	/**
	 *
	 */
	static public function getInterfaces()
	{
		return [
			'Twig\Environment'
		];
	}


	/**
	 *
	 */
	public function __invoke($twig, Hiraeth\Broker $broker)
	{
		$twig->addGlobal('app', $broker->make('Hiraeth\Application'));
		$twig->addGlobal('sys', $broker->make('Hiraeth\App\System'));
		$twig->addGlobal('users', $broker->make('Hiraeth\App\Users'));
		$twig->addGlobal('articles', $broker->make('Hiraeth\App\Articles'));
		$twig->addGlobal('parsedown', $broker->make('Parsedown'));

		return $twig;
	}
}
