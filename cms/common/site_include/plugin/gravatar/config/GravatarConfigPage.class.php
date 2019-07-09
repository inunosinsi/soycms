<?php

class GravatarConfigPage extends WebPage{

	private $pluginObj;
	private $error = false;
	private $logic;

	function __construct(){
		SOY2::import("site_include.plugin.gravatar.component.admin.GravatarAccountListComponent");
		SOY2::imports("site_include.plugin.gravatar.domain.*");
		$this->logic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic");
	}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["Account"])){
				$dao = SOY2DAOFactory::create("GravatarAccountDAO");

				//名前からスペースを除く
				$_POST["Account"]["name"] = trim(str_replace(array(" ", "　"), "", $_POST["Account"]["name"]));
				$gravatar = SOY2::cast("GravatarAccount", $_POST["Account"]);

				try{
					$dao->insert($gravatar);
					$this->logic->removeCacheFile();
					CMSPlugin::redirectConfigPage();
				}catch(Exception $e){
					//
				}

				$this->error = true;
			}

			if(isset($_POST["Config"])){
				$this->pluginObj->setThumbnailSize($_POST["Config"]["thumbnail"]);
				$this->pluginObj->setDetailSize($_POST["Config"]["detail"]);

				CMSPlugin::savePluginConfig(GravatarPlugin::PLUGIN_ID,$this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}

			//キャッシュの削除
			if(isset($_POST["cache"])){
				$this->logic->removeCacheFile();
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["Page"])){
				$pageId = (isset($_POST["Page"]["pageId"]) && is_numeric($_POST["Page"]["pageId"])) ? (int)$_POST["Page"]["pageId"] : null;
				$this->pluginObj->setGravatarListPageId($pageId);
				CMSPlugin::savePluginConfig(GravatarPlugin::PLUGIN_ID,$this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		//削除
		if(isset($_GET["remove"])){
			self::remove();
		}

		if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			parent::__construct();
		}

		DisplayPlugin::toggle("error", $this->error);

		self::buildForm();
		self::buildThumbnailForm();
		self::buildListForm();

		$this->addForm("cache_form");

		$this->createAdd("gravatar_list", "GravatarAccountListComponent", array(
			"list" => $this->logic->getGravatars()
		));

		//Gravatarごとの記事一覧ページのURLの例
		DisplayPlugin::toggle("url_sample", $this->pluginObj->getGravatarListPageId());
		$this->addLabel("url_text", array(
			"text" => "http://" . $_SERVER["HTTP_HOST"] . SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarPageLogic")->getPageUrl($this->pluginObj->getGravatarListPageId()) . "{Gravatarアバターの名前}"
		));
	}

	private function remove(){
		$id = (int)$_GET["remove"];
		try{
			SOY2DAOFactory::create("GravatarAccountDAO")->deleteById($id);
			$this->logic->removeCacheFile();
		}catch(Exception $e){
			//
		}
	}

	private function buildForm(){
		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Account[name]",
			"value" => "",
			"attr:required" => "required"
		));

		$this->addInput("mail_address", array(
			"name" => "Account[mailAddress]",
			"value" => "",
			"attr:required" => "required"
		));
	}

	private function buildThumbnailForm(){
		$this->addForm("thumb_form");

		$this->addInput("thumbnail", array(
			"name" => "Config[thumbnail]",
			"value" => $this->pluginObj->getThumbnailSize()
		));

		$this->addInput("detail", array(
			"name" => "Config[detail]",
			"value" => $this->pluginObj->getDetailSize()
		));
	}

	private function buildListForm(){
		$this->addForm("list_form");

		$this->addSelect("page", array(
			"name" => "Page[pageId]",
			"options" => self::getPageList(),
			"selected" => $this->pluginObj->getGravatarListPageId()
		));
	}

	private function getPageList(){
		try{
			$pages = self::pageDao()->getByPageType(Page::PAGE_TYPE_NORMAL);
		}catch(Exception $e){
			return array();
		}

		if(!count($pages)) return array();

		$list = array();
		foreach($pages as $page){
			$list[$page->getId()] = $page->getTitle();
		}

		return $list;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	private function pageDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.PageDAO");
		return $dao;
	}
}
