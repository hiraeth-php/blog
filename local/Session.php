<?php

namespace Hiraeth\App;

class Session
{
	/**
	 *
	 */
	public function __construct()
	{
		if (!session_id()) {
			session_start();
		}
	}


	/**
	 *
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}


	/**
	 *
	 */
	public function get($key, $default = NULL)
	{
		if (array_key_exists($key, $_SESSION)) {
			return $_SESSION[$key];
		}

		return $default;
	}
}
