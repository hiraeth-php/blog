<?php

namespace Hiraeth\App;

/**
 *
 */
class Package
{
	/**
	 *
	 */
	public $description = NULL;


	/**
	 *
	 */
	public $name = NULL;


	/**
	 *
	 */
	public $root = NULL;


	/**
	 *
	 */
	protected $readme = NULL;


	/**
	 *
	 */
	public function __toString()
	{
		return $this->name ?: '';
	}


	/**
	 *
	 */
	public function getReadme()
	{
		if ($this->readme) {
			return $this->readme;
		}

		$file = $this->root . '/README.md';

		if (file_exists($file)) {
			$this->readme = file_get_contents($file);
		}

		return $this->readme;
	}
}
