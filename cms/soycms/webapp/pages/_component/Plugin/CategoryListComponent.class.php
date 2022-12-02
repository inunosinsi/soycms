<?php

class CategoryListComponent extends HTMLList{

	private $labels;

	public function populateItem($arg,$key,$count){
		$targetId = "category-".$count;

		$this->addModel("category_panel_color", array(
			"attr:class" => ($count > 1) ? "panel panel-yellow" : "panel panel-green"	//アクティブなプラグイン以外すべてyellow
		));

		$this->addLink("category_name", array(
			"text" => (is_numeric($key) && (isset($this->labels[$key]))) ? $this->labels[$key] : "",
			"link" => "#".$targetId,
		));

		$this->addActionLink("plugin_category_delete_link", array(
			"link" => SOY2PageController::createLink("Plugin.DeleteCategory")."?category_name=".rawurldecode((string)$key),
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

	function setLabels($labels){
		$this->labels = $labels;
	}
}
