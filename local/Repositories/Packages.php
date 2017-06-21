<?php

namespace Hiraeth\App;

use Hiraeth;

/**
 *
 */
class Packages
{
	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app)
	{
		$this->app  = $app;
		$this->root = $app->getDirectory('vendor/hiraeth');
	}


	/**
	 *
	 */
	public function create()
	{
		$class = $this->getEntityClass();

		return new $class();
	}


	/**
	 *
	 */
	public function getEntityClass()
	{
		return 'Hiraeth\App\Package';
	}


	/**
	 *
	 */
	public function getSlug(Package $package)
	{
		return $package->name;
	}


	/**
	 *
	 */
	public function find($slug)
	{
		if ($slug == 'hiraeth/core') {
			return NULL;
		}

		$root = realpath(dirname($this->root) . '/' . $slug);
		$file = $root . '/composer.json';

		if (file_exists($file)) {
			$data    = json_decode(file_get_contents($file));
			$package = $this->create();

			$this->fill($package, (array) $data, TRUE);

			$package->root = $root;

			return $package;
		}

		return NULL;
	}


	/**
	 *
	 */
	public function findAll()
	{
		$entities = array();

		foreach (glob($this->root . '/*', GLOB_ONLYDIR) as $package_directory) {
			$package = $this->find(str_replace(dirname($this->root) . '/', '', $package_directory));

			if ($package) {
				$entities[] = $package;
			}
		}

		return (object) [
			'page'  => 1,
			'pages' => 1,
			'data'  => $entities
		];
	}


	/**
	 *
	 */
	protected function fill(Package $package, array $data, $loading = FALSE)
	{
		foreach ($data as $key => $value) {
			$package->$key = $value;
		}
	}
}
