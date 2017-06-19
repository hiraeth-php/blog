<?php

namespace Hiraeth\App;

use Psr\Http\Message\ServerRequestInterface as Request;
use Hiraeth\Application;
use Dotink\Jin\Parser;
use Checkpoint;
use Countable;


/**
 *
 */
abstract class AbstractRepository implements RepositoryInterface, Countable
{
	/**
	 *
	 */
	protected $app = NULL;


	/**
	 *
	 */
	protected $inspector = NULL;


	/**
	 *
	 */
	protected $parser = NULL;


	/**
	 *
	 */
	protected $system = NULL;


	/**
	 *
	 */
	abstract public function getSlug(AbstractEntity $entity);


	/**
	 *
	 */
	abstract public function getEntityClass();


	/**
	 *
	 */
	static protected function scanDirectory($pattern)
	{
		$files = array();

		foreach (glob($pattern) as $path) {
			if (is_file($path)) {
				$files[] = $path;
			} elseif (is_dir($path)) {
				$files = array_merge($files, static::scanDirectory($path . '/*'));
			}
		}

		return $files;
	}


	/**
	 *
	 */
	public function add(AbstractEntity $entity, Request $request)
	{
		try {
			$this->fill($entity, $request->getParsedBody());

			if ($this->find($this->getSlug($entity))) {
				throw new Checkpoint\ValidationException(sprintf(
					'The %s "%s" already exists.',
					$this->getEntityName(),
					$entity
				));
			}

			if ($this->getInspector()) {
				$this->getInspector()->run($entity, TRUE);
			}

			$this->store($entity);

			$this->system->getMessages()->success(sprintf(
				'The %s "%s" has been successfully created.',
				$this->getEntityName(),
				$entity
			));

			return TRUE;

		} catch (Checkpoint\ValidationException $e) {
			$this->system->getMessages()->error($e->getMessage());
		}

		return FALSE;
	}


	/**
	 *
	 */
	public function count()
	{
		$path = $this->app->getDirectory($this->getPath());
		return count(static::scanDirectory($path . '/*'));
	}


	/**
	 *
	 */
	public function create()
	{
		$entity_class = $this->getEntityClass();

		return new $entity_class();
	}


	/**
	 *
	 */
	public function delete(AbstractEntity $entity)
	{
		$path = $this->getPath($entity);
		$file = $this->app->getFile($path);

		if (unlink($file)) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 *
	 */
	public function edit(AbstractEntity $entity, Request $request)
	{
		$old_article = clone $entity;

		try {
			$this->fill($entity, $request->getParsedBody());

			if ($this->getInspector()) {
				$this->getInspector()->run($entity, TRUE);
			}

			$this->delete($old_article);
			$this->store($entity);

			$this->system->getMessages()->success(sprintf(
				'The %s "%s" has been successfully updated.',
				$this->getEntityName(),
				$entity
			));

			return TRUE;

		} catch (Checkpoint\ValidationException $e) {
			$this->system->getMessages()->error($e->getMessage());
		}

		return FALSE;
	}


	/**
	 *
	 */
	public function find($slug)
	{
		$entity_file = $this->getPath() . '/' . $slug . '.jin';

		if (!$this->app->hasFile($entity_file)) {
			return NULL;
		}

		$data    = file_get_contents($this->app->getFile($entity_file));
		$entity = $this->create();
		$parts   = explode(PHP_EOL . '----' . PHP_EOL, $data, 2);

		$this->fill($entity, $this->parser->parse($parts[0])->get() + ['body' => $parts[1]], TRUE);

		return $entity;
	}


	/**
	 *
	 */
	public function findAll()
	{
		$path     = $this->app->getDirectory($this->getPath());
		$files    = static::scanDirectory($path . '/*');
		$entities = array();

		foreach ($files as $file) {
			$info       = pathinfo($file);
			$slug       = str_replace($path . '/', '', $info['dirname']) . '/' . $info['filename'];
			$articles[] = $this->find($slug);
		}

		return (object) [
			'page'  => $page,
			'pages' => ceil(count($files) / $limit),
			'data'  => $entities
		];
	}


	/**
	 *
	 */
	public function getEntityName()
	{
		$parts = explode('\\', $this->getEntityClass());
		$name  = array_pop($parts);

		return strtolower($this->system->getInflector()->humanize($name));
	}


	/**
	 *
	 */
	public function getInspector()
	{
		return $this->inspector;
	}


	/**
	 *
	 */
	public function setApplication(Application $app)
	{
		$this->app = $app;
	}


	/**
	 *
	 */
	public function setParser(Parser $parser)
	{
		$this->parser = $parser;
	}


	/**
	 *
	 */
	public function setSystem(System $system)
	{
		$this->system = $system;
	}


	/**
	 *
	 */
	protected function getPath(AbstractEntity $entity = NULL)
	{
		$inflector = $this->system->getInflector();
		$writable  = 'writable/' . $inflector->hyphenate($inflector->pluralize($this->getEntityClass()));

		if ($entity) {
			$writable .= '/' . $this->getSlug($entity) . '.jin';
		}

		return $writable;
	}


	/**
	 *
	 */
	protected function fill(AbstractEntity $entity, array $data, $loading = FALSE)
	{
		foreach ($data as $key => $value) {
			$entity->$key = $value;
		}
	}


	/**
	 *
	 */
	protected function store(AbstractEntity $entity)
	{
		$path = $this->getPath($entity);
		$file = $this->app->getFile($path, TRUE);
		$data = NULL;
		$body = NULL;

		foreach ($entity as $property => $value) {
			if ($property == 'body') {
				$body = $value;
				continue;
			}

			if (!is_string($value)) {
				$value = json_encode($value);
			}

			$data .= sprintf('%s = %s' . PHP_EOL, $property, $value);
		}

		$data .= '----' . PHP_EOL . $body;

		file_put_contents($file, $data);
	}

}
