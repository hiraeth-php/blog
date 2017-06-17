<?php

namespace Hiraeth\App;

/**
 *
 */
class Article extends AbstractEntity
{
	/**
	 *
	 */
	public $author = NULL;


	/**
	 *
	 */
	public $datePublished = NULL;


	/**
	 *
	 */
	public $summary = NULL;


	/**
	 *
	 */
	public $title = NULL;


	/**
	 *
	 */
	public function __toString()
	{
		return $this->title ?:'';
	}
}
