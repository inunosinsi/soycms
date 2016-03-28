<?php
/*
 * Created on 2009/07/09
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
SOYCMS_PageInfoEditPlugin::registerPlugin();

class SOYCMS_PageInfoEditPlugin{
	
	const PLUGIN_ID = "soycms_page_info_plugin";
	
	private $keywords = array();
	private $description = array();
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ページ情報編集プラグイン",
			"description"=>"一括でページ情報を編集します。KeywordとDescriptionの設定もできます。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
			
			if(defined("_SITE_ROOT_"))
				CMSPlugin::setEvent("onPageOutput",$this->getId(),array($this,"onPageOutput"));
		}
	}
	
	function getId(){
		return SOYCMS_PageInfoEditPlugin::PLUGIN_ID;
	}
	
	function getKeywords() {
		return $this->keywords;
	}
	function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	
	function config_page(){
		if(isset($_POST["format"])){
			
			$pageDAO = SOY2DAOFactory::create("cms.PageDAO");
			$blogPageDAO = SOY2DAOFactory::create("cms.BlogPageDAO");
			$appPageDAO = SOY2DAOFactory::create("cms.ApplicationPageDAO");
			$mobilePageDAO = SOY2DAOFactory::create("cms.MobilePageDAO");
			
			$pageDAO->begin();
			
			foreach($_POST["format"] as $id => $value){
				
				try{
					$page = $pageDAO->getById($id);
					
					switch($page->getPageType()){
						case Page::PAGE_TYPE_BLOG:
						
							$page = $blogPageDAO->getById($id);
							SOY2::cast($page,(object)$value);
							$page = $this->convertOpenPeriod($page);
							$blogPageDAO->updatePageConfig($page);
							
							break;
						case Page::PAGE_TYPE_MOBILE:
							
							$page = $mobilePageDAO->getById($id);
							SOY2::cast($page,(object)$value);
							$page = $this->convertOpenPeriod($page);
							$mobilePageDAO->updatePageConfig($page);
														
							break;
							
						case Page::PAGE_TYPE_APPLICATION:
							
							$page = $appPageDAO->getById($id);
							SOY2::cast($page,(object)$value);
							$page = $this->convertOpenPeriod($page);
							$appPageDAO->updatePageConfig($page);
							
							break;
						
						case Page::PAGE_TYPE_NORMAL:
							SOY2::cast($page,(object)$value);
							$page = $this->convertOpenPeriod($page);
							$pageDAO->update($page);
							$pageDAO->updatePageConfig($page);
							break;
						}
				
				}catch(Exception $e){
					
				}
				
			}
			
			$pageDAO->commit();
			
			$this->keywords = $_POST["keyword"];
			$this->description = $_POST["description"];
			
			CMSPlugin::savePluginConfig($this->getId(),$this);
			
			CMSPlugin::redirectConfigPage();
		}
		
		ob_start();
		include(dirname(__FILE__) . "/config.php");
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	function convertOpenPeriod($page){
		$openPeriodStart = $page->getOpenPeriodStart();
		$tmpDate = (strlen($openPeriodStart)) ? strtotime($openPeriodStart) : false;
    	if($tmpDate === false){
    		$openPeriodStart = null;
    	}else{
    		$openPeriodStart = $tmpDate;
    	}
    	
    	$openPeriodEnd = $page->getOpenPeriodEnd();
		$tmpDate = (strlen($openPeriodEnd)) ? strtotime($openPeriodEnd) : false;
    	if($tmpDate === false){
    		$openPeriodEnd = null;
    	}else{
    		$openPeriodEnd = $tmpDate;
    	}
    	
    	$page->setOpenPeriodStart($openPeriodStart);
    	$page->setOpenPeriodEnd($openPeriodEnd);
    	
    	return $page;
	}
	
	function onPageOutput($args){
		
		$page = $args;
		
		$pageId = $page->id;
		
		$page->createAdd("page_keyword","PageInfo_MetaComponent",array(
			"name" => "keywords",
			"text" => (isset($this->keywords[$pageId])) ? $this->keywords[$pageId] : "",
			"soy2prefix" => "cms"
		));
		
		$page->createAdd("page_description","PageInfo_MetaComponent",array(
			"name" => "description",
			"text" => (isset($this->description[$pageId])) ? $this->description[$pageId] : "",
			"soy2prefix" => "cms"
		));
	}
	
	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_PageInfoEditPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_PageInfoEditPlugin();
		}
		CMSPlugin::addPlugin(SOYCMS_PageInfoEditPlugin::PLUGIN_ID,array($obj,"init"));
	}
}

class PageInfo_MetaComponent extends SOY2HTML{
	
	var $tag = "meta";
	const SOY_TYPE = SOY2HTML::SKIP_BODY;
	var $text = "";
	
	function execute(){
		parent::execute();		
	}
	
	function setText($value){
		$this->text = $value;
		$this->setAttribute("content",$value);
	}
	
	function getObject(){
		return "";
	}
	
}
?>