<?php

class EditPageMessageListComponent extends HTMLList {

	protected function populateItem($entity){

		$v = (isset($entity["alert"]) && is_string($entity["alert"])) ? $entity["alert"] : "";
		$msg = (isset($entity["message"]) && is_string($entity["message"])) ? $entity["message"] : "";

		$this->addLabel("message", array(
			"html" => (strlen($msg)) ? $msg : "",
			"attr:class" => self::_getAlertClass($v)
		));
	}

	private function _getAlertClass(string $alert){
		$cls = "alert alert-";
		return (strlen($alert)) ? $cls . $alert : $cls . "danger";

	}
}
