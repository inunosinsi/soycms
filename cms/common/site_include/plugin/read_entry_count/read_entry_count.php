<?php

ReadEntryCountPlugin::register();

class ReadEntryCountPlugin{

	const PLUGIN_ID = "ReadEntryCount";
	private $limit = 5;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"よく読まれている記事プラグイン",
			"description"=>"記事のPVをSOY CMS側で記録して、公開ページでよく読まれている記事一覧を出力します。",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.9"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				//
			//公開側
			}else{
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "display"));
				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this,"onPageOutput"));
			}

		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	function display($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addLabel("view_count", array(
			"soy2prefix" => "cms",
			"text" => (isset($entryId) && is_numeric($entryId)) ? self::_getReadEntryCountObject($entryId)->getCount() : 0//self::getReadEntryCountObject($entryId)->getCount()
		));
	}
	/**
	 * 公開側の出力
	 */
	function onPageOutput($obj){

		//ブログの記事ページを開いた時のみ集計
		SOY2::import('site_include.CMSBlogPage');
		if(($obj instanceof CMSBlogPage) && $obj->mode == CMSBlogPage::MODE_ENTRY && !is_null($obj->entry->getId())){
			self::_aggregate($obj->entry->getId());
		}

		SOY2::imports("site_include.plugin.read_entry_count.component.*");
		$obj->createAdd("read_entry_ranking_list", "ReadEntryRankingListComponent", array(
			"soy2prefix" => "p_block",
			"list" => self::get(),
			"blogs" => self::getBlogPageList(),
			"entryDao" => SOY2DAOFactory::create("cms.EntryDAO")
		));

		if(($obj instanceof CMSBlogPage) && ($obj->mode == CMSBlogPage::MODE_ENTRY || $obj->mode == CMSBlogPage::MODE_CATEGORY_ARCHIVE || $obj->mode == CMSBlogPage::MODE_MONTH_ARCHIVE)){
			$labelIds = array();
			$blogPageId = null;
			switch($obj->mode){
				case CMSBlogPage::MODE_ENTRY:
					$labels = $obj->entry->getLabels();
					if(count($labels)){
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
				"list" => self::getByLabelIds($labelIds, $blogPageId),
				"blogs" => self::getBlogPageList(),
				"entryDao" => SOY2DAOFactory::create("cms.EntryDAO")
			));
		}
	}

	private function _aggregate($entryId){
		$obj = self::_getReadEntryCountObject($entryId);
		$cnt = (int)$obj->getCount();
		$cnt++;
		$obj->setCount($cnt);
		self::save($obj);
	}

	private function _getReadEntryCountObject($entryId){
		static $list;
		if(is_null($list)) $list = array();
		if(isset($list[$entryId])) return $list[$entryId];
		try{
			$list[$entryId] = self::dao()->getByEntryId($entryId);
		}catch(Exception $e){
			$obj = new ReadEntryCount();
			$obj->setEntryId($entryId);
			$list[$entryId] = $obj;
		}
		return $list[$entryId];
	}

	private function save(ReadEntryCount $obj){
		try{
			self::dao()->insert($obj);
		}catch(Exception $e){
			try{
				self::dao()->update($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	private function get(){
		//無駄な処理になるけれども、直前で再度DAOクラスを読み込むことにした
		SOY2::imports("site_include.plugin.read_entry_count.domain.*");
		try{
			return SOY2DAOFactory::create("ReadEntryCountDAO")->getRanking($this->limit);
		}catch(Exception $e){
			return array();
		}
	}

	private function getByLabelIds($labelIds, $blogPageId){
		if(!count($labelIds)) return self::get();
		try{
			return SOY2DAOFactory::create("ReadEntryCountDAO")->getRankingByLabelIds($labelIds, $blogPageId, $this->limit);
		}catch(Exception $e){
			return array();
		}
	}

	private function getBlogPageList(){
		static $list;
		if(is_null($list)){
			$list = array();
			try{
				$pages = SOY2DAOFactory::create("cms.BlogPageDAO")->get();
			}catch(Exception $e){
				return array();
			}
			if(!count($pages)) return array();

			$url = self::getUrl();

			$list = array();
			foreach($pages as $pageId => $page){
				if(strlen($page->getUri())){
					$list[$page->getBlogLabelId()] = $url . $page->getUri() . "/" . $page->getEntryPageUri() . "/";
				//ページのURLが空文字の場合
				}else{
					$list[$page->getBlogLabelId()] = $url . $page->getEntryPageUri() . "/";
				}
			}
		}

		return $list;
	}

	private function getUrl(){
		$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
		$old = CMSUtil::switchDsn();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		CMSUtil::resetDsn($old);
		$url = "/";
		if(!$site->getIsDomainRoot()) $url .= $site->getSiteId() . "/";

		return $url;
	}

	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM ReadEntryCount", array());
			return;//テーブル作成済み
		}catch(Exception $e){

		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

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

	private function dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::imports("site_include.plugin.read_entry_count.domain.*");
			$dao = SOY2DAOFactory::create("ReadEntryCountDAO");
		}
		return $dao;
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

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new ReadEntryCountPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
