<?php
class AccessRestrictionAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=access_restriction");
	}

	function getLinkTitle(){
		return "ブラウザの登録";
	}

	function getTitle(){
		return "アクセス制限";
	}

	function getContent(){
		SOY2::import("module.plugins.access_restriction.util.AccessRestrictionUtil");
		if(AccessRestrictionUtil::checkBrowser()){
			return "<div class=\"alert alert-info\">現在アクセスしているブラウザはアクセス制限プラグインに登録されています。</div>";
		}else{
			return "<div class=\"alert alert-danger\">現在アクセスしているブラウザはアクセス制限プラグインに登録されていません。</div>";
		}
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "access_restriction", "AccessRestrictionAdminTop");
