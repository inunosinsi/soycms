<?php

class CustomerFormListComponent extends HTMLList{
	
	private $formConfig;
	private $customerConfig;
	
	protected function populateItem($entity,$key){
		
		$this->addLabel("form_name", array(
			"text" => $entity
		));
		
		$this->addCheckBox("display_form", array(
			"selected" => ($key !== "mailAddress") ? $this->formConfig[$key] : true,
			"value" => 1,
			"onclick" => ($key == "mailAddress") ? "return false" : null,
			"name" => "Config[CustomerDisplayFormConfig][" . $key . "]"
		));
		
		$this->addCheckBox("form_required", array(
			"selected" => ($key !== "mailAddress") ? $this->customerConfig[$key] : true,
			"value" => 1,
			"onclick" => ($key == "mailAddress") ? "return false" : null,
			"name" => "Config[CustomerInformationConfig][" . $key . "]"
		));
	}
	
	function setFormConfig($formConfig){
		$this->formConfig = $formConfig;
	}
	function setCustomerConfig($customerConfig){
		$this->customerConfig = $customerConfig;
	}
}
?>