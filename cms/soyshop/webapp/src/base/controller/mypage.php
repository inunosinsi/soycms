<?php
/**
 * 運営者の代理購入用のログイン
 */
function purchase_proxy_login(){
	if(isset($_GET["purchase"]) && $_GET["purchase"] == "proxy" && isset($_GET["user_id"]) && is_numeric($_GET["user_id"])){
		//管理画面にログインしているか調べる
		$session = SOY2ActionSession::getUserSession();
		if(!is_null($session->getAttribute("loginid"))){
			$mypage = MyPageLogic::getMyPage();
			$mypage->noPasswordLogin(trim($_GET["user_id"]));

			//GETパラメータにrの値がある場合はリダイレクト
			if(isset($_GET["r"]) && strlen($_GET["r"])){
				$param = soyshop_remove_get_value(htmlspecialchars($_GET["r"], ENT_QUOTES, "UTF-8"));
				soyshop_redirect_designated_page($param, "login=complete");
				exit;
			}
		}
	}
}

/**
 * ダウンロード販売
 */
function execute_download_action($pluginId){
	$downloadModule = soyshop_get_plugin_object($pluginId);
	if(!is_null($downloadModule->getId())){
		SOYShopPlugin::load("soyshop.download",$downloadModule);
		SOYShopPlugin::invoke("soyshop.download");
	}
}

/**
 * マイページ実行
 */
function execute_mypage_application($args){
	SOY2::import("base.site.pages.SOYShop_UserPage");
	$webPage = SOY2HTMLFactory::createInstance("SOYShop_UserPage", array(
		"arguments" => array(SOYSHOP_CURRENT_MYPAGE_ID, $args)
	));

	SOY2HTMLPlugin::addPlugin("src","SrcPlugin");
	SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");

	SOYShopPlugin::load("soyshop.site.onload");
	SOYShopPlugin::invoke("soyshop.site.onload", array("page" => $webPage));

	$webPage->common_execute();

	SOYShopPlugin::load("soyshop.site.beforeoutput");
	SOYShopPlugin::invoke("soyshop.site.beforeoutput", array("page" => $webPage));

	ob_start();
	$webPage->display();
	$html = ob_get_contents();
	ob_end_clean();

	SOYShopPlugin::load("soyshop.site.user.onoutput");
	$delegate = SOYShopPlugin::invoke("soyshop.site.user.onoutput", array("html" => $html));
	$html = $delegate->getHtml();

	echo $html;
}
