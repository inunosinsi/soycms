<?php
EntryInfoPlugin::register();

class EntryInfoPlugin{

	const PLUGIN_ID = "soycms_entry_info";
	private $mode = 0;


	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブログ記事SEOプラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=>"ブログページの記事毎ページにkeywordとdescriptionを記事投稿時に追加する",
			"author"=>"株式会社Brassica",
			"url"=>"http://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.8.1"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.soycms_entry_info.util.EntryInfoUtil");

			if(!defined("_SITE_ROOT_")){

				//記事作成時にキーワードとdescriptinをDBに挿入する
				CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));

				//記事複製時にキーワードとdescriptionもDBに挿入する
				CMSPlugin::setEvent('onEntryCopy', $this->getId(), array($this, "onEntryCopy"));

				//記事投稿画面にフォームを表示する
				CMSPlugin::addCustomFiledFunction($this->getId(),"Entry.Detail",array($this, "onCallCustomField"));
				CMSPlugin::addCustomFiledFunction($this->getId(),"Blog.Entry", array($this, "onCallCustomField_inBlog"));

			//公開側設定
			}else{
				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput', $this->getId(), array($this,"onPageOutput"));
			}

		}else{
			//CMSPlugin::setEvent('onActive',$this->getId(),array($this,"createTable"));
		}


	}


	/**
	 * @TODO ヘルプを表示
	 */
	function config_page(){
		SOY2::import("site_include.plugin.soycms_entry_info.config.EntryInfoConfigPage");
		$form = SOY2HTMLFactory::createInstance("EntryInfoConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * 記事作成画面にフォームを表示する
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		
		SOY2::import("site_include.plugin.soycms_entry_info.component.EntryInfoCustomFieldFormComponent");
		return EntryInfoCustomFieldFormComponent::build($entryId);
	}

	/**
	 * ブログ記事作成画面にフォームを表示する
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		
		SOY2::import("site_include.plugin.soycms_entry_info.component.EntryInfoCustomFieldFormComponent");
		return EntryInfoCustomFieldFormComponent::build($entryId);
	}


	/**
	 * エントリー更新
	 */
	function onEntryUpdate($arg){
		$keyword = (isset($_POST["keyword"]) && is_string($_POST["keyword"])) ? $_POST["keyword"] : null;;
		$entry = $arg["entry"];
		EntryInfoUtil::save((int)$entry->getId(), $keyword);
	}

	/**
	 * エントリーの複製時
	 */
	function onEntryCopy($args){
		list($oldEntryId, $newEntryId) = $args;
		EntryInfoUtil::save((int)$newEntryId, EntryInfoUtil::getEntryKeyword($oldEntryId));
	}

	/**
	 * 公開側の出力
	 */
	function onPageOutput($obj){

		//ブログではない時 or エントリー表示画面以外では動作しません
		if(!$obj instanceof CMSBlogPage || SOYCMS_BLOG_PAGE_MODE != CMSBlogPage::MODE_ENTRY) return;

		$entry = $obj->entry;
		if(is_null($entry)) $entry = new Entry();

		//データ取得
		$keyword = EntryInfoUtil::getEntryKeyword((int)$entry->getId());
		$dsp = (string)$entry->getDescription();

		if($this->mode == EntryInfoUtil::MODE_REACQUIRE){
			if(!strlen($keyword)) $keyword = EntryInfoUtil::getBlogTopMetaValue(EntryInfoUtil::TYPE_KEYWORD);
			if(!strlen($dsp)) $dsp = EntryInfoUtil::getBlogTopMetaValue(EntryInfoUtil::TYPE_DESCRIPTION);
		}

		$obj->addModel("is_entry_keyword", array(
			"soy2prefix" => "b_block",
			"visible" => (isset($keyword) && strlen($keyword))
		));

		$obj->addModel("entry_keyword", array(
			"soy2prefix" => "b_block",
			"attr:name" => "keywords",
			"attr:content" => $keyword
		));

		$obj->addModel("is_entry_description", array(
			"soy2prefix" => "b_block",
			"visible" => (strlen($dsp))
		));

		$obj->addModel("entry_description", array(
			"soy2prefix" => "b_block",
			"attr:name" => "description",
			"attr:content" => $dsp
		));
	}

	//廃止
	// function createTable(){
	// 	$dao = new SOY2DAO();try{$dao->executeUpdateQuery("alter table Entry add keyword text",array());}catch(Exception $e){//}return;
	// }

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new EntryInfoPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
