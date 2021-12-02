<?php

class CategoryListComponent extends HTMLList{

	public function populateItem($arg,$key,$count){
		if(!is_string($key)) $key = "";
		$targetId = "category-".$key;

		$this->addLink("category_name", array(
			"text"=>$key,
			"link" => "#".$targetId,
		));

		$this->addActionLink("plugin_category_delete_link", array(
			"link" => SOY2PageController::createLink("Plugin.DeleteCategory")."?category_name=".rawurldecode($key),
			"visible" => !in_array($key, array(
				CMSMessageManager::get("SOYCMS_NO_CATEGORY"),
				CMSMessageManager::get("SOYCMS_ACTIVE_PLUGINS"),
				CMSMessageManager::get("SOYCMS_NOT_ACTIVE_PLUGINS"),
			))
		));

		$this->createAdd("plugin_list","_component.Plugin.PluginListComponent",array(
			"list" => $arg
		));

		$this->addModel("has_plugin", array(
				"visible" => (count($arg)),
				"attr:id" => $targetId,
		));
		$this->addModel("no_plugin", array(
				"visible" => !(count($arg))
		));
	}
}
