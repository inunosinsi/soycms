<?php

class PluginListComponent extends HTMLList{

	public function populateItem($plugin,$key,$counter){
		
		$this->addLabel("plugin_name", array(
			"text" => $plugin->getName(),
		));

		$this->addLink("config_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config") ."?".$plugin->getId()
		));

		$this->addImage("plugin_icon", array(
			"src"=>$plugin->getIcon()
		));

		$this->addModel("plugin_box", array(
			"style" => (($counter%2)==0) ? "background-color:#F4F9FE" : ""
		));
	}
}
