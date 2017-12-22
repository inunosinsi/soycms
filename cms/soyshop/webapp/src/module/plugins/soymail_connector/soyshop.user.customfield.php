<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYMailUserCustomfieldModule extends SOYShopUserCustomfield{

	function register($app, $userId){

		//マイページのみ
		if(get_class($app) !== "MyPageLogic") return;

		//ポイントプラグインがインストールされていなければここで停止
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_point_base")) return;

		$user = $app->getUserInfo();

		//顧客IDがある場合は編集ページで今回は該当しない
		if(!is_null($user->getId())) return;

		//メールマガジン登録していない時も処理をやめる
		SOY2::import("domain.user.SOYShop_User");
		if($user->getNotSend() == SOYShop_User::USER_NOT_SEND) return;

		SOY2::import("module.plugins.soymail_connector.util.SOYMailConnectorUtil");
		$config = SOYMailConnectorUtil::getConfig();

		if(isset($config["first_order_add_point"]) && (int)$config["first_order_add_point"] > 0){
			$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
			$logic->insert((int)$config["first_order_add_point"], "メールマガジン会員登録プレゼント：" . $config["first_order_add_point"] . "ポイント", $userId);
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "soymail_connector", "SOYMailUserCustomfieldModule");
