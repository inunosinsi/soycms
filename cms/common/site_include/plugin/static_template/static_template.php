<?php

StaticTemplatePlugin::register();

class StaticTemplatePlugin{

	const PLUGIN_ID = "StaticTemplatePlugin";
	const FIELD_ID = "static_template";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "静的テンプレートプラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description" => "各ページのテンプレートをHTMLファイルから読み込みます",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/6195",
			"mail" => "info@saitodev.co",
			"version"=>"0.1"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.static_template.util.StaticTemplateUtil");
		
			//管理側
			if(!defined("_SITE_ROOT_")){

				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onBlogPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onPageRemove', self::PLUGIN_ID, array($this, "onPageRemove"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Template", array($this, "onCallCustomField"));

			//公開側
			}else{
				CMSPlugin::setEvent('onPageLoad',self::PLUGIN_ID, array($this,"onPageLoad"), array("filter"=>"all"));
			}

			//共通
			CMSPlugin::setEvent('onReadTemplateFile', self::PLUGIN_ID, array($this, "onReadTemplateFile"));

		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * ラベル更新時
	 */
	function onPageUpdate($arg){
		if(!isset($arg["new_page"])) return;
		$pageId = (int)$arg["new_page"]->getId();
		if($pageId === 0) return;

		$fieldId = StaticTemplateUtil::buildFieldId($pageId);
		$attr = soycms_get_page_attribute_object($pageId, $fieldId);

		if(isset($_POST["static_template_save"])){
			$templateFile = (isset($_POST["static_template"])) ? trim($_POST["static_template"]) : "";
			$attr->setValue($templateFile);
			soycms_save_page_attribute_object($attr);
			return false;
		}

		$selected = (string)$attr->getValue();
		if(strlen($selected)){
			$filepath = StaticTemplateUtil::getTemplateDirectory().$selected;
			if(file_exists($filepath)){
				file_put_contents($filepath, $_POST["template"]);
				// キャッシュの削除
				SOY2Logic::createInstance("logic.cache.CacheLogic")->clearCache();
				return false;
			}
		}
		
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
		$pageId = (isset($arg[0])) ? (int)$arg[0] : 0;
		$blogPageType = (isset($arg[1])) ? $arg[1] : "";
		return self::_buildFormOnPageEditPage($pageId, $blogPageType);
	}
	
	/**
	 * @param int, string
	 * @return html
	 */
	private function _buildFormOnPageEditPage(int $pageId, string $blogPageType){
		$templates = StaticTemplateUtil::getTemplateFileNameList();
		if(!count($templates)) return "";

		$fieldId = StaticTemplateUtil::buildFieldId($pageId, $blogPageType);
		$selected = soycms_get_page_attribute_object($pageId, $fieldId)->getValue();
	
		$html = array();
		$html[] = "<div class=\"form-group form-inline\">";
		$html[] = "<label>テンプレートファイル&nbsp;:&nbsp;</label>";
		$html[] = "<select class=\"form-control\" name=\"static_template\">";
		$html[] = "<option></option>";
		foreach($templates as $tmp){
			$name = (isset($tmp["name"]) && strlen($tmp["name"])) ? $tmp["name"] : "";
			if(strlen($name)){
				$name .= "(".$tmp["filename"].")";
			}else{
				$name = $tmp["filename"];
			}

			if($selected == $tmp["filename"]){
				$html[] = "<option value=\"".$tmp["filename"]."\" selected=\"selected\">".$name."</option>";	
			}else{
				$html[] = "<option value=\"".$tmp["filename"]."\">".$name."</option>";
			}
			
		}
		$html[] = "</select>&nbsp;";
		$html[] = "<input type=\"submit\" name=\"static_template_save\" class=\"btn btn-primary btn-sm\" value=\"読み込む\">";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	function onPageLoad(array $arg){
		$webPage = &$arg["webPage"];
		$cache = $webPage->getCacheFilePath();

		if(file_exists($cache)){
			$cacheUdate = filemtime($cache);

			$tmpDir = StaticTemplateUtil::getTemplateDirectory();
			$fs = soy2_scandir($tmpDir);
			if(count($fs)){
				foreach($fs as $f){
					if(filemtime($tmpDir.$f) > $cacheUdate){
						unlink($cache);
						break;
					}
				}
			}
		}
	}

	function onReadTemplateFile(array $arg){
		$pageId = &$arg["pageId"];
		if(!is_numeric($pageId)) return null;
		
		$blogPageType = &$arg["blogPageType"];

		$fieldId = StaticTemplateUtil::buildFieldId($pageId, (string)$blogPageType);
		$selected = soycms_get_page_attribute_object($pageId, $fieldId)->getValue();
		if(is_null($selected) || !strlen((string)$selected)) return null;

		$filepath = StaticTemplateUtil::getTemplateDirectory().$selected;
		if(!file_exists($filepath)) return null;

		

		return file_get_contents($filepath);
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM PageAttribute", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__DIR__) . "/PageCustomField/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("create" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		//$this->importFields();
		SOY2::import("site_include.plugin.static_template.config.StaticTemplateFormPage");
		$form = SOY2HTMLFactory::createInstance("StaticTemplateFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new StaticTemplatePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
