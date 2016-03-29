<?php

class CommonPartsPage extends WebPage{
	
	private $extendLogic;
	
	function redirectCheck(){
		if(!$this->extendLogic) $this->extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		if($this->extendLogic->checkSOYShopConnect() === true) SOY2PageController::jump("mail.User");
	}
	
	function createTag(){
		if(!$this->extendLogic) $this->extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");

		$checkSOYShop = $this->extendLogic->checkSOYShopConnect();

		$this->addModel("is_connect", array(
			"visible" => ($checkSOYShop === true)
		));
		
		$this->addModel("no_connect", array(
			"visible" => ($checkSOYShop === false)
		));
	}
	
}

?>