<?php

class CustomerFormListComponent extends HTMLList{

	private $formConfig;
	private $customerConfig;
	private $adminConfig;

	protected function populateItem($entity,$key){

		$this->addLabel("form_name", array(
			"text" => $entity
		));
		
		$this->addCheckBox("display_form", array(
			"selected" => ($key !== "mailAddress" && isset($this->formConfig[$key]) && is_bool($this->formConfig[$key])) ? $this->formConfig[$key] : true,
			"value" => 1,
			"onclick" => ($key == "mailAddress") ? "return false" : null,
			"name" => "Config[CustomerDisplayFormConfig][" . $key . "]"
		));

		$this->addCheckBox("form_required", array(
			"selected" => ($key !== "mailAddress" && isset($this->customerConfig[$key]) && is_bool($this->customerConfig[$key])) ? $this->customerConfig[$key] : true,
			"value" => 1,
			"onclick" => ($key == "mailAddress") ? "return false" : null,
			"name" => "Config[CustomerInformationConfig][" . $key . "]"
		));

		$this->addCheckBox("admin_form", array(
			"selected" => ($key !== "mailAddress" && isset($this->adminConfig[$key]) && is_bool($this->adminConfig[$key])) ? $this->adminConfig[$key] : true,
			"value" => 1,
			"onclick" => ($key == "mailAddress") ? "return false" : null,
			"name" => "Config[CustomerAdminConfig][" . $key . "]"
		));
	}

	function setFormConfig($formConfig){
		$this->formConfig = $formConfig;
	}
	function setCustomerConfig($customerConfig){
		$this->customerConfig = $customerConfig;
	}
	function setAdminConfig($adminConfig){
		$this->adminConfig = $adminConfig;
	}
}
