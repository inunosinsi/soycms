<?php

class ChildrenTablePage extends WebPage {

	private $children;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->createAdd("child_list", "_common.Order.ItemListComponent", array(
			"list" => $this->children,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));
	}

	function setChildren($children){
		$this->children = $children;
	}
}
