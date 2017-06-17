<?php

namespace Hiraeth\App;

use Hiraeth;
use Dotink\Jin\Parser;

class RepositoryProvider
{
	/**
	 *
	 */
	static public function getInterfaces()
	{
		return [
			'Hiraeth\App\RepositoryInterface'
		];
	}


	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app, System $system, Parser $parser)
	{
		$this->app    = $app;
		$this->parser = $parser;
		$this->system = $system;
	}


	/**
	 *
	 */
	public function __invoke($repository, Hiraeth\Broker $broker)
	{
		$repository->setApplication($this->app);
		$repository->setParser($this->parser);
		$repository->setSystem($this->system);

		return $repository;
	}
}
