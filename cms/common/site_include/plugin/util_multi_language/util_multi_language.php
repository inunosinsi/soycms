<?php
UtilMultiLanguagePlugin::register();

class UtilMultiLanguagePlugin{

	const PLUGIN_ID = "UtilMultiLanguagePlugin";

	private $config;
	private $check_browser_language;
	private $sameUriMode = true;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"多言語サイトプラグイン",
			"type" => Plugin::TYPE_SITE,
			"description"=>"サイトの言語設定を確認し、指定したURLへリダイレクトします。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.10"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");

			//公開画面側
			if(defined("_SITE_ROOT_")){
				if($this->sameUriMode) {
					CMSPlugin::setEvent('onPathInfoBuilder', self::PLUGIN_ID, array($this, "onPathInfoBuilder"));
					CMSPlugin::setEvent('onPageOutputLabelRead', self::PLUGIN_ID, array($this, "onPageOutputLabelRead"));
					CMSPlugin::setEvent('onPageOutputLabelListRead', self::PLUGIN_ID, array($this, "onPageOutputLabelListRead"));
				}
				
				//公開側へのアクセス時に必要に応じてリダイレクトする
				//出力前にセッションIDをURLに仕込むための宣言をしておく
				CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
				
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
			}else{
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				if($this->sameUriMode) {
					CMSPlugin::setEvent('onLabelUpdate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
					CMSPlugin::setEvent('onLabelCreate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
					CMSPlugin::setEvent('onLabelRemove', self::PLUGIN_ID, array($this, "onLabelRemove"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Label.Detail", array($this, "onCallCustomField"));
				}

			}

		//プラグインの初回動作はなし
		}
	}

	function onPathInfoBuilder($arg){
		$lngCnf = SOY2ActionSession::getUserSession()->getAttribute("soycms_publish_language");
		if(is_string($lngCnf) && strlen($lngCnf) && count($arg["args"])){
			if($arg["args"][0] == $lngCnf){
				$_dust = array_shift($arg["args"]);
				
				list($uri, $args) = CMSPathInfoBuilder::parsePath(implode("/", $arg["args"]) , false);
				$arg = array("uri" => $uri, "args" => $args);
			}
		}
		return $arg;
	}

	/**
	 * サイトアクセス時の動作
	 */
	function onSiteAccess($args){
		$controller = &$args["controller"];
		self::_redirect($args["controller"]);
	}

	/**
	 * 公開側の出力
	 */
	private function _redirect(CMSPageController $controller){

		//既に設定している場合は処理を止める
		if(defined("SOYCMS_PUBLISH_LANGUAGE")) return;

		$cnf = $this->getConfig();
		$redirectLogic = SOY2Logic::createInstance("site_include.plugin.util_multi_language.logic.RedirectLanguageSiteLogic");

		//ブラウザの言語設定を確認するモード
		if($this->check_browser_language){
			$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

			$lngCnf = "";
			$lngList = SOYCMSUtilMultiLanguageUtil::allowLanguages();
			if(count($lngList)){
				foreach($lngList as $_lng => $_dust){
					if(preg_match('/^' . $_lng . '/i', $language)) {
						$lngCnf = $_lng;
						break;
					}
				}
			}

			//念の為
			if(!strlen($lngCnf)) $lngCnf = "jp";

		//言語切替ボタンを使うモード
		}else{
			$userSession = SOY2ActionSession::getUserSession();

			//言語切替ボタンを押したとき
			if(isset($_GET["language"])){
				$lngCnf = $redirectLogic->getLanguageArterCheck($cnf);
				$userSession->setAttribute("soycms_publish_language", $lngCnf);
				$userSession->setAttribute("soyshop_publish_language", $lngCnf);
			//押してないとき
			}else{
				$lngCnf = $userSession->getAttribute("soycms_publish_language");
				if(is_null($lngCnf)){
					//SOY Shopの方の言語設定も確認する
					$lngCnf = $userSession->getAttribute("soyshop_publish_language");

					if(is_null($lngCnf)){
						$lngCnf = "jp";
						$userSession->setAttribute("soycms_publish_language", $lngCnf);
					}
				}
			}
		}

		if(!defined("SOYCMS_PUBLISH_LANGUAGE")){
			define("SOYCMS_PUBLISH_LANGUAGE", $lngCnf);
			define("SOYSHOP_PUBLISH_LANGUAGE", $lngCnf);
		}
		$redirectPath = $redirectLogic->getRedirectPath($cnf);

		if($redirectLogic->checkRedirectPath($redirectPath)){
			// 応急処置
			if(!defined("SOYCMS_PHP_CGI_MODE")) define("SOYCMS_PHP_CGI_MODE", function_exists("php_sapi_name") && stripos(php_sapi_name(), "cgi") !== false );
			if(SOYCMS_PHP_CGI_MODE){	// ?pathinfo=***が自動で付与された時にリダイレクトがおかしくなる
				if(is_bool(strpos($_SERVER["REQUEST_URI"], "?pathinfo="))){
					SOY2PageController::redirect($redirectPath);
					exit;
				}

				// GETパラメータでpathinfoがある時にリダイレクトを行うとリダイレクトループにハマる

			}else{
				CMSPageController::redirect($redirectPath);
				exit;
			}
		}
	}

	function onPageOutputLabelRead($arg){
		if(SOYCMS_PUBLISH_LANGUAGE == "jp") return null;
		$labelId = &$arg["labelId"];

		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
		$_labelId = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO")->getRelationLabelIdByParentIdAndLang($labelId, SOYCMS_PUBLISH_LANGUAGE);
		return (is_numeric($_labelId) && $_labelId > 0) ? $_labelId : null;
	}

	function onPageOutputLabelListRead($arg){
		if(SOYCMS_PUBLISH_LANGUAGE == "jp") return null;

		$labelIds = &$arg["labelIds"];
		if(!count($labelIds)) return null;

		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
		$_arr = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO")->getRelationListByParentIdsAndLang($labelIds, SOYCMS_PUBLISH_LANGUAGE);

		$new = array();
		foreach($labelIds as $labelId){
			$new[] = (isset($_arr[$labelId]) && is_numeric($_arr[$labelId])) ? $_arr[$labelId] : $labelId;
		}
		
		return $new;
	}
	function onPageOutput($obj){
		$uri = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "";
		if(is_numeric(strpos($uri, "?"))) $uri = substr($uri, 0, strpos($uri, "?"));
		foreach(SOYCMSUtilMultiLanguageUtil::getLanguageList($this) as $lang){
			$obj->addLink("language_" . $lang . "_link", array(
				"soy2prefix" => "cms",
				"link" => $uri."?language=" . $lang
			));
		}
	}

	/**
	 * ラベル更新時
	 */
	function onLabelUpdate($arg){
		if(!isset($arg["label"]) && !isset($_POST["multi_language"])) return;
		$labelId = (int)$arg["label"]->getId();

		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
		$dao = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO");

		foreach($_POST["multi_language"] as $lang => $_labelId){
			$idx = SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang);
			$_labelId = (int)$_labelId;
			// 登録
			if($_labelId > 0){
				$obj = new MultiLanguageLabelRelation();
				$obj->setParentId($labelId);
				$obj->setLang($idx);
				$obj->setChildId($_labelId);
				
				try{
					$dao->insert($obj);
				}catch(Exception $e){
					try{
						$dao->delete($labelId, $idx);
						$dao->insert($obj);
					}catch(Exception $e){
						//
					}
				}

			// 削除
			}else{
				try{
					$dao->delete($labelId, $idx);
				}catch(Exception $e){
					//
				}
			}
		}

		return true;
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onLabelRemove(array $args){
		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
		$dao = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO");

		foreach($args as $labelId){
			try{
				$dao->deleteByParentId($labelId);
			}catch(Exception $e){
				//
			}
		}
		return true;
	}

	/**
	 * ラベル編集画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$labelId = (isset($arg[0])) ? (int)$arg[0] : 0;

		SOY2::import("site_include.plugin.util_multi_language.component.BuildLabelCustomFieldFormComponent");
		$component = new BuildLabelCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm($labelId);
	}

	/**
	 *
	 * @return $html
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.util_multi_language.config.UtilMultiLanguageConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("UtilMultiLanguageConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getConfig(){
		return (is_string($this->config)) ? soy2_unserialize($this->config) : $this->config;
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

	function getSameUriMode(){
		return $this->sameUriMode;
	}
	function setSameUriMode($sameUriMode){
		$this->sameUriMode = $sameUriMode;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new UtilMultiLanguagePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
