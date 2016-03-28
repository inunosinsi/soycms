<?php

class IndexPage extends CMSWebPageBase{
	
	function IndexPage(){
		
		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		/*
		 * アップグレード対象のサイトだけ抽出
		 */
		$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
			"target" => "admin"
		));
		
		if(!$logic->hasUpdate()) SOY2PageController::jump("");
		
		WebPage::WebPage();
		
		$this->addActionLink("update_link", array(
			"link" => SOY2PageController::createLink("Upgrade.Admin.Complete")
		));
	}
}
?>