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
			"version"=>"0.4"
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
        //公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
      }

    }else{
      CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
    }
	}

  /**
	 * 公開側の出力
	 */
	function onPageOutput($obj){

		//ブログの記事ページを開いた時のみ集計
		if(($obj instanceof CMSBlogPage) && $obj->mode == CMSBlogPage::MODE_ENTRY && !is_null($obj->entry->getId())){
      $cntObj = self::getReadEntryCountObject($obj->entry->getId());
      $cnt = (int)$cntObj->getCount();
      $cnt++;
      $cntObj->setCount($cnt);
      self::save($cntObj);
    }

		SOY2::imports("site_include.plugin.read_entry_count.component.*");
		$obj->createAdd("read_entry_ranking_list", "ReadEntryRankingListComponent", array(
			"soy2prefix" => "p_block",
			"list" => self::get(),
			"blogs" => self::getBlogPageList(),
			"entryDao" => SOY2DAOFactory::create("cms.EntryDAO")
		));
  }

  private function getReadEntryCountObject($entryId){
    try{
      return self::dao()->getByEntryId($entryId);
    }catch(Exception $e){
      $obj = new ReadEntryCount();
      $obj->setEntryId($entryId);
      return $obj;
    }
  }

  private function save(ReadEntryCount $obj){
    try{
      self::dao()->insert($obj);
    }catch(Exception $e){
      try{
        self::dao()->update($obj);
      }catch(Exception $e){
        var_dump($e);
      }
    }
  }

	private function get(){
		try{
			return self::dao()->getRanking($this->limit);
		}catch(Exception $e){
			return array();
		}
	}

	private function getBlogPageList(){
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
				var_dump($e);
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
		if(!$obj){
			$obj = new ReadEntryCountPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
