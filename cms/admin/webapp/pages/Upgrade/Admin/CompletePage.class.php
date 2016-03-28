<?php

class CompletePage extends CMSWebPageBase{

	function CompletePage(){

		if(soy2_check_token()){
			$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
				"target" => "admin"
			));

			$logic->update();

			/**
			 * @データベースの変更後に何らかの操作が必要な場合
			 */
		}else{
			SOY2PageController::redirect("");
		}

		WebPage::WebPage();
	}
}
?>