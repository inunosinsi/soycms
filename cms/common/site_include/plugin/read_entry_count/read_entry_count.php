<?php

ReadEntryCountPlugin::register();

class ReadEntryCountPlugin{

	const PLUGIN_ID = "ReadEntryCount";
	private $limit = 5;
	private $moduleOnlyMode = 0;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"よく読まれている記事プラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=>"記事のPVをSOY CMS側で記録して、公開ページでよく読まれている記事一覧を出力します。",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.0.3"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//公開側
			if(defined("_SITE_ROOT_")){
				SOY2::import("site_include.plugin.read_entry_count.util.ReadEntryCountUtil");

				CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "display"));
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this,"onPageOutput"));
			}

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
		if(($obj instanceof CMSBlogPage) && SOYCMS_BLOG_PAGE_MODE == CMSBlogPage::MODE_ENTRY && !is_null($obj->entry->getId())){
			ReadEntryCountUtil::aggregate($obj->entry->getId());
		}

		//モジュール版以外
		if((int)$this->moduleOnlyMode !== 1){
			$blogPageList = ReadEntryCountUtil::getBlogPageList();
			SOY2::imports("site_include.plugin.read_entry_count.component.*");
			$obj->createAdd("read_entry_ranking_list", "ReadEntryRankingListComponent", array(
				"soy2prefix" => "p_block",
				"list" => self::_get(),
				"blogs" => $blogPageList
			));

			if(($obj instanceof CMSBlogPage) && (SOYCMS_BLOG_PAGE_MODE == CMSBlogPage::MODE_ENTRY || SOYCMS_BLOG_PAGE_MODE == CMSBlogPage::MODE_CATEGORY_ARCHIVE || SOYCMS_BLOG_PAGE_MODE == CMSBlogPage::MODE_MONTH_ARCHIVE)){
				$labelIds = array();
				$blogPageId = 0;
				switch(SOYCMS_BLOG_PAGE_MODE){
					case CMSBlogPage::MODE_ENTRY:
						$labels = $obj->entry->getLabels();
						if(is_array($labels) && count($labels)){
							foreach($labels as $label){
									$labelIds[] = $label->getId();
							}
							$blogPageId = $obj->page->getBlogLabelId();
						}
						break;
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						$labelIds = $obj->page->getCategoryLabelList();
						$blogPageId = $obj->page->getBlogLabelId();
						break;
					default:
				}

				$obj->createAdd("read_entry_ranking_list_same_category", "ReadEntryRankingListComponent", array(
					"soy2prefix" => "p_block",
					"list" => self::_getByLabelIds($labelIds, $blogPageId),
					"blogs" => $blogPageList
				));
			}
		}
	}

	/**
	 * @return array
	 */
	private function _get(){
		//無駄な処理になるけれども、直前で再度DAOクラスを読み込むことにした
		SOY2::imports("site_include.plugin.read_entry_count.domain.*");
		try{
			return SOY2DAOFactory::create("ReadEntryCountDAO")->getRanking($this->limit);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * @param array, int
	 * @return array
	 */
	private function _getByLabelIds(array $labelIds, int $blogPageId){
		if(!count($labelIds)) return self::_get();
		try{
			return SOY2DAOFactory::create("ReadEntryCountDAO")->getRankingByLabelIds($labelIds, $blogPageId, $this->limit);
		}catch(Exception $e){
			return array();
		}
	}

	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM ReadEntryCount", array());
			return;//テーブル作成済み
		}catch(Exception $e){

		}

		$sqls = preg_split('/create/', file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql"), -1, PREG_SPLIT_NO_EMPTY) ;
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

	function config_page(){
		SOY2::import("site_include.plugin.read_entry_count.config.ReadEntryCountConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReadEntryCountConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
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
		if(!$obj) $obj = new ReadEntryCountPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
