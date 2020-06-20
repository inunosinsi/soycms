<?php
/**
 * ユーザ側 Controllerクラス
 */
class SOYShopSiteController extends SOY2PageController{

    private $timer = array();
    private $startTime;

    function execute(){
        $this->countTimer("Start");

        SOY2::import("logic.cart.CartLogic");
        SOY2::import("logic.mypage.MyPageLogic");

        $dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

        /* init event */
        SOYShopPlugin::load("soyshop.site.prepare");
        SOYShopPlugin::invoke("soyshop.site.prepare");

        /*
         * ページのURIとパラメータを取得する
         */
        list($uri, $args) = $this->getUriAndArgs();

        //カートかマイページを開いているか調べる
        self::checkDisplayApplicationPage($uri);

        //カート・マイページ関連の定数
        if(!defined("SOYSHOP_CURRENT_CART_ID")) define("SOYSHOP_CURRENT_CART_ID", soyshop_get_cart_id());
        if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());

        //言語設定がされていない場合はここで日本語に設定する
        if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

		//運営者の代理販売のためのログイン
        self::purchaseProxy();

        /*
         * カート、マイページ
         * notificationやdownloadでのexitを含む
         */
        //カートページ、もしくはマイページを開いた場合
        if(SOYSHOP_APPLICATION_MODE){
            if( self::doApplication($uri, $args) ){
                //正常に実行されればここで処理を完了する
                return;
            }
        }

        //https → http
        self::checkSSL($uri, $args);

