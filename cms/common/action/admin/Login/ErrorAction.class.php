<?php

/**
 * 不正ログインがないか
 */
class ErrorAction extends SOY2Action{

	function execute($req,$form,$res) {

		//不正ログインを試みられた場合
		if(SOY2Logic::createInstance("logic.admin.Login.ErrorLogic")->hasErrorLogin()){
			SOY2PageController::jump("Site.Login.Notice");
		}

		//アップデートの必要なし
		return SOY2Action::SUCCESS;
	}
}
