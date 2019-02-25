<?php

class IndexPage extends CMSWebPageBase{

	function __construct(){

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
			"target" => "admin"
		));

		//更新が不要なら戻る
		if(!$logic->hasUpdate()) SOY2PageController::jump("");

		parent::__construct();

		$this->addActionLink("update_link", array(
			"link" => SOY2PageController::createLink("Upgrade.Admin.Complete")
		));
	}
}
