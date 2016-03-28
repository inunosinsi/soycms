<?php
class UtilMobileCheckPluginConfigFormPage extends WebPage{
	private $pluginObj;

	function UtilMobileCheckPluginConfigFormPage(){
	}

	function doPost(){

    	if(soy2_check_token()){
			if(isset($_POST["config"])){
				if(isset($_POST["config"]["smartPrefix"]))$this->pluginObj->setSmartPrefix($_POST["config"]["smartPrefix"]);
				if(isset($_POST["config"]["prefix"]))$this->pluginObj->setPrefix($_POST["config"]["prefix"]);
				if(isset($_POST["config"]["redirect"]))$this->pluginObj->setRedirect($_POST["config"]["redirect"]);
				if(isset($_POST["config"]["message"]))$this->pluginObj->setMessage($_POST["config"]["message"]);
				if(isset($_POST["config"]["redirectIphone"]))$this->pluginObj->setRedirectIphone($_POST["config"]["redirectIphone"]);
				if(isset($_POST["config"]["redirectIpad"]))$this->pluginObj->setRedirectIpad($_POST["config"]["redirectIpad"]);
				CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			}
			CMSPlugin::redirectConfigPage();
    	}

	}

	function execute(){

		WebPage::WebPage();

		$this->addForm("form");

		$this->addInput("smart_prefix", array(
			"name" => "config[smartPrefix]",
			"value" => $this->pluginObj->smartPrefix,
		));

		$this->addInput("prefix", array(
			"name" => "config[prefix]",
			"value" => $this->pluginObj->prefix,
		));

		$this->addCheckBox("auto_redirect", array(
			"name" => "config[redirect]",
			"value" => 1,
			"elementId" => "auto_redirect",
			"selected" => $this->pluginObj->redirect,
			"isBoolean" => true
		));

		$this->addTextArea("message", array(
			"name" => "config[message]",
			"value" => $this->pluginObj->message,
		));

		$this->addCheckBox("do_not_redirect_ipad", array(
			"name" => "config[redirectIpad]",
			"value" => UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_PC,
			"elementId" => "do_not_redirect_ipad",
			"selected" => $this->pluginObj->redirectIpad == UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_PC
		));

		$this->addCheckBox("redirect_ipad_smartphone", array(
			"name" => "config[redirectIpad]",
			"value" => UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_SP,
			"elementId" => "redirect_ipad_smartphone",
			"selected" => $this->pluginObj->redirectIpad == UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_SP
		));

		$this->addCheckBox("do_not_redirect", array(
			"name" => "config[redirectIphone]",
			"value" => UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_PC,
			"elementId" => "do_not_redirect",
			"selected" => $this->pluginObj->redirectIphone == UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_PC
		));

		$this->addCheckBox("redirect_smartphone", array(
			"name" => "config[redirectIphone]",
			"value" => UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_SP,
			"elementId" => "redirect_smartphone",
			"selected" => $this->pluginObj->redirectIphone == UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_SP
		));

		$this->addCheckBox("redirect_mobile", array(
			"name" => "config[redirectIphone]",
			"value" => UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_MB,
			"elementId" => "redirect_mobile",
			"selected" => $this->pluginObj->redirectIphone == UtilMobileCheckPlugin::CONFIG_SP_REDIRECT_MB
		));

	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

}
?>
