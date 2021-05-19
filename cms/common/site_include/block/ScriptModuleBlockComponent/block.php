<?php
/**
 * スクリプト読み込み用
 */
class ScriptModuleBlockComponent implements BlockComponent{

	private $scriptPath;
	private $functionName;

	private $siteId;
	private $isStickUrl = false;
	private $blogPageId;
	private $blockId;

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	public function getFormPage(){

		//DSNを切り替える
		if(is_null($this->siteId)){ $this->siteId = UserInfoUtil::getSite()->getId(); }

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

		$sites = $siteDAO->getBySiteType(Site::TYPE_SOY_CMS);

		try{
			$site = $siteDAO->getById($this->siteId);
		}catch(Exception $e){
			$site = UserInfoUtil::getSite();
		}

		SOY2DAOConfig::Dsn($site->getDataSourceName());


		//Blog一覧を取得する
		$logic = SOY2Logic::createInstance("logic.site.Page.BlogPageLogic");
		$blogPages = $logic->getBlogPageList();

		return SOY2HTMLFactory::createInstance("ScriptModuleBlockComponent_FormPage",array(
			"entity" => $this,
			"blogPages" => $blogPages,
			"sites" => $sites
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	public function getViewPage($page){

		$oldDsn = SOY2DAOConfig::Dsn();
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

		$array = array();

		$articlePageUrl = "";

		try{
			$site = $siteDAO->getById($this->siteId);
			SOY2DAOConfig::Dsn($site->getDataSourceName());

			$siteDsn = $site->getDataSourceName();

			$scriptPath = $this->getScriptFullPath();
			$functioName = $this->getFunctionName();


			$array = array();

			//処理の要
			if(file_exists($scriptPath)){
				include_once($scriptPath);
				if(function_exists($functioName)){
					$array = call_user_func($functioName);
					if(!is_array($array))$array = array();
				}
			}

			$articlePageUrl = "";
			$categoryPageUrl = "";
			if($this->isStickUrl){
				try{
					$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
					$blogPage = $pageDao->getById($this->blogPageId);

					$siteUrl = $site->getUrl();

					if($site->getIsDomainRoot()){
						$siteUrl = "/";
					}

					//アクセスしているサイトと同じドメインなら / からの絶対パスにしておく（ケータイでURLに自動でセッションIDが付くように）
					if(strpos($siteUrl,"http://".$_SERVER["SERVER_NAME"]."/")===0){
						$siteUrl = substr($siteUrl,strlen("http://".$_SERVER["SERVER_NAME"]));
					}
					if(strpos($siteUrl,"https://".$_SERVER["SERVER_NAME"]."/")===0){
						$siteUrl = substr($siteUrl,strlen("https://".$_SERVER["SERVER_NAME"]));
					}

					$articlePageUrl = $siteUrl . $blogPage->getEntryPageURL();
					$categoryPageUrl = $siteUrl . $blogPage->getCategoryPageURL();

				}catch(Exception $e){
					$this->isStickUrl = false;
				}
			}
		}catch(Exception $e){

		}

		SOY2DAOConfig::Dsn($oldDsn);

		SOY2::import("site_include.block._common.BlockEntryListComponent");
		return SOY2HTMLFactory::createInstance("BlockEntryListComponent",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"categoryPageUrl" => $categoryPageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"dsn" => (isset($siteDsn)) ? $siteDsn : null,
			"blockId" => $this->getBlockId()
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	public function getInfoPage(){

		$res = $this->functionName;

		//DSNを切り替える
		if(!is_null($this->siteId)){

			$oldDsn = SOY2DAOConfig::Dsn();
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");

			try{
				$site = $siteDAO->getById($this->siteId);

				SOY2DAOConfig::Dsn($site->getDataSourceName());
				$dao = SOY2DAOFactory::create("cms.PageDAO");
				$page = $dao->getById($this->blogPageId);
				$res .= " (".$site->getSiteName() . ": ".$page->getTitle() . ")";

			}catch(Exception $e){
				$res = "";//CMSMessageManager::get("SOYCMS_NO_SETTING");
			}

			SOY2DAOConfig::Dsn($oldDsn);
		}


		return $res;
	}

	/**
	 * @return string コンポーネント名
	 */
	public function getComponentName(){
		return CMSMessageManager::get("SOYCMS_CUSTOM_SCRIPT_BLOCK");
	}

	public function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_CUSTOM_SCRIPT_BLOCK_DESCRIPTION");"";
	}

	/**
	 * scriptPathはSOY2::RootDir()からの相対パスでも可
	 * 個別サイトのディレクトリからのパスも可
	 */
	public function getScriptFullPath(){
		if(strlen($this->scriptPath) >0){
			$paths = array(
				$this->scriptPath,
				CMSPlugin::getSiteDirectory().$this->scriptPath,
				SOY2::RootDir().$this->scriptPath,
			);
			foreach($paths as $path){
				if(file_exists($path)){
					return realpath($path);
				}
			}
		}
		return $this->scriptPath;
	}

	public function getScriptPath() {
		return $this->scriptPath;
	}
	public function setScriptPath($scriptPath) {
		$this->scriptPath = $scriptPath;
	}
	public function getSiteId() {
		return $this->siteId;
	}
	public function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	public function getIsStickUrl() {
		return $this->isStickUrl;
	}
	public function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}
	public function getBlogPageId() {
		return $this->blogPageId;
	}
	public function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	public function getFunctionName() {
		return $this->functionName;
	}
	public function setFunctionName($functionName) {
		$this->functionName = $functionName;
	}

	public function getBlockId() {
		return $this->blockId;
	}
	public function setBlockId($blockId) {
		$this->blockId = $blockId;
	}

	public function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	public function setDisplayCountFrom($displayCountFrom) {
		$cnt = (strlen($displayCountFrom) && is_numeric($displayCountFrom)) ? (int)$displayCountFrom : null;
		$this->displayCountFrom = $cnt;
	}

	public function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	public function setDisplayCountTo($displayCountTo) {
		$cnt = (strlen($displayCountTo) && is_numeric($displayCountTo)) ? (int)$displayCountTo : null;
		$this->displayCountTo = $cnt;
	}
}


class ScriptModuleBlockComponent_FormPage extends HTMLPage{

	private $entity;
	private $sites;
	private $blogPages = array();

	public function execute(){

		$scriptPath = $this->entity->getScriptFullPath();
		$functionName = $this->entity->getFunctionName();

		$pathExists = false;
		$functionExists = false;

		if(file_exists($scriptPath)){
			$pathExists = true;

			include_once($scriptPath);
			if(function_exists($functionName)){
				$functionExists = true;
			}
		}

		$this->addLabel("script_path_error", array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SCRIPT_NO_FILE"),
			"visible" => !$pathExists
		));

		$this->addLabel("function_name_error", array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SCRIPT_NO_METHOD"),
			"visible" => !$functionExists
		));

		$this->addInput("site_hidden", array(
			"name" => "object[siteId]",
			"value" => $this->entity->getSiteId()
		));


		$this->addInput("script_path", array(
			"name" => "object[scriptPath]",
			"value" => $this->entity->getScriptPath()
		));

		$this->addInput("function_name", array(
			"name" => "object[functionName]",
			"value" => $this->entity->getFunctionName()
		));

		$this->createAdd("no_stick_url","HTMLHidden",array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));

		$this->addCheckBox("stick_url", array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_BLOCK_ADD_ENTRY_LINK_TO_THE_TITLE"),
			"value" => 1,
			"selected" => $this->entity->getIsStickUrl(),
			"visible" =>  (count($this->blogPages) > 0)
		));

