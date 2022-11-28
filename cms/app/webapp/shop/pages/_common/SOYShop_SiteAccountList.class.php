<?php

class SOYShop_SiteAccountList extends HTMLList{

	private $role;

	function populateItem($entity){

		$this->addLabel("user_id", array(
			"text" => $entity->userId
		));

		$this->addSelect("site_role", array(
			"name" => "Account[" . $entity->id . "]",
			"options" => $this->role,
			"indexOrder" => true,
			"selected" => $entity->site_role,
			"visible" => ($entity->isDefaultUser == 0),
			"disabled" => (is_null($entity->app_role))
		));
	}

	function setRole($role){
		$this->role = $role;
	}
}
