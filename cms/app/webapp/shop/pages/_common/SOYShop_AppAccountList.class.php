<?php

class SOYShop_AppAccountList extends HTMLList{

	private $role;

	function populateItem($entity){

		$this->addLabel("user_id", array(
			"text" => $entity->userId
		));

		$this->addModel("is_default_user", array(
			"visible" => ($entity->isDefaultUser != 0)
		));

		$this->addSelect("app_role", array(
			"name" => "Account[" . $entity->id . "]",
			"options" => $this->role,
			"indexOrder" => true,
			"selected" => $entity->app_role,
			"visible" => ($entity->isDefaultUser == 0)
		));
	}

	function setRole($role){
		$this->role = $role;
	}
}
