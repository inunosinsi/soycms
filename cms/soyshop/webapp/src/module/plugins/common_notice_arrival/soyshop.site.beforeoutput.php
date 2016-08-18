<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonNoticeArrivalBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		//これらの条件を満たさないと処理は開始しない
		if(isset($_GET["notice"]) && isset($_GET["a"]) && soy2_check_token()){
						
			$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			$itemId = (int)$_GET["notice"];
			
			//現時点で在庫切れ商品であるかを確認する
			if(!$noticeLogic->checkStock($itemId)) $this->redirect();
			
			//ログインしているかを調べる
			$userId = $noticeLogic->getUserId();
			if(!isset($userId)) $this->jumpLoginPage();
			
			switch($_GET["a"]){
				case "add":
					$noticeLogic->registerNotice($itemId, $userId);
					
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
							
							$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
							try{
								$item = $itemDao->getById($itemId);
							}catch(Exception $e){
								$item = new SOYShop_Item();
							}
							
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
	}
	
	function redirect($params = array()){
		$referer = $_SERVER["HTTP_REFERER"];
		if(strpos($referer, "?")) $referer = substr($_SERVER["HTTP_REFERER"], 0, strpos($_SERVER["HTTP_REFERER"], "?"));
		
		$q = "";
		if(count($params)){
			foreach($params as $key => $p){
				$q .= (!strlen($q)) ? "?" : "&";
				$q .= $key . "=" . $p;
			}
		}
		header("Location:" . $referer . $q);
		exit;
	}
	
	function jumpLoginPage(){
		header("Location:" . soyshop_get_mypage_url(true) . "/login?r=" . $_SERVER["REDIRECT_URL"]);
		exit;
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_notice_arrival", "CommonNoticeArrivalBeforeOutput");
?>