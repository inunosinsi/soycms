<?php

CustomAliasPlugin::register();

class CustomAliasPlugin{

	const PLUGIN_ID = "CustomAlias";
	public $useId;
	public $prefix;
	public $postfix;

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"カスタムエイリアス",
			"description"=>"ブログの記事ページのURLの記事毎に変わる部分（エイリアス）を指定できるようにします。<br>SOY CMS 1.2.4以上で動作します。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onEntryCreate",$this->getId(),array($this,"onEntryUpdate"));
			CMSPlugin::setEvent("onEntryUpdate",$this->getId(),array($this,"onEntryUpdate"));
			CMSPlugin::setEvent("onEntryCopy",$this->getId(),array($this,"onEntryCopy"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID,"Entry.Detail",array($this,"onCallCustomField"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID,"Blog.Entry",array($this,"onCallCustomField_inBlog"));
		}
	}

	public static function register(){
		include_once(dirname(__FILE__)."/config_form.php");
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new CustomAliasPlugin();
		}
		
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}

	function getId(){
		return self::PLUGIN_ID;
	}

	function config_page($message){
		$form = SOY2HTMLFactory::createInstance("CustomAliasPluginFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	function onEntryCopy($ids){
		$oldId = $ids[0];
		$newId = $ids[1];
		
		if($this->useId){
			$entry = $this->getEntry($newId);
			if($entry){
				if($entry->isEmptyAlias() || $entry->getId() != $entry->getAlias()){
					$entry->setAlias($entry->getId());
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					$logic->update($entry);
				}
			}
		}
	}
	
	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		if($this->useId){
			if($entry->isEmptyAlias() || $entry->getId() != $entry->getAlias()){
				$entry->setAlias($entry->getId());
				$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
				$logic->update($entry);
			}
		}
	}
	
	function onCallCustomField(){
		if($this->useId){
			$html = "";
		}else{
			$arg = SOY2PageController::getArguments();
			$entryId = @$arg[0];
			$alias = $this->getAlias($entryId);

			$html = "<div class=\"section custom_alias\">";
			$html .= "<p class=\"sub\"><label for=\"custom_alias_input\">カスタムエイリアス（ブログのエントリーページのURL）</label></p>";
			$html .= "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" style=\"width:400px\" />";
			$html .= "</div>";
		}
		return $html;
	}

	function onCallCustomField_inBlog(){
		if($this->useId){
			$html = "";
		}else{
			$arg = SOY2PageController::getArguments();
			$pageId = @$arg[0];
			$entryId = @$arg[1];
	
			$page = $this->getBlogPage($pageId);
			$alias = $this->getAlias($entryId);
			
			$html = "";
			if($page){
				$entryPageUri = CMSUtil::getSiteUrl().$page->getEntryPageURL();
				$entryUri = $entryPageUri.rawurlencode($alias);
	
				$html .= "<div class=\"section custom_alias\">";
				$html .= "<p class=\"sub\"><label for=\"custom_alias_input\">カスタムエイリアス（ブログのエントリーページのURL）</label></p>";
				$html .= $entryPageUri;
				$html .= "<input value=\"".htmlspecialchars($alias, ENT_QUOTES, "UTF-8")."\" id=\"custom_alias_input\" name=\"alias\" type=\"text\" style=\"width:300px\" />";
				$html .= "<a href=\"".htmlspecialchars($entryUri, ENT_QUOTES, "UTF-8")."\" target=\"_blank\">確認</a>";
				$html .= "</div>";
			}
		}
		return $html;
	}
	
	function getEntry($entryId){
		try{
			$dao = SOY2DAOFactory::create("cms.EntryDAO");
			$entry = $dao->getById($entryId);
		}catch(Exception $e){
			return null;
		}
		return $entry;
	}
	
	function getAlias($entryId){
		$entry = $this->getEntry($entryId);
		if($entry){
			return $entry->getAlias();
		}else{
			return $entryId;
		}
	}
	
	function getBlogPage($pageId){
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
    	try{
    		$page = $dao->getById($pageId);
    	}catch(Exception $e){
    		return null;
    	}
    	return $page;
	}
	
	function setUseId($useId){
		$this->useId = $useId;
	}
	function setPrefix($prefix){
		$this->prefix = $prefix;
	}
	function setPostfix($postfix){
		$this->postfix = $postfix;
	}

}
?>