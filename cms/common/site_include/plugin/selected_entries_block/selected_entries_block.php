<?php

SelectedEntriesBlockPlugin::register();

class SelectedEntriesBlockPlugin{

	const PLUGIN_ID = "selected_entries_block";

	private $itemName = "記事一覧選択表示ブロックプラグインで表示する記事";	//カスタムフィールドで表示する項目名

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "記事一覧選択表示ブロックプラグイン",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "http://saitodev.co",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.5"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
                $this,"config_page"
            ));

			SOY2::import("site_include.plugin.selected_entries_block.util.SelectedEntriesBlockUtil");

			//管理側のみ
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));

				SOY2::import("site_include.plugin.selected_entries_block.component.SelectedEntriesCustomFieldForm");
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}

			CMSPlugin::setEvent('onPluginBlockLoad', self::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId', self::PLUGIN_ID, array($this, "returnPluginId"));
		}
	}

	function onEntryUpdate($arg){
		$entryId = $arg["entry"]->getId();
		$isCheck = (isset($_POST[SelectedEntriesBlockUtil::FIELD_ID]));

		SelectedEntriesBlockUtil::save($entryId, $isCheck);
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return SelectedEntriesCustomFieldForm::buildForm($entryId, $this->getItemName());
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return SelectedEntriesCustomFieldForm::buildForm($entryId, $this->getItemName());
	}

	function onLoad(){
		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");

		//記事の取得件数指定
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
		$count = PluginBlockUtil::getLimitByPageId($pageId);

		$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
        $sql = "SELECT ent.* FROM Entry ent ".
             "INNER JOIN EntryAttribute attr ".
             "ON ent.id = attr.entry_id ".
             "WHERE attr.entry_field_id = '" . SelectedEntriesBlockUtil::FIELD_ID . "' ".
			 "AND ent.openPeriodStart < " . time() . " ".
             "AND ent.openPeriodEnd >= " .time() . " ".
             "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
			 "ORDER BY ent.cdate desc ";

		if(is_numeric($count) && $count > 0){
			$sql .= "LIMIT " . $count;
		}

		try{
			$res = $entryDao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$entries = array();
		foreach($res as $v){
			$entries[$v["id"]] = $entryDao->getObject($v);
		}

        return $entries;
    }

    function returnPluginId(){
        return self::PLUGIN_ID;
    }


	/**
	 * 設定画面の表示
	 */
	function config_page($message){
        SOY2::import("site_include.plugin.selected_entries_block.config.SelectedEntriesBlockConfigPage");
        $form = SOY2HTMLFactory::createInstance("SelectedEntriesBlockConfigPage");
        $form->setPluginObj($this);
        $form->execute();
        return $form->getObject();
	}

	function getItemName(){
		return $this->itemName;
	}

	function setItemName($itemName){
		$this->itemName = $itemName;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new SelectedEntriesBlockPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
