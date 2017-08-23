<?php

class UpperMenuPage extends CMSHTMLPageBase{

	function execute(){

		//sitePath
		$this->addLink("sitepath", array(
				"text" => "/" . UserInfoUtil::getSite()->getSiteId(),
				"link" => CMSUtil::getSiteUrl(),
				"style" => "text-decoration:none;color:black;"
		));

		$this->addLink("sitepath_link", array(
				"link" => CMSUtil::getSiteUrl(),
				"style" => "text-decoration:none;color:black;"
		));
		$this->addLabel("sitepath_text", array(
				"text" => "/" . UserInfoUtil::getSite()->getSiteId(),
		));

		$this->addLabel("sitename", array(
			"text" => UserInfoUtil::getSite()->getSiteName()
		));

		$this->addModel("biglogo", array(
			"src"=>SOY2PageController::createRelativeLink("css/img/logo_big.gif")
		));

		//管理者名
		$this->addLabel("adminname", array(
			"text" => UserInfoUtil::getUserName(),
			"width" => 18,
			"title" => UserInfoUtil::getUserName(),
		));

		//記事管理者には表示しないもの
		$this->addModel("only_for_site_admin", array(
			"visible" => UserInfoUtil::hasSiteAdminRole(),
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
		$this->addLink("ext_link", array(
				"link" => SOY2PageController::createLink(SOY2PageController::getRequestPath().".".implode(".",SOY2PageController::getArguments()))."?ext_mode",
		));

		$this->addLink("account_link", array(
			"link" => (defined("SOYCMS_ASP_MODE")) ?
						 SOY2PageController::createLink("Login.UserInfo")
						:SOY2PageController::createRelativeLink("../admin/index.php/Account")
		));
	}
}
