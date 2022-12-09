<?php
class SOYShopUploadImageBase implements SOY2PluginAction{

	private $mode;
	private $item;
	private $user;

	function convertFileName($pathinfo){
		return "";
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	function getItem(){
		return $this->item;
	}
	function setItem($item){
		$this->item = $item;
	}

	function getUser(){
		return $this->user;
	}
	function setUser($user){
		$this->user = $user;
	}
}
class SOYShopUploadImageDeletageAction implements SOY2PluginDelegateAction{

	private $_name;
	private $mode = "item";
	private $item;
	private $user;
	private $pathinfo;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
    	if($action instanceof SOYShopUploadImageBase){
			//モードは何に使用するか決めてない
			$action->setMode($this->mode);
			if(is_null($this->item)){
				SOY2::import("domain.shop.SOYShop_Item");
				$this->item = new SOYShop_Item();
			}
			$action->setItem($this->item);

			if(is_null($this->user)){
				SOY2::import("domain.user.SOYShop_User");
				$this->user = new SOYShop_User();
			}
			$action->setUser($this->user);

			$this->_name = $action->convertFileName($this->pathinfo);
		}
	}

	function getName(){
		return $this->_name;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
	function setItem($item){
		$this->item = $item;
	}
	function setUser($user){
		$this->user = $user;
	}

	function setPathinfo($pathinfo){
    	$this->pathinfo = $pathinfo;
	}
}
SOYShopPlugin::registerExtension("soyshop.upload.image","SOYShopUploadImageDeletageAction");
