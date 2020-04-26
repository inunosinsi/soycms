<?php

CustomAliasPlugin::register();

class CustomAliasPlugin{

	const PLUGIN_ID = "CustomAlias";
	private $useId;
 	private $prefix;
	private $postfix;
	private $mode;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"カスタムエイリアス",
			"description"=>"ブログの記事ページのURLの記事毎に変わる部分（エイリアス）を指定できるようにします。<br>SOY CMS 1.2.4以上で動作します。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.6"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.custom_alias.util.CustomAliasUtil");
			CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent("onEntryCopy", self::PLUGIN_ID, array($this, "onEntryCopy"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
		}
	}

	function onEntryCopy($ids){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$newId = $ids[1];
				$entry = CustomAliasUtil::getEntryById($newId);
				$alias = self::_generateAlias($entry, $mode);
				if(strlen($alias)){
					$entry->setAlias($alias);
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					$logic->update($entry);
				}
				break;
			default:
				//何もしない
		}
	}

	function onEntryUpdate($arg){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$entry = &$arg["entry"];
				$alias = self::_generateAlias($entry, $mode);
				if(strlen($alias)){
					$entry->setAlias($alias);
					$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
					$logic->update($entry);
				}
				break;
			default:
				//何もしない
		}
	}

	private function _generateAlias(Entry $entry, $mode){
		switch($mode){
			case CustomAliasUtil::MODE_ID:
				if($entry->isEmptyAlias() || (is_numeric($entry->getId()) && $entry->getId() != $entry->getAlias())){
				 	return $entry->getId();
				}
				break;
			case CustomAliasUtil::MODE_HASH:
				// @ToDo ハッシュ関数を選択できるようにしたい
				return md5($entry->getTitle());
		}
		return null;
	}

	function onCallCustomField(){
		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				return "";
			default:
				$arg = SOY2PageController::getArguments();
				$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;

				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasFormComponent");
				return CustomAliasFormComponent::buildForm($mode, $entryId);
		}
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
		$page = CustomAliasUtil::getBlogPageById($pageId);
		if(is_null($page->getId())) return "";

		$entryPageUri = CMSUtil::getSiteUrl().$page->getEntryPageURL();
		$entryId = (isset($arg[1]) && is_numeric($arg[1])) ? (int)$arg[1] : null;

		$mode = self::_mode();
		switch($mode){
			case CustomAliasUtil::MODE_ID:
			case CustomAliasUtil::MODE_HASH:
				$alias = CustomAliasUtil::getAliasById($entryId);
				if(!strlen($alias)) return "";
				//確認用のURLだけ表示しておく
				$html = array();
				$html[] = "<div class=\"form-group\">";
				$html[] = "<label for=\"custom_alias_input\">カスタムエイリアス</label><br>";
				$html[] = $entryPageUri . $alias . " ";
				$html[] = "<a href=\"".htmlspecialchars($entryPageUri.rawurlencode($alias), ENT_QUOTES, "UTF-8")."\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-primary\">確認</a>";
				$html[] = "</div>";

				return implode("\n", $html);
			default:
				SOY2::import("site_include.plugin.custom_alias.component.CustomAliasFormComponent");
				return CustomAliasFormComponent::buildForm($mode, $entryId, $entryPageUri);
		}
	}

	//互換性を持たせる為のヘルパー
	private function _mode(){
		if(is_null($this->mode)){
			if($this->useId) return CustomAliasUtil::MODE_ID;
			return CustomAliasUtil::MODE_MANUAL;
		}
		return $this->mode;
	}

	function config_page($message){
		SOY2::import("site_include.plugin.custom_alias.config.CustomAliasPluginFormPage");
		$form = SOY2HTMLFactory::createInstance("CustomAliasPluginFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getUseId(){
		return $this->useId;
	}
	function setUseId($useId){
		$this->useId = $useId;
	}

	function getPrefix(){
		return $this->prefix;
	}
	function setPrefix($prefix){
		$this->prefix = $prefix;
	}

	function getPostfix(){
		return $this->postfix;
	}
	function setPostfix($postfix){
		$this->postfix = $postfix;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomAliasPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj, "init"));
	}
}
