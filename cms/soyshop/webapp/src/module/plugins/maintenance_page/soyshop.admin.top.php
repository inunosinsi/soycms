<?php

class MaintenancePageAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return (AUTH_CONFIG) ? SOY2PageController::createLink("Config.Detail?plugin=maintenance_page") : "";
	}

	function getLinkTitle(){
		return (AUTH_CONFIG) ? "設定" : "";
	}

	function getTitle(){
		return "メンテナンスページ";
	}

	function getContent(){
		SOY2::import("module.plugins.maintenance_page.util.MaintenancePageUtil");
		if(MaintenancePageUtil::checkActive()){
			return "<div class=\"alert alert-info\">【実行中】メンテナンスページを表示中<br>メンテナンスページの表示はシークレットモードで確認できます。</div>";
		}else{
			return "<div class=\"alert alert-warning\">【停止中】メンテナンスページは表示されていません</div>";
		}
	}

	function allowDisplay(){
		return AUTH_SITE;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "maintenance_page", "MaintenancePageAdminTop");
