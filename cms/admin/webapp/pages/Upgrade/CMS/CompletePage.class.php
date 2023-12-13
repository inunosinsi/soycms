<?php

class CompletePage extends CMSWebPageBase{

	function __construct(){

		if(soy2_check_token()){
			SOY2LogicContainer::get("logic.admin.Upgrade.UpdateAdminLogic", array(
				"target" => "admin"
			))->update();
			
			/**
			 * @データベースの変更後に何らかの操作が必要な場合
			 */
		}else{
			SOY2PageController::redirect("");
		}

		parent::__construct();
	}
}