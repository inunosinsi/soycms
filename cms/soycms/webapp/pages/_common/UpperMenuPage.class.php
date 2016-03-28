<?php

class UpperMenuPage extends CMSHTMLPageBase{

    function UpperMenuPage() {
    	HTMLPage::HTMLPage();
    }
    
    function execute(){
    	
    	//sitePath
		$this->addLink("sitepath", array(
			"text" => "/" . UserInfoUtil::getSite()->getSiteId(),
			"link" => CMSUtil::getSiteUrl(),
			"style" => "text-decoration:none;color:black;"
		));
		
		$this->addLabel("sitename", array(
			"text" => UserInfoUtil::getSite()->getSiteName()
		));
		
		//管理者名
		$this->addLabel("adminname", array(
			"text" => UserInfoUtil::getUserName(),
			"width" => 18,
			"title" => UserInfoUtil::getUserName(),
		));
		
		//popup
		$messages = CMSMessageManager::getMessages();
		$error  = CMSMessageManager::getErrorMessages();
				
		$this->addLabel("message", array(
			"html" => implode("", $error) . implode("", $messages)
		));
		
		$this->addModel("popup", array(
			"style" => (count($error) > 0 || count($messages) > 0) ? "" : "display:none;"
		));
		
		//SOY InquiryかSOY Mailのデータベースがサイト側に存在している場合、新しいinlineを表示する
		$inquiryUseSiteDb = SOYAppUtil::checkAppAuth("inquiry");
		$mailUseSiteDb = SOYAppUtil::checkAppAuth("mail");
		
		$this->addModel("display_app_link", array(
			"visible" => ($inquiryUseSiteDb || $mailUseSiteDb)
		));
		
		$this->addModel("display_inquiry_link", array(
			"visible" => ($inquiryUseSiteDb)
		));
		
		$this->addModel("display_mail_link", array(
			"visible" => ($mailUseSiteDb)
		));
		
		//SOY Inquiryのデータベースがサイト側に存在する場合に表示するリンク
		$this->addLink("inquiry_link", array(
			"link" => SOYAppUtil::createAppLink("inquiry")
		));
		
		//SOY Mailのデータベースがサイト側に存在する場合に表示するリンク
		$this->addLink("mail_link", array(
			"link" => SOYAppUtil::createAppLink("mail")
		));
		
		//エクストラモード周り 権限まわりの二重チェック config.ext.phpでも行っている
		$extMode = false;
		$extConfigFilePath = dirname(SOY2HTMLConfig::PageDir()) . "/config.ext.php";
		if(file_exists($extConfigFilePath) && defined("EXT_MODE_DERECTORY_NAME")){
			$extDir = dirname(SOY2HTMLConfig::PageDir()) . "/" . EXT_MODE_DERECTORY_NAME;
			if(file_exists($extDir)){
				$extMode = true;
			}
		}

		//config.ext.phpがあり、extモード用のディレクトリがあることを確認してからリンクを表示する
		$this->addModel("display_ext_link", array(
			"visible" => ($extMode)
		));
		
		$this->addLink("account_link", array(
			"link" => (defined("SOYCMS_ASP_MODE")) ? 
						 SOY2PageController::createLink("Login.UserInfo")
						:SOY2PageController::createRelativeLink("../admin/index.php/Account")
		));
    }
}
?>