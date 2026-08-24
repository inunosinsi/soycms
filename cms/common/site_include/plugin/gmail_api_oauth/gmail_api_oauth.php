<?php
GmailApiOAuthPlugin::registerPlugin();

class GmailApiOAuthPlugin {

	const PLUGIN_ID = "gmail_api_oauth";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=> "Gmail API(OAuth2.0認証)プラグイン",
			"type" => Plugin::TYPE_SOYAPP,
			"description"=> "SOY InquiryのSMTPによる送信でGmail API(OAuth2.0認証)の設定を行う為のプラグインになります",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co/soycms/soyinquiry/tutorial/235",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.gmail_api_oauth.util.GmailApiOAuthUtil");
			
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
			}
		}
	}

	function onSiteAccess($obj){
		if(!isset($_SERVER["REQUEST_URI"]) || is_bool(strpos($_SERVER["REQUEST_URI"], GmailApiOAuthUtil::CALLBACK_URI))) return;
		if(file_exists(GmailApiOAuthUtil::getClientSecretJsonPath())){
			include_once("oauth.php");
		}else{
			header("HTTP/1.1 404 Not Found");
			echo "<h1>404 Not Found</h1>";
			echo "お探しのページは見つかりませんでした。";
			exit;
		}
	}

	function config_page($message){
		SOY2::import("site_include.plugin.".self::PLUGIN_ID.".config.GmailApiConfigPage");
		$form = SOY2HTMLFactory::createInstance("GmailApiConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new GmailApiOAuthPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
}
