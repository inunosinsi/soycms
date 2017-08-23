<?php

class InsertBlogLinkPage extends CMSWebPageBase{

	private $pageId;

	function __construct($arg) {

		$this->pageId = @$arg[0];
		if(isset($arg[1])){
			$old = $this->changeDsn();
			$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");

			$site = $logic->getById($arg[1]);
			if(is_null($site)){
				$site = new Site();
			}

			$this->changeDsn($arg[1]);

		}else{
			$site = UserInfoUtil::getSite();
		}


		parent::__construct();
		list($page,$obj) = $this->getPageObject();
		$labels = $this->getCategoryList();
		$entries = $this->getEntryList();

		//表示しないものは選択肢に出さない
		if(!$obj->getGenerateTopFlag())DisplayPlugin::hide("show_top");
		if(!$obj->getGenerateEntryFlag() || !count($entries))DisplayPlugin::hide("show_entry");
		if(!$obj->getGenerateCategoryFlag() || !count($labels) || !count($entries))DisplayPlugin::hide("show_category");
		if(!$obj->getGenerateMonthFlag() || !count($entries))DisplayPlugin::hide("show_archive");
		if(!$obj->getGenerateRssFlag())DisplayPlugin::hide("show_feed");

		$this->createAdd("category_list","HTMLSelect",array(
			"options"=>$labels,
			"indexOrder"=>true,
			"property"=>"caption",
		));
		$this->createAdd("entry_list","HTMLSelect",array(
			"options"=>$entries,
			"indexOrder"=>true,
			"property"=>"title"
		));
		$this->createAdd("month_today","HTMLInput",array(
			"value"=>date('Y-m')
		));


		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->createAdd("otherdata","HTMLScript",array(
			"type" => "text/JavaScript",
			"script" => 'var page = '.json_encode($page).';'.
			'var back_link = "'.SOY2PageController::createLink('Page.Editor.InsertLink').'";'.
			'var siteId = "'.((isset($arg[1]))? $arg[1]: '').'";'
		));

		$this->createAdd("page_url","HTMLLabel",array(
			"text"=>'URL：'.$site->getUrl().$obj->getUri()
		));

		$this->createAdd("page_title","HTMLLabel",array(
			"text"=>CMSMessageManager::get("SOYCMS_BLOG_TITLE").':'.$obj->getTitle()
		));
		$this->createAdd("blog_title","HTMLLabel",array(
			"text"=>$obj->getTitle()
		));

		if(is_array(@$old)){
			$this->restoreDsn($old);
		}
	}

	function getPageObject(){
		$page = $this->run("Blog.DetailAction",array("id"=>$this->pageId))->getAttribute("Page");
		$retArray = array();
		$retArray[] = $page->getId();
		$retArray[] = $page->getUri();


		return array($retArray,$page);
	}

	function getCategoryList(){
		return $this->run("Blog.CategoryListAction",array("pageId"=>$this->pageId))->getAttribute("categoryLabels");
	}

	function getEntryList(){
		return $this->run("Blog.EntryListAction",array("pageId"=>$this->pageId))->getAttribute("entries");
	}

	function changeDsn($siteId = null){
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		if(is_null($siteId)){
			return array(
			"dsn"=>$oldDsn,
			"user"=>$oldUser,
			"pass"=>$oldPass
			);
		}

		try{
			$dao = SOY2DAOFactory::create("admin.SiteDAO");
			$site = $dao->getById($siteId);
			$this->siteRoot = $site->getUrl();

			SOY2DAOConfig::Dsn($site->getDataSourceName());
		}catch(Exception $e){

		}

		return array(
			"dsn"=>$oldDsn,
			"user"=>$oldUser,
			"pass"=>$oldPass
		);
	}

	function restoreDsn($array){
		SOY2DAOConfig::Dsn($array["dsn"]);
		SOY2DAOConfig::user($array["user"]);
		SOY2DAOConfig::pass($array["pass"]);
	}
}
