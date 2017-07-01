<?php

namespace Hiraeth\App;


/**
 *
 */
class Articles extends AbstractRepository
{
	/**
	 *
	 */
	public function __construct(ArticleInspector $inspector)
	{
		$this->inspector = $inspector;
	}


	/**
	 *
	 */
	public function findLatest($limit = 10, $page = 1)
	{
		$path     = $this->app->getDirectory($this->getPath());
		$files    = array_reverse(static::scanDirectory($path . '/*'));
		$articles = array();

		foreach (array_slice($files, ($page - 1) * $limit, $limit) as $file) {
			$info       = pathinfo($file);
			$slug       = str_replace($path . '/', '', $info['dirname']) . '/' . $info['filename'];
			$articles[] = $this->find($slug);
		}

		return (object) [
			'page'  => $page,
			'pages' => ceil(count($files) / $limit),
			'data'  => $articles
		];
	}


	/**
	 *
	 */
	public function getEntityClass()
	{
		return 'Hiraeth\App\Article';
	}


	/**
	 *
	 */
	public function getSlug(AbstractEntity $article)
	{
		$name  = $this->system->getInflector()->hyphenate($article);
		$date  = strtotime($article->datePublished);
		$year  = date('Y', $date);
		$month = date('m', $date);
		$day   = date('d', $date);

		return sprintf('%s/%s/%s/%s', $year, $month, $day, $name);

	}

	/**
	 *
	 */
	protected function fill(AbstractEntity $article, array $data, $loading = FALSE)
	{
		parent::fill($article, $data, $loading);

		if (!$loading) {
			if (!$article->author) {
				$article->author = $this->system->getUser()->login;
			}

			if (!$article->datePublished) {
				$article->datePublished = date('Y-m-d');
			}
		}
	}
}
