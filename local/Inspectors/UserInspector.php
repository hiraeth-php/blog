<?php

namespace Hiraeth\App;

use Checkpoint;

/**
 *
 */
class UserInspector extends Checkpoint\Inspector
{
	/**
	 *
	 */
	protected function validate($user)
	{
		$this->check('login', $user->login, ['notBlank']);
		$this->check('fullName', $user->fullName, ['notBlank']);
	}
}
