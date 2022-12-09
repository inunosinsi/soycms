<?php
class SOYShopPointBase implements SOY2PluginAction{

	/**
	 * @param int userId
	 */
	function doPost(int $userId){}

	/**
	 * @param int userId
	 * @return int point
	 */
	function getPoint(int $userId){
		return 0;
	}

	/**
	 * @param int userId
	 * @return int timestamp
	 */
	function getTimeLimit(int $userId){
		return 0;
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
			$p = $action->getPoint($this->userId);
			if(is_numeric($p)) $this->_point = $p;
			$l = $action->getTimeLimit($this->userId);
			if(is_numeric($l)) $this->_limit = $l;
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
