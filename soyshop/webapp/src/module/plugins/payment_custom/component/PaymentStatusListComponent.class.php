<?php

class PaymentStatusListComponent extends HTMLList{

	private $status;

	protected function populateItem($entity, $key){

		$this->addCheckBox("status_radio", array(
			"name" => "payment_custom[status]",
			"value" => $key,
			"label" => $entity,
			"selected" => ($this->status == $key)
		));
	}
	function setStatus($status){
		$this->status = $status;
	}
}
