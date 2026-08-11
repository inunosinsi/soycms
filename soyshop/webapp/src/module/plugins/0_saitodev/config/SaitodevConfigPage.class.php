<?php

class SaitodevConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.0_saitodev.util.SaitodevUtil");
		SOY2::import("module.plugins.0_saitodev.component.RoleListComponent");
	}

	function doPost(){

		if(soy2_check_token()){

			//非表示にするものだけ記録しておく
			$conf = array();
			foreach($_POST["Role"] as $role => $v){
				if($v == "off"){
					$conf[] = $role;
				}
			}

			SaitodevUtil::saveConfig($conf);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("role_list", "RoleListComponent", array(
			"list" => self::_getRoles(),
			"roles" => SaitodevUtil::getConfig()
		));
	}

	private function _getRoles(){
		$old = SOYAppUtil::switchAdminDsn();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId(SOYSHOP_ID);
		}catch(Exception $e){
			$site = new Site();
		}
		SOYAppUtil::resetAdminDsn($old);

		$old = SOYAppUtil::switchAppMode("shop");
		$roles = SOY2Logic::createInstance("logic.RoleLogic")->getSiteRoleArray($site);
		SOYAppUtil::resetAppMode($old);
		return $roles;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
