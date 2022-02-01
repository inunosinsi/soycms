<?php
class SOYShopOrderMailReplace implements SOY2PluginAction{

	/**
	 * @return array(
	 * 	array(string replaceKey => string replaceLabel)
	 * )
	 */
	function strings(){
		return array();
	}

	/**
	 * @param SOYShop_Order, string
	 * @return string
	 */
	function replace(SOYShop_Order $order, string $content){
		return "";
	}
}
class SOYShopOrderMailReplaceDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $order;
	private $content;
	private $_strings = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "strings":
				$this->_strings[$moduleId] = $action->strings();
				break;
			case "replace":
				$this->content = $action->replace($this->order, (string)$this->content);
				break;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getContent(){
		return $this->content;
	}
	function getStrings(){
		return $this->_strings;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.mail.replace","SOYShopOrderMailReplaceDeletageAction");
