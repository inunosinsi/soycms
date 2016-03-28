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
	function getFormPage(){

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
	function getViewPage($page){

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

				}catch(Exception $e){
					$this->isStickUrl = false;
				}
			}
		}catch(Exception $e){

		}

		SOY2DAOConfig::Dsn($oldDsn);

		return SOY2HTMLFactory::createInstance("ScriptModuleBlockComponent_ViewPage",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => @$articlePageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"dsn" => @$siteDsn,
			"blockId" => $this->getBlockId()
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){

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
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_CUSTOM_SCRIPT_BLOCK");
	}

	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_CUSTOM_SCRIPT_BLOCK_DESCRIPTION");"";
	}

	/**
	 * scriptPathはSOY2::RootDir()からの相対パスでも可
	 * 個別サイトのディレクトリからのパスも可
	 */
	function getScriptFullPath(){
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

	function getScriptPath() {
		return $this->scriptPath;
	}
	function setScriptPath($scriptPath) {
		$this->scriptPath = $scriptPath;
	}
	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	function getIsStickUrl() {
		return $this->isStickUrl;
	}
	function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}
	function getBlogPageId() {
		return $this->blogPageId;
	}
	function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	function getFunctionName() {
		return $this->functionName;
	}
	function setFunctionName($functionName) {
		$this->functionName = $functionName;
	}

	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}

}


class ScriptModuleBlockComponent_FormPage extends HTMLPage{

	private $entity;
	private $sites;
	private $blogPages = array();

	function ScriptModuleBlockComponent_FormPage(){
		HTMLPage::HTMLPage();

	}

	function execute(){

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

		$this->createAdd("script_path_error","HTMLLabel",array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SCRIPT_NO_FILE"),
			"visible" => !$pathExists
		));

		$this->createAdd("function_name_error","HTMLLabel",array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SCRIPT_NO_METHOD"),
			"visible" => !$functionExists
		));

		$this->createAdd("site_hidden","HTMlInput",array(
			"name" => "object[siteId]",
			"value" => $this->entity->getSiteId()
		));


		$this->createAdd("script_path","HTMLInput",array(
			"name" => "object[scriptPath]",
			"value" => $this->entity->getScriptPath()
		));

		$this->createAdd("function_name","HTMLInput",array(
			"name" => "object[functionName]",
			"value" => $this->entity->getFunctionName()
		));


		$this->createAdd("no_stick_url","HTMLHidden",array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));

		$this->createAdd("stick_url","HTMLCheckBox",array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_BLOCK_ADD_ENTRY_LINK_TO_THE_TITLE"),
			"value" => 1,
			"selected" => $this->entity->getIsStickUrl(),
			"visible" =>  (count($this->blogPages) > 0)
		));

		$style = SOY2HTMLFactory::createInstance("SOY2HTMLStyle");
		$style->display = ($this->entity->getIsStickUrl()) ? "" : "none";

		$this->createAdd("blog_page_list","HTMLSelect",array(
			"name" => "object[blogPageId]",
			"selected" => $this->entity->getBlogPageId(),
			"options" => $this->blogPages,
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->createAdd("blog_page_list_label","HTMLLabel",array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SELECT_BLOG_TITLE"),
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->createAdd("main_form","HTMLForm",array());


		//サイト変更機能
		$this->createAdd("sites_form","HTMLForm");
		$this->createAdd("site","HTMLSelect",array(
			"options" => $this->sites,
			"property" => "siteName",
			"name" => "object[siteId]",
			"selected" => $this->entity->getSiteId()
		));

	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	function setEntity(ScriptModuleBlockComponent $block){
		$this->entity = $block;
	}

	/**
	 * ブログページを渡す
	 *
	 * array(ページID => )
	 */
	function setBlogPages($pages){
		$this->blogPages = $pages;
	}

   /**
     *  ラベルオブジェクトのリストを返す
     *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
     */
    function getLabelList(){
    	$dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $dao->get();
    }

	function getTemplateFilePath(){
		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "ScriptModuleBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "ScriptModuleBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "ScriptModuleBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";
		}
	}


	function getSites() {
		return $this->sites;
	}
	function setSites($sites) {
		$this->sites = $sites;
	}
}


class ScriptModuleBlockComponent_ViewPage extends HTMLList{

	var $isStickUrl;
	var $articlePageUrl;
	var $blogPageId;
	var $blockId;

	private $dsn = false;

	function setIsStickUrl($flag){
		$this->isStickUrl = $flag;
	}

	function setArticlePageUrl($articlePageUrl){
		$this->articlePageUrl = $articlePageUrl;
	}

	function setBlogPageId($id){
		$this->blogPageId = $id;
	}

	function getStartTag(){

		return parent::getStartTag();
	}

	function getDsn() {
		return $this->dsn;
	}
	function setDsn($dsn) {
		$this->dsn = $dsn;
	}

	/**
	 * 実行前後にDSNの書き換えを実行
	 */
	function execute(){

		if($this->dsn)$old = SOY2DAOConfig::Dsn($this->dsn);

		parent::execute();

		if($this->dsn)SOY2DAOConfig::Dsn($old);
	}

	function populateItem($entity){

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = $this->articlePageUrl.rawurlencode($entity->getAlias());

		if($this->isStickUrl){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $entity->getId(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"html"=> $hTitle,
			"soy2prefix"=>"cms"
		));
		$this->createAdd("content","CMSLabel",array(
			"html"=>$entity->getContent(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entity->getMore(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("create_date","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("create_time","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		//entry_link追加
		$this->createAdd("entry_link","HTMLLink",array(
			"link" => $entryUrl,
			"soy2prefix"=>"cms"
		));

		//リンクの付かないタイトル 1.2.6～
		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entity->getTitle(),
			"soy2prefix"=>"cms"
		));

		//1.2.7～
		$this->createAdd("more_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		//1.7.5~
		$this->createAdd("update_date","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("update_time","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->createAdd("entry_url","HTMLLabel",array(
			"text"=>$entryUrl,
			"soy2prefix"=>"cms",
		));

		//ラーメンマップ用にblockIdを渡しておく
		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entity->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entity, "blockId"=>$this->blockId));
	}
	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}

}

?>
