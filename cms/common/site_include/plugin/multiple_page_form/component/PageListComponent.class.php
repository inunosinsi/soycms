<?php

class PageListComponent extends HTMLList {

	protected function populateItem($entity, $hash){
		$this->addLabel("page_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("page_type", array(
			"text" => (isset($entity["type"])) ? MultiplePageFormUtil::getTypeText($entity["type"]) : ""
		));

		$this->addInput("display_order", array(
			"name" => "Display[" . $hash . "]",
			"value" => (isset($entity["order"]) && is_numeric($entity["order"])) ? $entity["order"] : 0,
			"style" => "width:80px;"
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?multiple_page_form&detail=" . $hash)
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?multiple_page_form&remove=" . $hash),
			"onclick" => "return confirm('削除してもよろしいでしょうか？');"
		));
	}
}
