<?php

/**
 * データベースのバージョンを確認して、アップデートがあればUpgradeにリダイレクト
 */
class CheckAdminVersionAction extends SOY2Action{

	function execute($req,$form,$res) {
		
		//初期管理者のみ
		if( ! UserInfoUtil::isDefaultUser()){
			return SOY2Action::FAILED;
		}
		
		/*
		 * CMSの設定のバージョンチェック
		 */
		if($this->hasUpdateAdminConfig()){
			SOY2PageController::jump("Upgrade.CMS");
		}

		//アップデートの必要なし
		return SOY2Action::SUCCESS;
	}

	/**
	 * adminの設定のバージョンを確認してアップデートがあるかどうか
	 */
	function hasUpdateAdminConfig(){
		$logic = SOY2LogicContainer::get("logic.admin.Upgrade.UpdateAdminLogic");
		return $logic->hasUpdate();
	}
}
