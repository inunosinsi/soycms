<?php

class PointHistoryListComponent extends HTMLList{
	
	protected function populateItem($entity, $key) {
		
		$this->addLabel("point_history_value", array(
			"text" => $entity->getContent()
		));
	}
}
?>