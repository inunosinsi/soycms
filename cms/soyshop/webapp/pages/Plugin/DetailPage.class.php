<?php
class DetailPage extends WebPage{

	var $id;
	private $module;

	function doPost(){

    	if(soy2_check_token()){

	    	$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
	    	$logic->prepare();
			if($this->module->getIsActive()) {
				$logic->uninstallModule($this->module->getId());
			}else{
				$logic->installModule($this->module->getId());
			}

    	}

		SOY2PageController::jump("Plugin.Detail." . $this->id . "?updated");

	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : "";
    	$dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

    	try{
    		$this->module = $dao->getById($this->id);
    	}catch(Exception $e){
    		SOY2PageController::jump("Plugin");
    	}

    	WebPage::WebPage();

    	$this->addForm("toggle_form");

    	$this->addInput("button", array(
    		"name" => "change_status",
    		"value" => ($this->module->getIsActive()) ? "アンインストール":"インストール" ,
    		"type" => "submit"
    	));

    	$this->addLabel("module_name_text", array(
			"text" => $this->module->getName()
		));
    	$this->addLabel("module_name", array(
			"text" => $this->module->getName()
		));

    	$this->addLabel("module_id", array(
			"text" => $this->module->getPluginId()
		));
		$this->addLabel("module_version", array(
			"text" => $this->module->getVersion()
		));
		$this->addLabel("module_status", array(
			"text" => (($this->module->isActive()) ? "インストール済み" : "未インストール")
		));
		$this->addLabel("module_description", array(
			"text" => $this->module->getDescription()
		));
		
		/** 詳細説明があるプラグインの場合は、説明のURLを記載する **/
		$this->addModel("display_module_detail_link", array(
			"visible" => (!is_null($this->module->getLink()) && strlen($this->module->getLink()) > 0)
		));
		$this->addLink("module_detail_link", array(
			"link" => $this->module->getLink(),
			"text" => (!is_null($this->module->getLabel()) && strlen($this->module->getLabel()) > 0) ? $this->module->getLabel() : $this->module->getLink()
		));
 		$html = $this->getPluginInfo($this->id);
    	$this->addLabel("plugin_info", array(
    		"html" => $html
    	));

    	$this->addModel("has_info", array(
    		"visible" => (strlen($html) > 0)
    	));
    }

    /**
     * @return html
     */
    function getPluginInfo($pluginId){
		SOYShopPlugin::load("soyshop.info", $this->module);

		return SOYShopPlugin::display("soyshop.info", array(
			"active" => $this->module->isActive()
		));
    }
}
?>