		$style = SOY2HTMLFactory::createInstance("SOY2HTMLStyle");
		$style->display = ($this->entity->getIsStickUrl()) ? "" : "none";

		$this->addSelect("blog_page_list", array(
			"name" => "object[blogPageId]",
			"selected" => $this->entity->getBlogPageId(),
			"options" => $this->blogPages,
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->addLabel("blog_page_list_label", array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SELECT_BLOG_TITLE"),
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->addForm("main_form");


		//サイト変更機能
		$this->addForm("sites_form");
		$this->addSelect("site", array(
			"options" => $this->sites,
			"property" => "siteName",
			"name" => "object[siteId]",
			"selected" => $this->entity->getSiteId()
		));
	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	public function setEntity(ScriptModuleBlockComponent $block){
		$this->entity = $block;
	}

	/**
	 * ブログページを渡す
	 *
	 * array(ページID => )
	 */
	public function setBlogPages($pages){
		$this->blogPages = $pages;
	}

	/**
	 *  ラベルオブジェクトのリストを返す
	 *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
	 */
	public function getLabelList(){
		$dao = SOY2DAOFactory::create("cms.LabelDAO");
		return $dao->get();
	}

	public function getTemplateFilePath(){

		//ext-modeでbootstrap対応画面作成中
		if(defined("EXT_MODE_BOOTSTRAP") && file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html";
		}

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html";
		}
	}


	public function getSites() {
		return $this->sites;
	}
	public function setSites($sites) {
		$this->sites = $sites;
	}
}
