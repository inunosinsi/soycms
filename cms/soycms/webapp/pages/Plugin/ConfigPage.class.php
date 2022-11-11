<?php

class ConfigPage extends CMSWebPageBase{

	function __construct() {
		$array = array_keys($_GET);
		$this->id = array_shift($array);
		$result = $this->run("Plugin.GetAction",array("pluginId"=>$this->id));

		if(!$result->success()){
			$this->jump("Plugin");
		}else{
			$plugin = $result->getAttribute("plugin");
		}

		parent::__construct();

		$this->addLabel("plugin_name", array(
			"text" => $plugin->getName()
		));

		$this->addLink("plugin_email", array(
			"text"=> (strlen($plugin->getMail())) ? $plugin->getMail() : "-",
			"link"=> "mailto:" . $plugin->getMail(),
			"target"=>"_blank"
		));

		$this->addLink("plugin_url", array(
			"text"=> (strlen($plugin->getUrl())) ? $plugin->getUrl() : "-",
			"link"=> $plugin->getUrl(),
			"target"=>"_blank"
		));

		$this->addLabel("plugin_author", array(
			"text"=> (strlen($plugin->getAuthor())) ? $plugin->getAuthor() : "-"
		));

		$this->addLabel("plugin_description", array(
			"html"=>$plugin->getDescription()
		));

		$this->addLabel("plugin_version", array(
			"text"=> (strlen($plugin->getVersion())) ? $plugin->getVersion() : "-"
		));

		$this->addForm("plugin_config_form", array(
			"action"=>SOY2PageController::createLink("Plugin.ConfigModifyPage")
		));

		$this->addInput("back_url", array(
			"name"=>"back_url",
			"value"=>SOY2PageController::createLink("Plugin.ConfigPage")."?".$this->id
		));

		$this->addInput("plugin_id", array(
			"name"=>"plugin_id",
			"value"=>$this->id
		));

		$this->addSelect("category_select", array(
			"options"=>array_keys($this->run("Plugin.PluginCategoryListAction")->getAttribute("list")),
			"selected"=>$plugin->getCategory(),
			"name"=>"category"
		));

		if($plugin->isActive()){
			DisplayPlugin::hide("only_nonactive");
		}else{
			DisplayPlugin::hide("only_active");
		}

		if($plugin->getConfig() && $plugin->isActive()){
			$configArray = $this->getFlashSession()->getAttribute("config_redirect");
			$html = call_user_func($plugin->getConfig(),$configArray);
		}else{
			$html = "";
		}
		$this->addLabel("plugin_config", array(
			"html" => $html
		));
	}
}
