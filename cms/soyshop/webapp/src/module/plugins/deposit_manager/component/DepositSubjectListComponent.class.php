<?php

class DepositSubjectListComponent extends HTMLList{

	function populateItem($entity){
		$this->addLabel("subject", array(
			"text" => $entity->getSubject()
		));

		$this->addInput("display_order", array(
			"name" => "DisplayOrder[" . $entity->getId() . "]",
			"value" => (is_numeric($entity->getDisplayOrder()) && $entity->getDisplayOrder() > 0 && $entity->getDisplayOrder() < SOYShop_DepositManagerSubject::DISPLAY_ORDER_LIMIT) ? $entity->getDisplayOrder() : ""
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=deposit_manager&subject_id=" . $entity->getId()),
			"onclick" => "return confirm('削除しても宜しいですか？');"
		));
	}

}
