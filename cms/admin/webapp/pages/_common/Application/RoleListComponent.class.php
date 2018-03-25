<?php

class RoleListComponent extends HTMLList{

	private $roles;
	private $application;

	protected function populateItem($entity, $key){

		$userId = $entity->getId();

		$this->addLabel("user_name", array(
			"text" => (strlen($entity->getName())) ? $entity->getName() . " (".$entity->getUserId().")" : $entity->getUserId()
		));


		if(isset($this->roles[$userId])){
			$role = $this->roles[$userId];
			$roleValeu = $role->getAppRole();
		}else{
			$roleValeu = 0;
		}

		$this->addSelect("role", array(
			"options" => AppRole::getRoleLists($this->application["useMultipleRole"]),
			"indexOrder" => true,
			"name" => "AppRole[".$userId."]",
			"selected" => $roleValeu,
			"visible" => !$entity->getIsDefaultUser()
		));
	}

	function getRoles() {
		return $this->roles;
	}
	function setRoles($roles) {
		$this->roles = $roles;
	}
	function getApplication() {
		return $this->application;
	}
	function setApplication($application) {
		$this->application = $application;
	}
}