        try{
            //URIからページを取得
            try{
                $page = $dao->getByUri($uri);
            }catch(Exception $e){
                //ルートでページャ対策
                try{
                    $page = $dao->getByUri("_home");
                }catch(Exception $e){
                    //ページが存在しない場合
                    $page = $dao->getByUri(SOYSHOP_404_PAGE_MARKER);
                }

            }

            //ページIDを放り込んでおく
            define("SOYSHOP_PAGE_ID", $page->getId());

            //404
            if(SOYSHOP_404_PAGE_MARKER == $page->getUri()){
                SOYShopPlugin::load("soyshop.site.404notfound");
                SOYShopPlugin::invoke("soyshop.site.404notfound");
                header("HTTP/1.0 404 Not Found");
            }

            /*
             * 出力
             * soyshop.site.onload
             * soyshop.site.beforeoutput
             * soyshop.site.onoutput
             */
            $this->outputPage($uri, $args, $page);

        }catch(Exception $e){
            $this->onError($e);
        }

    }

    /**
     * 予期しないエラーが発生した場合
     */
    function onError(Exception $e){
        header("HTTP/1.0 500 Internal Server Error");
        echo "<h1>500 Internal Server Error</h1>";
        if(DEBUG_MODE){
            echo "<pre>";
            var_dump($e);
            echo "</pre>";
        }
    }

    /**
     * @return
     */
    function &getPathBuilder(){
        static $builder;

        if(!$builder){
            $builder = new SOYShopPathInfoBuilder();
        }

        return $builder;
    }

    /**
     * PATH_INFOをページのURIとパラメータに分離する
     * @return Array(String, Array)
     */
    private function getUriAndArgs(){
        /*
         * パスからURIと引数に変換
         * 対応するページが存在すれば$uriに値が入る
         */
        $pathBuilder = $this->getPathBuilder();
        $uri  = $pathBuilder->getPath();
        $args = $pathBuilder->getArguments();

        /*
         * 対応するページがない場合
         */
        if( empty($uri) ){
            if(empty($args)){
                /*
                 * ルート直下へのアクセスはトップページ
                 */
                $uri = SOYSHOP_TOP_PAGE_MARKER;
            }elseif( is_array($args) && count($args) && strlen($args[0]) && 0 === strpos($args[0], "index.") ){
                /*
                 * http://domain.com/index.*へのアクセスはトップページへリダイレクトする
                 */
                array_shift($args);
                $args = implode($args,"/");
                SOY2PageController::redirect(soyshop_get_site_url(true) . $args);
            }
        }else{
            /*
             * http://domain.com/_homeへのアクセスはトップページへリダイレクトする
             */
            if(SOYSHOP_TOP_PAGE_MARKER == $uri){
                $args = implode($args,"/");
                SOY2PageController::redirect(soyshop_get_site_url(true) . $args);
            }

            /**
             * スマホサイト、もしくは多言語サイトの時は、$uriの末尾にアプリケーションのURIを追加して、$argsの値を一つずらす
             *    これを行わないと、カートに商品を放り込めないし、マイページは開けない
             */
            if(
                (defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE) ||
                (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE) ||
                (defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp")
            ){
                //argsに多言語のプレフィックスが入った場合は、$uriに多言語化のプレフィックスを付与して、$argsの値を前にずらす
                if(defined("SOYSHOP_PUBLISH_LANGUAGE") && (isset($args[0]) && $args[0] == SOYSHOP_PUBLISH_LANGUAGE)){
                    $uri .= "/" . $args[0];
                    array_shift($args);
                }

                $pcCartUri = SOYShop_DataSets::get("config.cart.cart_url", "cart");
                $pcMyPageUri = SOYShop_DataSets::get("config.mypage.url", "user");
                if(isset($args[0]) && ($args[0] == $pcCartUri || $args[0] == $pcMyPageUri)){
                    $uri .= "/" . trim($args[0]);
                    for($i = 0; $i < count($args); $i++){
                        $args[$i] = (isset($args[$i + 1])) ? trim($args[$i + 1]) : null;
                        if(is_null($args[$i])) unset($args[$i]);
                    }
                }
            }
        }

        return array($uri, $args);
    }

    private function checkDisplayApplicationPage($uri){
        $isApp = false;
        $isCart = false;
        $isMypage = false;

        //多言語サイトプラグインをアクティブにしていないもしくはスマホページか日本語ページの時
        //もしくは携帯リダイレクトプラグインと多言語化サイトを同時に実行している場合
        if(
            (!defined("SOYSHOP_PUBLISH_LANGUAGE") || SOYSHOP_PUBLISH_LANGUAGE == "jp") ||
            (defined("SOYSHOP_PUBLISH_LANGUAGE") && (defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE))
        ){
            if($uri == soyshop_get_cart_uri()){
                $isApp = true;
                $isCart = true;
            }else if($uri == soyshop_get_mypage_uri()){
                $isApp = true;
                $isMypage = true;
            }

        //多言語サイトプラグインをアクティブにしていて、多言語サイトを見ている時
        }else if(defined("SOYSHOP_PUBLISH_LANGUAGE")){
            SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			if(class_exists("UtilMultiLanguageUtil")){
				$config = UtilMultiLanguageUtil::getConfig();
				if(isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])){
					$cartUri = SOYShop_DataSets::get("config.cart.cart_url", "cart");
					$mypageUri = SOYShop_DataSets::get("config.mypage.url", "user");
					if($uri == $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $cartUri){
						$isApp = true;
						$isCart = true;
					}elseif($uri == $config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"] . "/" . $mypageUri){
						$isApp = true;
						$isMypage = true;
					}
				}
			}
        }

        define("SOYSHOP_APPLICATION_MODE", $isApp);
        define("SOYSHOP_CART_MODE", $isCart);
        define("SOYSHOP_MYPAGE_MODE", $isMypage);
    }

    /**
     * ページ出力
     * @param String $uri
     * @param Array $args
     * @param WebPage $page
     */
    private function outputPage($uri, $args, $page){

        $this->countTimer("Search");

        $webPage = $page->getWebPageObject($args);
        $webPage->setArguments($args);

        /* Event OnLoad */
        SOYShopPlugin::load("soyshop.site.onload");
        SOYShopPlugin::invoke("soyshop.site.onload", array("page" => $webPage));

        $webPage->build($args);
        $this->countTimer("Build");

        $webPage->main($args);
        $webPage->common_execute();

        $this->countTimer("Main");
        $this->appendDebugInfo($webPage);

        /* Event BeforeOutput */
        SOYShopPlugin::load("soyshop.site.beforeoutput");
        SOYShopPlugin::invoke("soyshop.site.beforeoutput", array("page" => $webPage));

        ob_start();
        $webPage->display();
        $html = ob_get_contents();
        ob_end_clean();

        $this->countTimer("Render");
        $this->replaceRenderTime($html);

        /* EVENT onOutput */
        SOYShopPlugin::load("soyshop.site.onoutput");
        $delegate = SOYShopPlugin::invoke("soyshop.site.onoutput", array("html" => $html, "page" => $webPage));
        $html = $delegate->getHtml();

        echo $html;

    }

    /**
     * カートやマイページの処理を行う
     * @param String $uri
     * @param Array $args
     * @return Boolean
     */
    private function doApplication($uri, $args){

        //カート マイページ 共通化
        SOY2::import("component.backward.BackwardUserComponent");
        SOY2::import("component.UserComponent");

        //カートの多言語化
        SOY2::import("message.MessageManager");

        //カート
        if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){

            MessageManager::addMessagePath("cart");

            //notify event
            if(isset($_GET["soyshop_notification"])){
                self::executeNotificationAction($_GET["soyshop_notification"]);
                exit;
            }

			//block event
			if(isset($_GET["soyshop_ban"])){
				self::executeBanAction($_GET["soyshop_ban"]);
                exit;
			}

            self::executeCartApplication($args);
            return true;
        }

        //マイページ
        if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE){

            MessageManager::addMessagePath("mypage");

            //download_event
            if(isset($_GET["soyshop_download"])){
                self::executeDownloadAction($_GET["soyshop_download"]);
                exit;
            }

            self::executeUserApplication($args);
            return true;
        }
    }

    /**
     * カート実行
     */
    private function executeCartApplication($args){

        $webPage = SOY2HTMLFactory::createInstance("SOYShop_CartPage", array(
            "arguments" => array(SOYSHOP_CURRENT_CART_ID)
        ));

        if(count($args) > 0 && $args[0] == "operation"){
            $webPage->doOperation();
            exit;
        }else{

            SOY2HTMLPlugin::addPlugin("src", "SrcPlugin");
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
    }

    /**
     * 通知イベント(決済など)
     * @param string $pluginId $_GET["soyshop_notification"]
     */
    private function executeNotificationAction($pluginId){
		$paymentModule = soyshop_get_plugin_object($pluginId);
		if(!is_null($paymentModule->getId())){
			SOYShopPlugin::load("soyshop.notification", $paymentModule);
            SOYShopPlugin::invoke("soyshop.notification");
		}
    }

	/**
	 * カートの禁止イベント
	 * @param string $pluginId $_GET["soyshop_ban"]
	 */
	private function executeBanAction($pluginId){
		SOY2Logic::createInstance("logic.cart.CartLogic")->banIPAddress($pluginId);
		return "OK";
	}

    /**
     * マイページ実行
     */
    private function executeUserApplication($args){

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

    /**
     * ダウンロード販売
     */
    private function executeDownloadAction($pluginId){
		$downloadModule = soyshop_get_plugin_object($pluginId);
		if(!is_null($downloadModule->getId())){
			SOYShopPlugin::load("soyshop.download",$downloadModule);
            SOYShopPlugin::invoke("soyshop.download");
		}
    }

    /**
     * SSLチェック　httpsのリダイレクト設定に従って振り分け
     * @param String $uri
     * @param Array $args
     */
    private function checkSSL($uri, $args){
        switch(SOYShop_ShopConfig::load()->getSSLConfig()){
            case SOYShop_ShopConfig::SSL_CONFIG_HTTPS:
                self::redirectToSSLURL($uri, $args);
                break;
            //ログインチェック後にhttpsに飛ばす
            case SOYShop_ShopConfig::SSL_CONFIG_LOGIN:
                //ログイン
                if(MyPageLogic::getMyPage()->getIsLoggedin()){
                    self::redirectToSSLURL($uri, $args);
                //ログアウト
                }else{
                    self::redirectToNonSSLURL($uri, $args);
                }
                break;
            default:
            case SOYShop_ShopConfig::SSL_CONFIG_HTTP:
                self::redirectToNonSSLURL($uri, $args);
        }
    }

    /**
     * SSLチェック
     * ショップのURLがSSLを使う設定のときにhttpでアクセスされた場合はhttpsにリダイレクトする
     * @param String $uri
     * @param Array $args
     */
    private function redirectToSSLURL($uri, $args){
        if(!isset($_SERVER["HTTPS"])){
            if($uri != SOYSHOP_TOP_PAGE_MARKER) array_unshift($args, $uri);
            $args = implode($args,"/");
            $sslUrl = soyshop_get_ssl_site_url();
            SOY2PageController::redirect($sslUrl . $args, true);
            exit;
        }
    }

    /**
     * SSLチェック
     * ショップのURLがSSLを使わない設定のときにhttpsでアクセスされた場合はhttpにリダイレクトする
     * @param String $uri
     * @param Array $args
     */
    private function redirectToNonSSLURL($uri, $args){
        if(isset($_SERVER["HTTPS"])){
            if($uri != SOYSHOP_TOP_PAGE_MARKER) array_unshift($args, $uri);
            $args = implode($args,"/");
            SOY2PageController::redirect(soyshop_get_site_url(true) . $args, true);
            exit;
        }
    }

    /**
     * 運営者の代理購入用のログイン
     */
    private function purchaseProxy(){
		if(isset($_GET["purchase"]) && $_GET["purchase"] == "proxy" && isset($_GET["user_id"]) && is_numeric($_GET["user_id"])){
            //管理画面にログインしているか調べる
            $session = SOY2ActionSession::getUserSession();
            if(!is_null($session->getAttribute("loginid"))){
                $mypage = MyPageLogic::getMyPage();
                $mypage->noPasswordLogin(trim($_GET["user_id"]));
            }
        }
    }

    /**
     * タイマーに記録する（デバッグモードのみ）
     * @param String $label
     */
    private function countTimer($label){
        if(DEBUG_MODE){
            $this->timer[$label] = microtime(true);
            if(!$this->startTime){
                $this->startTime = $this->timer[$label];
            }
        }
    }

    /**
     * デバッグ情報をHTMLの末尾に付け足す（デバッグモードのみ）
     * @param WebPage $webPage
     */
    private function appendDebugInfo($webPage){
        if(DEBUG_MODE){
            $debugInfo = "";

            $previous = null;
            foreach($this->timer as $label => $time){
                if(!$previous){
                    $previous = $time;
                    continue;
                }
                $debugInfo .= "<p>".$label.": " . ($time - $previous) . " 秒</p>";
                $previous = $time;
            }
            $debugInfo .= "<p><b>Total: " . ($previous - $this->startTime) . " 秒</b></p>";
            $debugInfo .= "<p>Render: ##########RENDER_TIME######### 秒</p>";

            $ele = $webPage->getBodyElement();
            $ele->appendHTML($debugInfo);
        }
    }

    /**
     * レンダリング時間を置換する
     * @param String $html (リファレンス渡し)
     */
    private function replaceRenderTime(&$html){
        if(DEBUG_MODE){
            $html = str_replace("##########RENDER_TIME#########", $this->timer["Render"] - $this->timer["Main"], $html);
        }
    }
}
