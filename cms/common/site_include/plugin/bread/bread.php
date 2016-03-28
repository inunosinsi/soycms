<?php
/*
 * パン屑リスト出力プラグイン
 *
 */

define('BREAD_PLUGIN_NAME',"bread");

//初期化
$obj = CMSPlugin::loadPluginConfig(BREAD_PLUGIN_NAME);
if(is_null($obj)){
	$obj = new BreadPlugin();
}
CMSPlugin::addPlugin(BREAD_PLUGIN_NAME,array($obj,"init"));

class BreadPlugin{

	var $separetor = "&gt;";

	function setCms_separetor($separetor){
		$this->separetor = $separetor;
	}

	/**
	 * ×separetor
	 * ○separator
	 */
	function setCms_separator($separetor){
		$this->separetor = $separetor;
	}

	function getId(){
		return BREAD_PLUGIN_NAME;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"パン屑リスト出力プラグイン",
			"description"=>"パン屑リストを出力することが出来ます。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.2"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
			CMSPlugin::addBlock($this->getId(),"page",array(
				$this,"block"
			));
		}
	}

	function config_page($message){
		return file_get_contents(dirname(__FILE__)."/info.html");
	}

	function block($html,$pageId){
		$pageDao = SOY2DAOFactory::create("cms.PageDAO");

		$buff = array();

		try{
			while(true){
				$page = $pageDao->getById($pageId);
				if(empty($buff)){
					$buff[] = $page->getTitle();
				}else{
					if(defined("CMS_PREVIEW_MODE")){
						$link = SOY2PageController::createLink("Page.Preview") ."/". $page->getId();
					}else{
						$link = SOY2PageController::createLink("") . $page->getUri();
					}

					$buff[] = '<a href="'.htmlspecialchars($link,ENT_QUOTES,"UTF-8").'">'.htmlspecialchars($page->getTitle(),ENT_QUOTES,"UTF-8").'</a>';
				}

				$pageId = $page->getParentPageId();

				if(!$pageId)break;
			}
		}catch(Exception $e){

		}

		$buff = array_reverse($buff);

		return implode($this->separetor,$buff);
	}


}
?>
