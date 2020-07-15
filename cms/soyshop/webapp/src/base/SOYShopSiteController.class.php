<?php
/**
 * ユーザ側 Controllerクラス
 */
class SOYShopSiteController extends SOY2PageController{

    private $timer = array();
    private $startTime;

    function execute(){
		include_once("controller/debug.php");

		count_timer("Start");

        SOY2::import("logic.cart.CartLogic");
        SOY2::import("logic.mypage.MyPageLogic");

        /* init event */
        SOYShopPlugin::load("soyshop.site.prepare");
        SOYShopPlugin::invoke("soyshop.site.prepare");

        /*
         * ページのURIとパラメータを取得する
         */
		include_once("controller/func.php");
        list($uri, $args) = get_uri_and_args();

		include_once("controller/define.php");

        //カートかマイページを開いているか調べる→アプリケーションページに関する定数を定義する
        define_application_page_constant($uri);

		//全ページで使用するもの
		define_all_page_constant();

        /*
         * カート、マイページ
         * notificationやdownloadでのexitを含む
         */
        //カートページ、もしくはマイページを開いた場合
        if(SOYSHOP_APPLICATION_MODE){
			include_once("controller/app.php");
            if( do_application($uri, $args) ){
                //正常に実行されればここで処理を完了する
                return;
            }
        }

        //https → http
		include_once("controller/ssl.php");
        check_ssl($uri, $args);

        try{
            //URIからページを取得
			$page = get_page_object_on_controller($uri);

            //ページIDを放り込んでおく
            define("SOYSHOP_PAGE_ID", $page->getId());

            //404
            if(SOYSHOP_404_PAGE_MARKER == $page->getUri()){
                SOYShopPlugin::load("soyshop.site.404notfound");
                SOYShopPlugin::invoke("soyshop.site.404notfound");
                header("HTTP/1.0 404 Not Found");
            }

			//ページ種別によって読み込むページクラスを変える
			include_page_class($page->getType());

            /*
             * 出力
             * soyshop.site.onload
             * soyshop.site.beforeoutput
             * soyshop.site.onoutput
             */
			include_once("controller/output.php");
            output_page($uri, $args, $page);

        }catch(Exception $e){
			header("HTTP/1.0 500 Internal Server Error");
	        echo "<h1>500 Internal Server Error</h1>";
	        if(DEBUG_MODE){
	            echo "<pre>";
	            var_dump($e);
	            echo "</pre>";
	        }
        }
    }
}
