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
		$app       = $broker->make('Hiraeth\Application');
		$sys       = $broker->make('Hiraeth\App\System');
		$auth      = $broker->make('iMarc\Auth\Manager');
		$users     = $broker->make('Hiraeth\App\Users');
		$articles  = $broker->make('Hiraeth\App\Articles');
		$packages  = $broker->make('Hiraeth\App\Packages');
		$parsedown = $broker->make('Parsedown');

		$auth->setEntity($sys->getUser() ?: $users->create());

		foreach (get_defined_vars() as $name => $value) {
			$twig->addGlobal($name, $value);
		}

		return $twig;
	}
}
