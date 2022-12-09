<?php

class OutPutHistoryListComponent extends HTMLList{
	
	protected function populateItem($entity) {
		
		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i:s", $entity->getCreateDate())
		));
		
		$this->addLabel("output_date", array(
			"text" => date("Y-m-d", $entity->getOutputDate())
		));
	}
}

?>