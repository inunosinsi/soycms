<?php

class SOYShopPageController extends SOY2PageController{

	function execute(){

		/* init event */
        SOYShopPlugin::load("soyshop.admin.prepare");
        SOYShopPlugin::invoke("soyshop.admin.prepare");

		//管理権限の設定 SOYShopAuthUtil内で権限に関することをすべて対応してしまう
		SOY2::import("util.SOYShopAuthUtil");
		SOYShopAuthUtil::setAuthConstant();

		//キャッシュの削除
		if(isset($_GET["clear_cache"])) {
			SOYShopCacheUtil::clearCache();
			header("Location:" . $_SERVER["HTTP_REFERER"]);
			exit;
		}

		//SOY Appのリンク表示
		define("SHOW_LOGOUT_LINK", self::_isDirectLogin());
		define("USE_INQUIRY_SITE_DB", SOYAppUtil::checkAppAuth("inquiry"));
		define("USE_MAIL_SITE_DB", SOYAppUtil::checkAppAuth("mail"));

		//管理画面モード → カードIDがnoneであれば起動
		define("SOYSHOP_ADMIN_MODE", (soyshop_get_cart_id() == "none"));
		if(!SOYSHOP_ADMIN_MODE){
			define("SHOP_MANAGER_LABEL", "ショップ");
			define("SHOP_USER_LABEL", "顧客");
		}else{
			define("SHOP_MANAGER_LABEL", "アプリ");
			define("SHOP_USER_LABEL", "アカウント");
		}

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

		//該当するページを表示して良いか調べて、ダメであればリダイレクト
		SOYShopAuthUtil::checkAuthEachPage($classPath);

		if(strpos($classPath,"Site") !== false){
			$template = "site";
			$pageClass = "site";
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
			strpos($classPath,"Abstract") !== false ||
			strpos($classPath, "Order.Register.Item.SearchPage") === 0 ||
			strpos($classPath, "Order.Register.Item.PricePage") === 0 ||
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

		//ショップとサイトのどちらを開いているか？
		define("ADMIN_PAGE_TYPE", $pageClass);

		$shopConfig = SOYShop_ShopConfig::load();
		$shopName = $shopConfig->getShopName();
		$appName = trim(htmlspecialchars($shopConfig->getAppName(), ENT_QUOTES, "UTF-8"));
		$appLogoPath = trim(htmlspecialchars($shopConfig->getAppLogoPath(), ENT_QUOTES, "UTF-8"));
		$adminName = SOY2ActionSession::getUserSession()->getAttribute("username");

		//ぱんくず
		SOY2::import("component.Breadcrumb.BreadcrumbComponent");
		$breadcrumb = (method_exists($webPage, "getBreadcrumb")) ? $webPage->getBreadcrumb() : null;

		$subMenu = (method_exists($webPage,"getSubMenu")) ? $webPage->getSubMenu() : null;
		$footerMenu = (method_exists($webPage,"getFooterMenu")) ? $webPage->getFooterMenu() : null;
		$layout = ($subMenu) ? "layout_right" : "layout_full";

		$activeTab = (strlen($classPathBuilder->getClassPath($path)) > 0)
				   ? strtolower(strtr($classPathBuilder->getClassPath($path), ".", "_"))
				   : "news" ;

		define("SOYAPP_LINK", SOYAppUtil::createAppLink());

		$title = (method_exists($webPage,"getTitle")) ? $webPage->getTitle() . " | " . $appName : $appName . " | ".$shopName;
		if(!is_string($title)) $title = "";
		
		$css= (method_exists($webPage,"getCSS")) ? $webPage->getCSS() : array();
		$scripts= (method_exists($webPage,"getScripts")) ? $webPage->getScripts() : array();

		if(method_exists($webPage,"isLayer") && $webPage->isLayer()){
			$template = "layer";
		}

		//jsとcssのパス
		$paths = self::_paths();

		try{
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
    private function _isDirectLogin(){
     	return (SOY2ActionSession::getUserSession()->getAttribute("hasOnlyOneRole"));
    }

	private function _paths(){
		if(defined("SOYCMS_READ_LIBRARY_VIA_CDN") && SOYCMS_READ_LIBRARY_VIA_CDN){
			return array(
				"css" => array(
					"bootstrap" => "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css",
					"metis" => "https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.css",
					"sb-admin-2" => "https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/3.3.7/css/sb-admin-2.css",
					"morris" => "https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.2/morris.min.css",
					"fontawesome" => "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css",
					"jquery-ui" => "https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"
				),
				"js" => array(
					"jquery" => "https://code.jquery.com/jquery-3.6.0.min.js",
					"jquery-ui" => "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js",
					"bootstrap" => "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js",
					"metis" => "https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js",
					"raphael" => "https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.1/raphael-min.js",
					"morris" => "https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.2/morris.min.js",
					"sb-admin-2" => "https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/3.3.7/js/sb-admin-2.min.js",
					"jquery-cookie" => "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
				)
			);
		}else{
			$dir = rtrim(dirname(SOY2PageController::createRelativeLink("./")), "/") . "/soycms";
			return array(
				"css" => array(
					"bootstrap" => $dir . "/webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css?" . SOYSHOP_BUILD_TIME,
					"metis" => $dir . "/webapp/pages/files/vendor/metisMenu/metisMenu.min.css?" . SOYSHOP_BUILD_TIME,
					"sb-admin-2" => $dir . "/webapp/pages/files/dist/css/sb-admin-2.css?" . SOYSHOP_BUILD_TIME,
					"morris" => $dir . "/webapp/pages/files/vendor/morrisjs/morris.css?" . SOYSHOP_BUILD_TIME,
					"fontawesome" => $dir . "/webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css?" . SOYSHOP_BUILD_TIME,
					"jquery-ui" => $dir . "/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.css?" . SOYSHOP_BUILD_TIME
				),
				"js" => array(
					"jquery" => $dir . "/webapp/pages/files/vendor/jquery/jquery.min.js?" . SOYSHOP_BUILD_TIME,
					"jquery-ui" => $dir . "/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js?" . SOYSHOP_BUILD_TIME,
					"bootstrap" => $dir . "/webapp/pages/files/vendor/bootstrap/js/bootstrap.min.js?" . SOYSHOP_BUILD_TIME,
					"metis" => $dir . "/webapp/pages/files/vendor/metisMenu/metisMenu.min.js?" . SOYSHOP_BUILD_TIME,
					"raphael" => $dir . "/webapp/pages/files/vendor/raphael/raphael.min.js?" . SOYSHOP_BUILD_TIME,
					"morris" =>	$dir . "/webapp/pages/files/vendor/morrisjs/morris.min.js?" . SOYSHOP_BUILD_TIME,
					"sb-admin-2" => $dir . "/webapp/pages/files/dist/js/sb-admin-2.min.js?" . SOYSHOP_BUILD_TIME,
					"jquery-cookie" => $dir . "/webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js?" . SOYSHOP_BUILD_TIME
				)
			);
		}
	}
}
