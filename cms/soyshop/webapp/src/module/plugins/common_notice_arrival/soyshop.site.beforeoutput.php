<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonNoticeArrivalBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $uri;
	private $itemId;

    function beforeOutput($page){

        //これらの条件を満たさないと処理は開始しない
        if(isset($_GET["notice"]) && isset($_GET["a"]) && soy2_check_token()){

			$this->uri = $page->getPageObject()->getUri();	//リダイレクト時に使用

            $noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
            $this->itemId = (int)$_GET["notice"];

            //現時点で在庫切れ商品であるかを確認する
            if(!$noticeLogic->checkStock($this->itemId)) $this->redirect();

            //ログインしているかを調べる
            $userId = $noticeLogic->getUserId();
            if(!isset($userId)) $this->jumpLoginPage($this->itemId);

            switch($_GET["a"]){
                case "add":
                    $noticeLogic->registerNotice($this->itemId, $userId);

                    //管理側にメールを送信するか？
                    SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
                    $config = NoticeArrivalUtil::getConfig();
                    if(isset($config["send_mail"]) && $config["send_mail"]){

                        //MailLogicの呼び出し
                        SOY2::import("domain.config.SOYShop_ServerConfig");
                        $serverConfig = SOYShop_ServerConfig::load();

                        $adminMailAddress = $serverConfig->getAdministratorMailAddress();

                        if(strlen($adminMailAddress)){
                            $mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
							$item = self::getItemById($this->itemId);

                            SOY2::import("domain.user.SOYShop_User");
                            $user = new SOYShop_User();

                            /**
                             * @ToDo 文面の設定
                             */
                            $title = "[#SHOP_NAME#] #ITEM_NAME#の入荷通知登録がありました。";
                            $content = "#ITEM_NAME#の入荷通知登録がありました。";
                            $title = $noticeLogic->convertMailTitle($title, $item);
                            $body = $noticeLogic->convertMailContent($content, $user, $item);

                            $mailLogic->sendMail($adminMailAddress, $title, $body);

                            $this->redirect(array("notice" => "successed"));
                        }
                    }

                    break;
                case "remove":
                    break;
            }

            $this->redirect();
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

    function redirect($params = array()){
		$url = soyshop_get_page_url($this->uri) . "/" . self::getItemById($this->itemId)->getAlias();

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

    function jumpLoginPage($itemId){
        header("Location:" . soyshop_get_mypage_url(true) . "/login?r=" . $_SERVER["REDIRECT_URL"] . "&notice_register=" . $this->itemId);
        exit;
    }

	private function getItemById($itemId){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($this->itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_notice_arrival", "CommonNoticeArrivalBeforeOutput");
