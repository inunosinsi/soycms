<?php

class SlipNumberAdminList extends SOYShopAdminListBase{

    function getTabName(){
		if(self::isExistSlipNumberTable()) return "発送";
    }

    function getTitle(){
		if(self::isExistSlipNumberTable()) return "発送";
    }

    function getContent(){
		if(!self::isExistSlipNumberTable()) SOY2PageController::jump("");
		SOY2::import("module.plugins.slip_number.page.SlipNumberListPage");
		$form = SOY2HTMLFactory::createInstance("SlipNumberListPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
    }

	private function isExistSlipNumberTable(){
		static $isExist;
		if(is_null($isExist)){
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery("SELECT id FROM soyshop_slip_number LIMIT 1;");
				$isExist = (!is_null($res) && is_array($res));
			}catch(Exception $e){
				$isExist = false;
			}
		}

		return $isExist;
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "slip_number", "SlipNumberAdminList");
