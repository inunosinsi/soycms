<?php

class CategoryLinkListComponent extends HTMLList{

	public function populateItem($arg,$key){
		if(!is_string($key)) $key = "";

		$this->addLink("plugin_category_link", array(
			"text"=>$key,
			"link"=>SOY2PageController::createLink("Plugin")."?category=".rawurldecode($key)
		));
	}
}
