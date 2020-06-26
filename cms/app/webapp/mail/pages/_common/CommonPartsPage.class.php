<?php

class CommonPartsPage extends WebPage{

	function redirectCheck(){
		if(self::logic()->checkSOYShopConnect()) SOY2PageController::jump("mail.User");
	}

	function createTag($id=null){
		$checkSOYShop = self::logic()->checkSOYShopConnect();
		DisplayPlugin::toggle("is_connect", $checkSOYShop);
		DisplayPlugin::toggle("no_connect_btn", !$checkSOYShop);
		DisplayPlugin::toggle("no_connect", !$checkSOYShop);

		//新規の場合に表示しない項目がある
		$this->addModel("detail", array(
			"visible" => (is_numeric($id) && $id > 0)
		));
	}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		return $logic;
	}
}
