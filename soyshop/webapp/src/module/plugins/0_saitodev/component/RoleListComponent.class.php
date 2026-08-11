<?php

class RoleListComponent extends HTMLList {

	private $roles;	//非表示のものだけ配列に入っている

	protected function populateItem($entity, $key){

		$this->addLabel("label", array(
			"text" => (isset($entity) && is_string($entity)) ? $entity : ""
		));

		$this->addSelect("role", array(
			"name" => "Role[" . $key . "]",
			"options" => array("on" => "表示する", "off" => "表示しない"),
			"selected" => self::_checkRole($key)
		));

		if(!is_numeric($key) || $key == 0) return false;
	}

	private function _checkRole($role){
		if(!count($this->roles)) return "on";
		if(!is_numeric($role) || $role == 0) return "on";

		return (is_numeric(array_search($role, $this->roles))) ? "off" : "on";
	}

	function setRoles($roles){
		$this->roles = $roles;
	}
}
