<?php

class SOYShopMailConfig implements SOY2PluginAction{

	private $type;
	private $target;
	private $order;

	/**
	 * @return Array("active", "header", "content", "footer")
	 */
	function getConfig(){

	}

	/**
	 * @param void
	 * @return void
	 */
	function doPost(){

	}

	/**
	 * @return html
	 */
	function buildEditForm(){

	}

	function getTarget(){
		return $this->target;
	}
	function setTarget($target){
		$this->target = $target;
	}

	function getType(){
		return $this->type;
	}
	function setType($type){
		$this->type = $type;
	}
}

class SOYShopMailConfigDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "send";	//sendとeditがある
	private $target = "user";	//userやadminとか
	private $type = "order";	//orderやdeliveryとか

	private $_config;	//active、header、content、footerの配列で返す
	private $_html;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		$action->setTarget($this->target);
		$action->setType($this->type);

		switch($this->mode){
			case "send":
				$this->_config = $action->getConfig();
				break;
			case "edit":
				$this->_html = $action->buildEditForm();
				break;
			case "update":
				$action->doPost();
				break;
		}
	}

	function getConfig(){
		return $this->_config;
	}
	function getHtml(){
		return $this->_html;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setTarget($target){
		$this->target = $target;
	}
	function setType($type){
		$this->type = $type;
	}
}
SOYShopPlugin::registerExtension("soyshop.mail.config", "SOYShopMailConfigDeletageAction");
