<?php
UtilMultiLanguagePlugin::register();

class UtilMultiLanguagePlugin{

	const PLUGIN_ID = "UtilMultiLanguagePlugin";

	private $config;
	private $check_browser_language;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"多言語サイトプラグイン",
			"description"=>"サイトの言語設定を確認し、指定したURLへリダイレクトします。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.7"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){
			
			SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");

			//公開側へのアクセス時に必要に応じてリダイレクトする
			//出力前にセッションIDをURLに仕込むための宣言をしておく
			CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
			CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));

		//プラグインの初回動作
		}else{
			//
		}
	}

	/**
	 *
	 * @return $html
	 */
	function config_page($message){
		include_once(dirname(__FILE__) . "/config/UtilMultiLanguageConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("UtilMultiLanguageConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * サイトアクセス時の動作
	 */
	function onSiteAccess($obj){
		$this->redirect($obj);
	}

	/**
	 * 公開側の出力
	 */
	function redirect(){
		
		//既に設定している場合は処理を止める
		if(defined("SOYCMS_PUBLISH_LANGUAGE")) return;
			
		$config = $this->getConfig();
		$redirectLogic = SOY2Logic::createInstance("site_include.plugin.util_multi_language.logic.RedirectLanguageSiteLogic");
		
		//ブラウザの言語設定を確認するモード
		if($this->check_browser_language){
			$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
			
			foreach(self::getLanguageList() as $lang => $title){
				if(preg_match('/^' . $lang . '/i', $language)) {
					$languageConfig = $lang;
					break;
				}
			}
			
			//念の為
			if(!isset($languageConfig)) $languageConfig = "jp";
			
		//言語切替ボタンを使うモード
		}else{
			$userSession = SOY2ActionSession::getUserSession();
			
			//言語切替ボタンを押したとき
			if(isset($_GET["language"])){
				$languageConfig = $redirectLogic->getLanguageArterCheck($config);
				$userSession->setAttribute("soycms_publish_language", $languageConfig);
				$userSession->setAttribute("soyshop_publish_language", $languageConfig);
			//押してないとき
			}else{
				$languageConfig = $userSession->getAttribute("soycms_publish_language");
				if(is_null($languageConfig)){
					//SOY Shopの方の言語設定も確認する
					$languageConfig = $userSession->getAttribute("soyshop_publish_language");
					
					if(is_null($languageConfig)){
						$languageConfig = "jp";
						$userSession->setAttribute("soycms_publish_language", $languageConfig);
					}
				}
			}
		}
		
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")){
			define("SOYCMS_PUBLISH_LANGUAGE", $languageConfig);
			define("SOYSHOP_PUBLISH_LANGUAGE", $languageConfig);
		}
		$redirectPath = $redirectLogic->getRedirectPath($config);
		
		if($redirectLogic->checkRedirectPath($redirectPath)){
			CMSPageController::redirect($redirectPath);
			exit;
		}
	}

	function onPageOutput($obj){
		foreach(SOYCMSUtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			$obj->addLink("language_" . $lang . "_link", array(
				"soy2prefix" => "cms",
				"link" => "?language=" . $lang
			));
		}
	}

	function getConfig(){
		if(strlen($this->config)){
			return soy2_unserialize($this->config);
		}else{
			return $this->config;
		}
	}
	
	function setConfig($config){
		$this->config = soy2_serialize($config);
	}
	
	function getCheckBrowserLanguage(){
		return $this->check_browser_language;
	}
	
	function setCheckBrowserLanguage($check_browser_language){
		$this->check_browser_language = $check_browser_language;
	}
	
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new UtilMultiLanguagePlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>