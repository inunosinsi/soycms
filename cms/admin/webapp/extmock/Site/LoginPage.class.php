<?php

class LoginPage extends CMSWebPageBase{

    function LoginPage($args) {

    	WebPage::WebPage();

    	$id = (isset($args[0])) ? $args[0] : null;

		$res = false;

		//SOYShopサイトのIDを取得する
		if($id == 0 && isset($_GET["site_id"])){
			$siteId = $_GET["site_id"];
			$site = SOYShopUtil::getShopSite($siteId);
			if(!is_null($site->getId())){
				$id = $site->getId();
				$res = true;
			}else{
				SOY2PageController::jump("Site");
			}
		}

    	//他のサイトにログインしているかどうかチェック
    	$oldSite = UserInfoUtil::getSite();

    	$action = SOY2ActionFactory::createInstance("Site.LoginAction", array(
    		"siteId" => $id
    	));

    	$result = $action->run();
    	
    	//SOYShopの管理画面へ遷移する
    	if($res){
    		$session = SOY2ActionSession::getUserSession();
    		SOYShopUtil::setShopAdminSession($session);
    	}

    	if($result->success()){
    		
    		//URLにappIdの値が存在している場合は直接SOY Appに
    		if(isset($_GET["appId"])){
    			SOY2PageController::redirect("../app/index.php/" . $_GET["appId"]);
    		}

    		if($oldSite && $oldSite->getId() != $id){
    			$this->addMessage("NOTIFY_DOUBLE_LOGIN", array(
    				"SITE_NAME" => $oldSite->getSiteName()
    			));
    			CMSMessageManager::save();
    		}

			//転送先の指定があればそこへリダイレクト
			$redirect = isset($_GET["r"]) ? $_GET["r"] : "" ;
			if(strlen($redirect) > 0 && CMSAdminPageController::isAllowedPath($redirect, "../soycms/")){
				SOY2PageController::redirect($redirect);
			}else{
				SOY2PageController::redirect("../soycms/");
			}

			exit;
    	}

    	SOY2PageController::jump("Site");
    }
}
?>