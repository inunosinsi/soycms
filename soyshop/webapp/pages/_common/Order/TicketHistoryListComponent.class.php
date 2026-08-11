<?php


class TicketHistoryListComponent extends HTMLList{

	private $label;

	protected function populateItem($entity, $key) {

		$this->addLabel("ticket_label", array(
			"text" => $this->label
		));

		$this->addLabel("ticket_history_value", array(
			"text" => $entity->getContent()
		));
	}

	function setLabel($label){
		$this->label = $label;
	}
}
