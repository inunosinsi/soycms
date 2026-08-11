<?php

class ScheduleListComponent extends HTMLList{

	function populateItem($entity, $key, $index){

		$this->addLabel("start_date", array(
			"text" => (is_numeric($entity->getStartDate())) ? date("Y-m-d", $entity->getStartDate()) : ""
		));

		$this->addLabel("tax_rate", array(
			"text" => $entity->getTaxRate() . "%"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=common_consumption_tax&id=" . $entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}
