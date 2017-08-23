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

		$this->createAdd("plugin_name","HTMLLabel",array(
			"text" => $plugin->getName()
		));

		$this->createAdd("plugin_email","HTMLLink",array(
			"text"=> (strlen($plugin->getMail())) ? $plugin->getMail() : "-",
			"link"=> "mailto:" . $plugin->getMail(),
			"target"=>"_blank"
		));

		$this->createAdd("plugin_url","HTMLLink",array(
			"text"=> (strlen($plugin->getUrl())) ? $plugin->getUrl() : "-",
			"link"=> $plugin->getUrl(),
			"target"=>"_blank"
		));

		$this->createAdd("plugin_author","HTMLLabel",array(
			"text"=> (strlen($plugin->getAuthor())) ? $plugin->getAuthor() : "-"
		));

		$this->createAdd("plugin_description","HTMLLabel",array(
			"html"=>$plugin->getDescription()
		));

		$this->createAdd("plugin_version","HTMLLabel",array(
			"text"=> (strlen($plugin->getVersion())) ? $plugin->getVersion() : "-"
		));

		$this->createAdd("plugin_config_form","HTMLForm",array(
			"action"=>SOY2PageController::createLink("Plugin.ConfigModifyPage")
		));

		$this->createAdd("back_url","HTMLInput",array(
			"name"=>"back_url",
			"value"=>SOY2PageController::createLink("Plugin.ConfigPage")."?".$this->id
		));

		$this->createAdd("plugin_id","HTMLInput",array(
			"name"=>"plugin_id",
			"value"=>$this->id
		));

		$this->createAdd("category_select","HTMLSelect",array(
			"options"=>array_keys($this->run("Plugin.PluginCategoryListAction")->getAttribute("list")),
			"selected"=>$plugin->getCategory(),
			"name"=>"category"
		));


		if($plugin->isActive()){
			DisplayPlugin::hide("only_nonactive");
		}else{
			DisplayPlugin::hide("only_active");
		}

		$configArray = $this->getFlashSession()->getAttribute("config_redirect");

		if($plugin->getConfig() && $plugin->isActive()){
			$html = call_user_func($plugin->getConfig(),$configArray);
		}else{
			$html = "";
		}
		$this->createAdd("plugin_config","HTMLLabel",array(
			"html" => $html
		));
	}
}
