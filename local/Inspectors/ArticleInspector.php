<?php

namespace Hiraeth\App;

use Checkpoint;

/**
 *
 */
class ArticleInspector extends Checkpoint\Inspector
{
	/**
	 *
	 */
	protected function validate($article)
	{
		$this->check('title', $article->title, ['notBlank']);
		$this->check('datePublished', $article->datePublished, ['date'], TRUE);
	}
}
