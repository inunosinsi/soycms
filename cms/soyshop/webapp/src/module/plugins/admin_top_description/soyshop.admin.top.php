<?php
class AdminTopDescriptionAdminTop extends SOYShopAdminTopBase{

	function notice(){
		$html = array();
		$html[] = "新着ページでは、新着系のプラグインをインストールすることで表示したい内容を設定することができます。";
		$html[] = "早速、新着系のプラグインをインストールしてみましょう。プラグインの選択画面は<a href=\"" . SOY2PageController::createLink("Plugin.List") . "\">こちら</a>をクリックして下さい。";

		$html[] = "";

		$html[] = "画面上部のロゴ画像やアプリ名の表示の変更を行いたい場合は設定にある基本設定にあるアプリの設定で行うことができます。";
		$html[] = "アプリ名等の変更を行う場合は<a href=\"" . SOY2PageController::createLink("Config.ShopConfig") . "\">こちら</a>をクリックして下さい。";

		$html[] = "";
		$html[] = "この表示を消したい場合は<strong>管理画面の新着ページの説明プラグイン</strong>をアンインストールしましょう。";
		return implode("<br>", $html);
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "admin_top_description", "AdminTopDescriptionAdminTop");
