<?php
class OrderTableIndexAdminTop extends SOYShopAdminTopBase{

	function notice(){
		$wording = "データベースの最適化を行っています。最適化は自動で実行され、終了するとこの通知も自動で非表示になります。<br>";
		$wording .= "最適化についての詳しい記載は<a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=order_table_index") . "\">こちら</a>をご覧ください。";
		return $wording;
	}

	function allowDisplay(){
		return AUTH_OPERATE;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "order_table_index", "OrderTableIndexAdminTop");
