<?php
class DetailPage extends WebPage{

	private $id;

	function doPost(){

    	if(soy2_check_token()){

	    	$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
	    	$logic->prepare();

			$module = soyshop_get_plugin_object($this->id);
			if($module->getIsActive()) {
				$logic->uninstallModule($module->getId());
			}else{
				$logic->installModule($module->getId());
			}

    	}

		SOY2PageController::jump("Plugin.Detail." . $this->id . "?updated");

	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : "";
    	$module = soyshop_get_plugin_object($this->id);
		if(is_null($module->getId())) SOY2PageController::jump("Plugin");

    	parent::__construct();

    	$this->addForm("toggle_form");

    	$this->addInput("button", array(
    		"name" => "change_status",
    		"value" => ($module->getIsActive()) ? "アンインストール":"インストール" ,
    		"type" => "submit"
    	));

    	$this->addLabel("module_name_text", array(
			"text" => $module->getName()
		));
    	$this->addLabel("module_name", array(
			"text" => $module->getName()
		));

    	$this->addLabel("module_id", array(
			"text" => $module->getPluginId()
		));
		$this->addLabel("module_version", array(
			"text" => $module->getVersion()
		));
		$this->addLabel("module_status", array(
			"text" => (($module->isActive()) ? "インストール済み" : "未インストール")
		));
		$this->addLabel("module_description", array(
			"text" => $module->getDescription()
		));

		/** 詳細説明があるプラグインの場合は、説明のURLを記載する **/
		DisplayPlugin::toggle("display_module_detail_link", (!is_null($module->getLink()) && strlen($module->getLink()) > 0));
		$this->addLink("module_detail_link", array(
			"link" => $module->getLink(),
			"text" => (!is_null($module->getLabel()) && strlen($module->getLabel()) > 0) ? $module->getLabel() : $module->getLink()
		));
 		$html = $this->getPluginInfo($this->id);
    	$this->addLabel("plugin_info", array(
    		"html" => $html
    	));

		DisplayPlugin::toggle("has_info", (strlen($html) > 0));
    }

    /**
     * @return html
     */
	 function getPluginInfo($pluginId){
	 	$module = soyshop_get_plugin_object($pluginId);
		SOYShopPlugin::load("soyshop.info", $module);
		return SOYShopPlugin::display("soyshop.info", array(
			"active" => $module->isActive()
		));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("プラグイン詳細", array("Plugin" => "プラグイン管理"));
	}
}
