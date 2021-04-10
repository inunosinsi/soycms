<?php

class EditPageMessageListComponent extends HTMLList {

	protected function populateItem($entity){

		$v = (isset($entity["alert"]) && is_string($entity["alert"])) ? $entity["alert"] : null;

		$this->addLabel("message", array(
			"html" => (isset($entity["message"]) && strlen($entity["message"])) ? $entity["message"] : "",
			"attr:class" => self::_getAlertClass($v)
		));
	}

	private function _getAlertClass($alert){
		$cls = "alert alert-";
		return (strlen($alert)) ? $cls . $alert : $cls . "danger";

	}
}
