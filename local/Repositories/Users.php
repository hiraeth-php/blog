<?php

namespace Hiraeth\App;


/**
 *
 */
class Users extends AbstractRepository
{
	/**
	 *
	 */
	public function __construct(UserInspector $inspector)
	{
		$this->inspector = $inspector;
	}


	/**
	 *
	 */
	public function getEntityClass()
	{
		return 'Hiraeth\App\User';
	}


	/**
	 *
	 */
	public function getSlug(AbstractEntity $user)
	{
		return $this->system->getInflector()->hyphenate(preg_replace('#[^a-zA-Z0-9]#', '', ucwords($user)));
	}


	/**
	 *
	 */
	protected function fill(AbstractEntity $user, array $data, $loading = FALSE)
	{
		if (!$loading) {
			$old_password = $user->password;
			$new_password = $data['password'];

			if (($old_password && $new_password) || !$old_password) {
				$data['password'] = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
			}
		}

		if (!isset($data['roles'])) {
			$data['roles'] = array();
		}

		parent::fill($user, $data, $loading);
	}
}
