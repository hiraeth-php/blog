<?php

namespace Hiraeth\App;

/**
 *
 */
class User extends AbstractEntity
{
	/**
	 *
	 */
	public $fullName = NULL;


	/**
	 *
	 */
	public $login = NULL;


	/**
	 *
	 */
	public $nickName = NULL;


	/**
	 *
	 */
	public $permissions = [];


	/**
	 *
	 */
	public $roles = ['Anonymous'];


	/**
	 *
	 */
	public function __toString()
	{
		return $this->login ?: '';
	}


	/**
	 *
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 *
	 */
	public function getRoles()
	{
		return $this->roles;
	}
}
