<?php

class CommonNoticeArrivalBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $uri;

    function beforeOutput(WebPage $page){

        //これらの条件を満たさないと処理は開始しない
        if(isset($_GET["notice"]) && isset($_GET["notice"]) && isset($_GET["a"]) && soy2_check_token()){

			$uri = $page->getPageObject()->getUri();	//リダイレクト時に使用

            $item = soyshop_get_item_object((int)$_GET["notice"]);
			$url = soyshop_get_page_url($uri) . "/" . $item->getAlias();

            //現時点で在庫切れ商品であるかを確認する
            if($item->getStock() > 0) self::_redirect($url);

            //ログインしているかを調べる
            $loggedInUserId = MyPageLogic::getMyPage()->getUserId();	//ログインしていれば1以上の整数
            if($loggedInUserId === 0) self::_jumpLoginPage($item->getId());

			$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			$noticeMailLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeMailLogic");
            switch($_GET["a"]){
                case "add":
                    $noticeLogic->register($item->getId(), $loggedInUserId);

                    //管理側にメールを送信するか？
                    SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
                    $cnf = NoticeArrivalUtil::getConfig();
                    if(isset($cnf["send_mail"]) && $cnf["send_mail"]){

                        //MailLogicの呼び出し
                        SOY2::import("domain.config.SOYShop_ServerConfig");
                        $adminMailAddress = SOYShop_ServerConfig::load()->getAdministratorMailAddress();

                        if(strlen($adminMailAddress)){
                            $mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

                            /**
                             * @ToDo 文面の設定
                             */
                            $title = "[#SHOP_NAME#] #ITEM_NAME#の入荷通知登録がありました。";
                            $content = "#ITEM_NAME#の入荷通知登録がありました。";
                            $title = $noticeMailLogic->convertMailContent($title, $item);
                            $body = $noticeMailLogic->convertMailContent($content, $item);

                            $mailLogic->sendMail($adminMailAddress, $title, $body);

                            self::_redirect($url, array("notice" => "successed"));
                        }
                    }

                    break;
                case "remove":
                    break;
            }

            self::_redirect($url);
        }

		//ログイン後に戻ってきた時の処理
		if(get_class($page) != "SOYShop_UserPage" && strpos($_SERVER["REQUEST_URI"], "login=complete") && strpos($_SERVER["HTTP_REFERER"], "notice_register=")){
			preg_match('/notice_register=(.*)/', $_SERVER["HTTP_REFERER"], $tmp);
			if(isset($tmp[1]) && is_numeric($tmp[1])){
				$url = str_replace("/" . SOYSHOP_ID . "/", "", soyshop_get_site_url(true)) . $_SERVER["REDIRECT_URL"] . "?notice=" . (int)$tmp[1] . "&a=add&soy2_token=" . soy2_get_token();
				header("Location:". $url);
				exit;
			}
		}
    }

    private function _redirect(string $url, array $params=array()){
		$q = "";
        if(count($params)){
            foreach($params as $key => $p){
                $q .= (!strlen($q)) ? "?" : "&";
                $q .= $key . "=" . $p;
            }
        }
        header("Location:" . $url . $q);
        exit;
    }

    private function _jumpLoginPage(int $itemId){
        header("Location:" . soyshop_get_mypage_url(true) . "/login?r=" . $_SERVER["REDIRECT_URL"] . "&notice_register=" . $itemId);
        exit;
    }
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_notice_arrival", "CommonNoticeArrivalBeforeOutput");
