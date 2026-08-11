<?php

class SOYShopOrderConfirmBase implements SOY2PluginAction{

	/**
	 * @param int 0 or 1
	 * @return bool
	 */
	function hasError(int $param){
		return true;
	}

	/**
	 * @param string
	 * @return bool
	 */
	function checkError(string $param){
		return true;
	}
	
	
	/**
	 * @return string html
	 */
	function display(){
		return "";
	}
	
	/**
	 * エラーメッセージがある場合はこちら
	 * @param bool
	 * @return string html
	 **/
	function error(bool $isErr){
		return "";
	}
}
class SOYShopOrderConfirmDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $param;//$_POST["order_confirm_module"]
	private $error;	//チェック忘れがあるか？

	private $hasError = false;
	private $_html = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			//ページのdoPost内で
			case "checkError":
				if(isset($this->param[$moduleId])){
					if(is_numeric($this->param[$moduleId]) && $action->hasError($this->param[$moduleId])){
						$this->hasError = true;
					}else if(is_string($this->param[$moduleId]) && $action->checkError($this->param[$moduleId])){
						$this->hasError = true;	
					}
					
				}else{
					//do nothing
				}
				break;
			case "display":
				$this->_html[$moduleId] = array(
					"html" => $action->display(),
					"error" => $action->error($this->error)
				);
				break;
		}
	}
	
	function setError($error){
		$this->error = $error;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
	function setParam($param){
		$this->param = $param;
	}
	function getHtml(){
		return $this->_html;
	}
	function hasError(){
		return $this->hasError;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.confirm","SOYShopOrderConfirmDeletageAction");
