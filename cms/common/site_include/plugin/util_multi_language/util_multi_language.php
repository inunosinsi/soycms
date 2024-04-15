<?php
UtilMultiLanguagePlugin::register();

class UtilMultiLanguagePlugin{

	const PLUGIN_ID = "UtilMultiLanguagePlugin";

	private $config;
	private $check_browser_language;
	private $sameUriMode = false;
	
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
			"version"=>"1.0.3"
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

					CMSPlugin::setEvent('onEntryGet', self::PLUGIN_ID, array($this,"onEntryGet"));

					// 隠し機能 多言語用の画像に切り替える為の拡張ポイント /path/to/dir/sample.jpg → /path/to/dir/sample_en.jpgに変換
					CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
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
					CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
					CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
					CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

					CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
					CMSPlugin::setEvent('onBlogPageConfigUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
					CMSPlugin::setEvent('onPageRemove', self::PLUGIN_ID, array($this, "onPageRemove"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Page.Title", array($this, "onPageTitleCallCustomField"));
					
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

					CMSPlugin::setEvent('onLabelUpdate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
					CMSPlugin::setEvent('onLabelCreate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
					CMSPlugin::setEvent('onLabelRemove', self::PLUGIN_ID, array($this, "onLabelRemove"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Label.Detail", array($this, "onLabelCallCustomField"));

					CMSPlugin::setEvent('onSiteConfigUpdate', self::PLUGIN_ID, array($this, "onSiteConfigUpdate"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Site.Name", array($this, "onSiteNameCallCustomField"));
					CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Site.Description", array($this, "onSiteDescriptionCallCustomField"));
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
		// SOYCMS_PUBLISH_LANGUAGEを定義していない時にのみ実行
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) {
			$controller = &$args["controller"];
			if(!function_exists("multi_language_redirect")) SOY2::import("site_include.plugin.util_multi_language.func.redirect", ".php");
			multi_language_redirect($args["controller"], $this->getConfig(), (int)$this->check_browser_language);
		}
	}

		/**
	 * ラベル更新時
	 */
	function onPageUpdate($arg){
		if(!isset($arg["new_page"])) return;
		$pageId = (int)$arg["new_page"]->getId();
		if($pageId === 0) return;

		if(!isset($_POST["language"]) || !is_array($_POST["language"]) || !count($_POST["language"])) return;

		foreach($_POST["language"] as $lang => $title){
			$attr = soycms_get_page_attribute_object($pageId, SOYCMSUtilMultiLanguageUtil::LANGUAGE_FIELD_KEY.$lang);
			$attr->setValue(trim($title));
			soycms_save_page_attribute_object($attr);
		}

		// キャッシュの削除
		SOY2Logic::createInstance("logic.cache.CacheLogic")->clearCache();
		
		return true;
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onPageRemove($args){
		foreach($args as $pageId){
			try{
				soycms_get_hash_table_dao("page_attribute")->deleteByPageId($pageId);
			}catch(Exception $e){

			}
		}

		return true;
	}

	function onPageOutputLabelRead($arg){
		static $_res;
		if(is_null($_res)) $_res = array();

		$labelId = &$arg["labelId"];
		if(isset($_res[$labelId])) return $_res[$labelId];

		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
		
		if(SOYCMS_PUBLISH_LANGUAGE != "jp"){
			$_res[$labelId] = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO")->getRelationLabelIdByParentIdAndLang($labelId, SOYCMS_PUBLISH_LANGUAGE);
		// 他言語から日本語設定があるか？を調べる
		}else{
			$_res[$labelId] = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO")->getRelationLabelIdByChildId($labelId);
		}
		return (is_numeric($_res[$labelId]) && $_res[$labelId] > 0) ? $_res[$labelId] : null;
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

	function onEntryGet($args){
		//$blogLabelId = &$args["blogLabelId"];
		$alias = &$args["alias"];
		$entryId = soycms_get_entry_object_by_alias($alias)->getId();
		if(!is_numeric($entryId)) return null;
		
		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageEntryRelationDAO");

		if(SOYCMS_PUBLISH_LANGUAGE != "jp"){
			$_entryId = SOY2DAOFactory::create("MultiLanguageEntryRelationDAO")->getRelationEntryIdByParentIdAndLang($entryId, SOYCMS_PUBLISH_LANGUAGE);
		}else{
			$_entryId = SOY2DAOFactory::create("MultiLanguageEntryRelationDAO")->getRelationEntryIdByChildId($entryId);
		}
		if(is_null($_entryId)) return null;
		
		SOY2::import("logic.site.Entry.class.new.LabeledEntryDAO");
		return SOY2::cast("LabeledEntry", soycms_get_entry_object($_entryId));
    }

	function onOutput($arg){
		if(!function_exists("multi_language_convert_image_filepath")) SOY2::import("site_include.plugin.util_multi_language.func.output", ".php");
		return multi_language_convert_image_filepath($arg, SOYCMS_PUBLISH_LANGUAGE);
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
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		if(!isset($arg["entry"]) && !isset($_POST["multi_language"])) return;
		
		if(!function_exists("multi_language_execute_common_update_process")) SOY2::import("site_include.plugin.util_multi_language.func.fn", ".php");
		return multi_language_execute_common_update_process((int)$arg["entry"]->getId());
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		if(!function_exists("multi_language_execute_common_update_process")) SOY2::import("site_include.plugin.util_multi_language.func.fn", ".php");
		return multi_language_execute_common_remove_process($args);
	}


	function onPageTitleCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0])) ? (int)$arg[0] : 0;
		SOY2::import("site_include.plugin.util_multi_language.component.BuildPageCustomFieldFormComponent");
		$component = new BuildPageCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm($pageId);
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildEntryCustomFieldFormCommon($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildEntryCustomFieldFormCommon($entryId);
	}

	private function _buildEntryCustomFieldFormCommon(int $entryId){
		SOY2::import("site_include.plugin.util_multi_language.component.BuildEntryCustomFieldFormComponent");
		$component = new BuildEntryCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm($entryId);
	}

	/**
	 * ラベル更新時
	 */
	function onLabelUpdate($arg){
		if(!isset($arg["label"]) && !isset($_POST["multi_language"])) return;

		if(!function_exists("multi_language_execute_common_update_process")) SOY2::import("site_include.plugin.util_multi_language.func.fn", ".php");
		return multi_language_execute_common_update_process((int)$arg["label"]->getId(), "Label");
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onLabelRemove(array $args){
		if(!function_exists("multi_language_execute_common_update_process")) SOY2::import("site_include.plugin.util_multi_language.func.fn", ".php");
		return multi_language_execute_common_remove_process($args, "Label");
	}

	/**
	 * ラベル編集画面
	 * @return string HTMLコード
	 */
	function onLabelCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$labelId = (isset($arg[0])) ? (int)$arg[0] : 0;

		SOY2::import("site_include.plugin.util_multi_language.component.BuildLabelCustomFieldFormComponent");
		$component = new BuildLabelCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm($labelId);
	}

	/**
	 * サイトの設定の更新時
	 */
	function onSiteConfigUpdate($arg){
		if(!isset($_POST["language"]) || !is_array($_POST["language"])) return;

		SOY2DAOFactory::importEntity("cms.DataSets");

		if(isset($_POST["language"]["name"]) && is_array($_POST["language"]["name"])){
			foreach($_POST["language"]["name"] as $lang => $name){
				DataSets::put(SOYCMSUtilMultiLanguageUtil::LANGUAGE_SITE_NAME_KEY.$lang, $name);
			}
		}

		if(isset($_POST["language"]["description"]) && is_array($_POST["language"]["description"])){
			foreach($_POST["language"]["description"] as $lang => $desp){
				DataSets::put(SOYCMSUtilMultiLanguageUtil::LANGUAGE_SITE_DESCRIPTION_KEY.$lang, $desp);
			}
		}
	}

	/**
	 * サイトの設定画面
	 * @return string HTMLコード
	 */
	function onSiteNameCallCustomField(){
		SOY2::import("site_include.plugin.util_multi_language.component.BuildSiteNameCustomFieldFormComponent");
		$component = new BuildSiteNameCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm();
	}

	/**
	 * サイトの設定画面
	 * @return string HTMLコード
	 */
	function onSiteDescriptionCallCustomField(){
		SOY2::import("site_include.plugin.util_multi_language.component.BuildSiteDescriptionCustomFieldFormComponent");
		$component = new BuildSiteDescriptionCustomFieldFormComponent();
		$component->setPluginObj($this);
		return $component->buildForm();
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
		$cnf = (is_string($this->config)) ? soy2_unserialize($this->config) : $this->config;
		return (is_array($cnf)) ? $cnf : array();
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
