<?php

class OrderStatusBulkChangeListComponent extends HTMLList{

	function populateItem($entity, $key){

		$this->addLink("status_change_button", array(
			"link" => "javascript:void(0);",
			"text" => (isset($entity) && is_string($entity)) ? $entity : "",
			"onclick" => "$('#do_change_order_status_btn').val(" . $key . ").trigger('click');",
		));
	}
}
