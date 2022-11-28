<?php

class RegisteredItemListComponent extends HTMLList{
	
	protected function populateItem($entity, $idx) {
		
		$this->addLabel("label", array(
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));
		
		$this->addLabel("attribute", array(
			"text" => (isset($entity["attribute"])) ? $entity["attribute"] : ""
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=member_special_price&index=" . $idx),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
?>