<?php

namespace Hiraeth\App;

use Hiraeth\Application;
use Plasticbrain\FlashMessages\FlashMessages as Messages;
use Psr\Http\Message\ServerRequestInterface as Request;
use ICanBoogie\Inflector;

class System
{
	/**
	 *
	 */
	static protected $responses = [
		301 => 'Moved Permanently',
		303 => 'See Other',
		403 => 'Forbidden',
		404 => 'Not Found'
	];


	/**
	 *
	 */
	protected $app = NULL;


	/**
	 *
	 */
	protected $secure = FALSE;


	/**
	 *
	 */
	public function __construct(Application $app, Session $session, Messages $messages, Inflector $inflector)
	{
		$this->app       = $app;
		$this->session   = $session;
		$this->messages  = $messages;
		$this->inflector = $inflector;
	}


	/**
	 *
	 */
	public function error($code)
	{
		header('HTTP/1.1 ' . $code . ' ' . static::$responses[$code]);
		exit(1);
	}


	/**
	 *
	 */
	public function getInflector()
	{
		return $this->inflector;
	}


	/**
	 *
	 */
	public function getMessages()
	{
		return $this->messages;
	}


	/**
	 *
	 */
	public function getUser()
	{
		return $this->session->get('user');
	}


	/**
	 *
	 */
	public function isSecure()
	{
		return $this->secure;
	}


	/**
	 *
	 */
	public function login(Users $users, Request $request, $redirect = NULL)
	{
		$referer = $request->getHeader('Referer');
		$login   = $request->getParsedBody()['login'];
		$pass    = $request->getParsedBody()['password'];
		$user    = $users->find('/' . $login);

		if (count($referer) && $referer[0] != (string) $request->getUri()->withPath('/login/')) {
			$this->session->set('postLoginUrl', $referer[0]);
		}

		if (!$user || !password_verify($pass, $user->password)) {
			$this->messages->error('Invalid login information provided, please try again.');
			$this->redirect('/login/');

		} else {
			$this->session->set('user', $user);
			$this->messages->success('You have successfuly logged in.');
			$this->redirect($redirect ?: $this->session->get('postLoginUrl', '/'));
		}
	}


	/**
	 *
	 */
	public function read($file)
	{
		return file_get_contents($this->app->getFile($file));
	}


	/**
	 *
	 */
	public function redirect($path, $type = 303)
	{
		header('HTTP/1.1 ' . $type . ' ' . static::$responses[$type]);
		header('Location: ' . $path);
		exit(0);
	}


	/**
	 *
	 */
	public function setup(Users $users, Request $request)
	{
		foreach ([
			'/articles/2017/06/26/from-zero-to-routing-in3-packages'
			=> '/articles/2017/06/26/from-zero-to-routing-in-3-packages'
		] as $path => $target) {
			if ($request->getUri()->getPath() == $path) {
				$this->redirect($target, 301);
			}
		}

		$path  = $request->getUri()->getPath();
		$query = $request->getUri()->getQuery();

		if (count($users)) {
			$this->secure = TRUE;
		}

		if (!$this->isSecure() && $path . '?' . $query != '/users/?action=add') {
			$this->redirect('/users/?action=add');
		}
	}
}
