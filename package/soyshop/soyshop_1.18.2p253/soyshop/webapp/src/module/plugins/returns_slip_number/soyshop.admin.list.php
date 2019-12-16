<?php

class ReturnsSlipNumberAdminList extends SOYShopAdminListBase{

    function getTabName(){
		if(self::isExistSlipNumberTable()) return "返送";
    }

    function getTitle(){
		if(self::isExistSlipNumberTable()) return "返送";
    }

    function getContent(){
		if(!self::isExistSlipNumberTable()) SOY2PageController::jump("");
		SOY2::import("module.plugins.returns_slip_number.page.ReturnsSlipNumberListPage");
		$form = SOY2HTMLFactory::createInstance("ReturnsSlipNumberListPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
    }

	private function isExistSlipNumberTable(){
		static $isExist;
		if(is_null($isExist)){
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery("SELECT id FROM soyshop_returns_slip_number LIMIT 1;");
				$isExist = (!is_null($res) && is_array($res));
			}catch(Exception $e){
				$isExist = false;
			}
		}

		return $isExist;
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "returns_slip_number", "ReturnsSlipNumberAdminList");
