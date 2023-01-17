<?php

ReadEntryCountBlockPlugin::register();

class ReadEntryCountBlockPlugin{

	const PLUGIN_ID = "ReadEntryCountBlockPlugin";
	private $limit = 5;
	private $moduleOnlyMode = 0;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ブロックプラグイン版よく読まれている記事プラグイン",
			"type" => Plugin::TYPE_BLOCK,
			"description"=>"記事のPVをSOY CMS側で記録して、公開ページでよく読まれている記事一覧を出力します。",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.0.0"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.read_entry_count.util.ReadEntryCountUtil");

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "display"));
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this,"onPageOutput"));
			}

			CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		
		// view_count用の値を一気に取得
		ReadEntryCountUtil::setReadEntryCountByEntryIds($entryIds);
	}

	function display($arg){
		$entryId = (isset($arg["entryId"])) ? (int)$arg["entryId"] : 0;
		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addLabel("view_count", array(
			"soy2prefix" => "cms",
			"text" => ($entryId > 0) ? ReadEntryCountUtil::getReadEntryCountByEntryId($entryId) : 0
		));
	}
	/**
	 * 公開側の出力
	 */
	function onPageOutput($obj){
		//ブログの記事ページを開いた時のみ集計
		SOY2::import('site_include.CMSBlogPage');
		if(($obj instanceof CMSBlogPage) && SOYCMS_BLOG_PAGE_MODE == CMSBlogPage::MODE_ENTRY && is_numeric($obj->entry->getId())){
			ReadEntryCountUtil::aggregate($obj->entry->getId());
		}
	}

	function onLoad(){
		$blogPageList = ReadEntryCountUtil::getBlogPageList();
		if(!count($blogPageList)) return array();

		$now = time();
		$blogLabelIds = array_keys($blogPageList);

		$entryDao = soycms_get_hash_table_dao("entry");
		$sql = "SELECT ent.* FROM ReadEntryCount cnt ".
				"INNER JOIN Entry ent ".
				"ON cnt.entry_id = ent.id ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND ent.openPeriodStart < " . $now . " ".
				"AND ent.openPeriodEnd > " . $now . " ".
				"AND lab.label_id IN (" . implode(",", $blogLabelIds) . ") ".
				"ORDER BY cnt.count DESC ";
		if(is_numeric($this->limit) && $this->limit > 0){
			$sql .= "LIMIT " . $this->limit;
		}

		try{
			$res = $entryDao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$entries = array();
		foreach($res as $v){
			$entries[] = soycms_set_entry_object($entryDao->getObject($v));
		}

		return $entries;
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	function config_page(){
		SOY2::import("site_include.plugin.read_entry_count.config.ReadEntryCountConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReadEntryCountConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM ReadEntryCount", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$sqls = preg_split('/create/', file_get_contents(dirname(dirname(__FILE__)) . "/read_entry_count/sql/init_".SOYCMS_DB_TYPE.".sql"), -1, PREG_SPLIT_NO_EMPTY) ;
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

	function getLimit(){
		return $this->limit;
	}
	function setLimit($limit){
		$this->limit = $limit;
	}

	function getModuleOnlyMode(){
		return $this->moduleOnlyMode;
	}
	function setModuleOnlyMode($moduleOnlyMode){
		$this->moduleOnlyMode = $moduleOnlyMode;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new ReadEntryCountBlockPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
