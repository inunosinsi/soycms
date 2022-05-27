<?php

class SendAddressFormListComponent extends HTMLList{

	private $formConfig;
	private $requiredConfig;

	protected function populateItem($entity,$key){

		$this->addLabel("form_name", array(
			"text" => $entity
		));
		
		$this->addCheckBox("display_form", array(
			"selected" => (isset($this->formConfig[$key]) && is_bool($this->formConfig[$key])) ? $this->formConfig[$key] : true,
			"value" => 1,
			"name" => "Config[sendAddressDisplayFormConfig][" . $key . "]",
			"onclick" => ($key == "zipCode" || $key == "address") ? "return false" : null,
		));

		$this->addCheckBox("form_required", array(
			"selected" => (isset($this->requiredConfig[$key]) && is_bool($this->requiredConfig[$key])) ? $this->requiredConfig[$key] : true,
			"value" => 1,
			"name" => "Config[sendAddressInformationConfig][" . $key . "]",
			"onclick" => ($key == "zipCode" || $key == "address") ? "return false" : null,
		));
	}

	function setFormConfig($formConfig){
		$this->formConfig = $formConfig;
	}
	function setRequiredConfig($requiredConfig){
		$this->requiredConfig = $requiredConfig;
	}
}
