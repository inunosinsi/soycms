<?php

class SOYShopPageController extends SOY2PageController{

	function execute(){

		$session = SOY2ActionSession::getUserSession();

		/**
		 * appAuth	テンプレートの変更やページの追加を許可する
		 * appLimit	プラグインやCSVの使用を許可する。設定の変更も許可する
		 */

		//初期管理者の時は全操作を許可する
		if($session->getAttribute("isdefault")){
			$appAuth = true;
			$appLimit = true;
		//App権限を取得する
		}else{
			//一般管理者の場合、true
			$appAuth = ($session->getAttribute("app_shop_auth_level")==1) ? true : false;
			//一般管理者または受注管理者の場合、true
			if($appAuth){
				$appLimit = true;
			//管理制限者の場合、false
			}else{
				$appLimit = ($session->getAttribute("app_shop_auth_level")==2) ? true : false;
			}
		}

		$session->setAttribute("app_shop_auth_limit", $appLimit);

		$template = "main";

		//Pathを作成
		$pathBuilder = $this->getPathBuilder();
		$path = $pathBuilder->getPath();
		$args = $pathBuilder->getArguments();
		if(!strlen($path) || substr($path,strlen($path)-1,1) == "."){
			$path .= $this->getDefaultPath();
		}

		$this->requestPath = $path;
		$this->arguments = $args;
		$classPathBuilder = $this->getClassPathBuilder();
		$classPath = $classPathBuilder->getClassPath($path);

		$classPath .= 'Page';

		//管理制限者でプラグインの管理画面を開こうとしたとき、トップページにリダイレクト
		if($appLimit == false && strpos($classPath,"Plugin") !== false){
			SOY2PageController::jump("");
		}

		//管理制限者で設定画面を開こうとしたとき、トップページにリダイレクト
		if($appLimit == false && strpos($classPath,"Config") !== false){
			SOY2PageController::jump("");
		}


		if(strpos($classPath,"Site") !== false){
			//App権限を確認し、許可されていない場合はトップページにリダイレクト
			if($appAuth){
				$template = "site";
				$pageClass = "site";
			}else{
				SOY2PageController::jump("");
			}
		}else{
			$pageClass = "shop";
		}

		if(strpos($classPath,"Invoice") !== false){
			$template = "invoice";
		}

		//ポップアップ用のテンプレート
		if(
			strpos($classPath,"Storage") !== false ||
			strpos($classPath,"Arrival") !== false ||
			strpos($classPath,"Favorite") !== false ||
			strpos($classPath, "Order.Register.Item.SearchPage") === 0 ||
			(isset($_GET["display_mode"]) && $_GET["display_mode"] == "free")
		){
			$template = "free";
		}

		//一回だけIndexPageを試す。
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$path = $pathBuilder->getPath();
			$classPath = $classPathBuilder->getClassPath($path);

			if(!preg_match('/.+Page$/',$classPath)){
				$classPath .= '.IndexPage';
			}
		}

		//存在しない場合
		if(!SOY2HTMLFactory::pageExists($classPath)){
			$this->onNotFound();
		}

		//拡張ページ タブの表示　WebPageよりも前に実行していないと表示順が反映されない
		SOYShopPlugin::load("soyshop.admin.list");
		$extConts = SOYShopPlugin::invoke("soyshop.admin.list", array("mode" => "tab"))->getContents();
		if(is_null($extConts)) $extConts = array();

		$webPage = &SOY2HTMLFactory::createInstance($classPath, array(
			"arguments" => $args
		));

		try{
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$shopConfig = SOYShop_ShopConfig::load();
			$shopName = $shopConfig->getShopName();

			$subMenu = (method_exists($webPage,"getSubMenu")) ? $webPage->getSubMenu() : null;
			$layout = ($subMenu) ? "layout_right" : "layout_full";

			$activeTab = (strlen($classPathBuilder->getClassPath($path)) > 0)
			           ? strtolower(strtr($classPathBuilder->getClassPath($path), ".", "_"))
			           : "news" ;

			$showLogoutLink = $this->isDirectLogin();
			$isReview = SOYShopPluginUtil::checkIsActive("item_review");

			//SOY Appのリンク表示
			$inquiryUseSiteDb = SOYAppUtil::checkAppAuth("inquiry");
			$mailUseSiteDb = SOYAppUtil::checkAppAuth("mail");

			$createAppLink = SOYAppUtil::createAppLink();

			$title = (method_exists($webPage,"getTitle")) ? $webPage->getTitle() . " | SOY Shop" : "SOY Shop" . " | ".$shopName;
			$css= (method_exists($webPage,"getCSS")) ? $webPage->getCSS() : array();
			$scripts= (method_exists($webPage,"getScripts")) ? $webPage->getScripts() : array();

			if(method_exists($webPage,"isLayer") && $webPage->isLayer()){
				$template = "layer";
			}

			$isOrder = $shopConfig->getDisplayOrderAdminPage();
			$isItem = $shopConfig->getDisplayItemAdminPage();

			ob_start();
			$webPage->display();
			$html = ob_get_contents();
			ob_end_clean();

			include(SOY2::RootDir() . "layout/{$template}.php");

		}catch(Exception $e){


			ob_start();
			echo "<pre>";
			if($e instanceof SOY2DAOException)echo $e->getPDOErrorMessage();
			var_dump($e);
			echo "</pre>";
			$html = ob_get_contents();
			ob_end_clean();

			include(SOY2::RootDir() . "layout/main.php");

			exit;
		}
	}

    /**
     * SOY Shopしかログイン権限がない管理者かどうか
     */
    function isDirectLogin(){
     	$only_one = SOY2ActionSession::getUserSession()->getAttribute("hasOnlyOneRole");
     	return ($only_one == true);
    }
}
