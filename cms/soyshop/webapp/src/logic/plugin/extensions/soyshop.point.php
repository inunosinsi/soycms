<?php
class SOYShopPointBase implements SOY2PluginAction{
	
	function doPost($userId){
		
	}
	
	function getPoint($userId){
		
	}
	
	function getTimeLimit($userId){
		
	}
}

class SOYShopPointDeletageAction implements SOY2PluginDelegateAction{

	private $_point;
	private $_limit;
	private $userId;
	private $mode;	//将来の拡張で使うかもしれない

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost($this->userId);
		}else{
			$this->_point = $action->getPoint($this->userId);
			$this->_limit = $action->getTimeLimit($this->userId);
		}
	}
	
	function getPoint(){
		return $this->_point;
	}
	function getTimeLimit(){
		return $this->_limit;
	}
	
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
}
SOYShopPlugin::registerExtension("soyshop.point", "SOYShopPointDeletageAction");
?>