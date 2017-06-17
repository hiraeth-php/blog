<?php

namespace Hiraeth\App;

use Hiraeth\Application;
use Dotink\Jin\Parser;

/**
 *
 */
interface RepositoryInterface
{
	/**
	 *
	 */
	public function setApplication(Application $app);


	/**
	 *
	 */
	public function setParser(Parser $parser);


	/**
	 *
	 */
	public function setSystem(System $system);
}
