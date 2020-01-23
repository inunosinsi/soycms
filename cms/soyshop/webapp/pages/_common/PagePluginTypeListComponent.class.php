<?php

class PagePluginTypeListComponent extends HTMLList{

	protected function populateItem($entity, $key, $count){

		$this->addLabel("prefix", array(
			"text" => ($key != "jp") ? "(" . $key . ")" : ""
		));

		$link = SOY2PageController::createLink("Site.Pages.Create");
		$link .= ($key != "jp") ? "?uri=" . $key : "";
		$this->addLink("create_link", array(
			"link" => $link
		));

		$this->createAdd("page_list", "_common.PageListComponent", array(
			"list" => $entity
		));
	}
}
