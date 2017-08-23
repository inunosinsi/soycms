<?php

class InsertLinkPage extends CMSWebPageBase{

	function __construct($arg) {
		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$old = $this->changeDsn();
		$sites = $logic->getSiteOnly();
		if(isset($arg[0])){

			$site = $logic->getById($arg[0]);
			if(is_null($site)){
				$site = new Site();
			}

			$this->changeDsn($arg[0]);
		}else{
			$this->restoreDsn($old);
			$site = new Site();
		}
		parent::__construct();
		$this->createAdd("current_site","HTMLLabel",array(
			"text"=>"(".$site->getSiteName().")",
			"visible"=>isset($arg[0])
		));

		$links = array();
		$links['null_insert_link'] = CMSMessageManager::get("SOYCMS_SELECT_LINK_TYPE");

		foreach($this->getParentPageList() as $key => $page){
			$links[$key] = $page;
		}
		//$links = array_merge($links,$this->getParentPageList());

		$links['foreign_address'] = CMSMessageManager::get("SOYCMS_INSERT_EXTERNAL_LINK");

		//他サイトへのリンクはASPでは使用不可
		if(!defined("SOYCMS_ASP_MODE") && count($sites) >1) $links['foreign_site'] = CMSMessageManager::get("SOYCMS_ANOTHER_SOYCMS_WEBSITE_LINK");

		$this->createAdd("insert_link","HTMLSelect",array(
			"indexOrder"=>true,
			"options"=>$links,
			"name"=>"insert_select",
			"selected"=>"foreign_address"
		));


		$blogList = $this->blogPageList();

		$this->createAdd("otherdata","HTMLScript",array(
			"type" => "text/JavaScript",
			"script" => 'var page_list = '.json_encode($blogList).';' .
					'var blogLinkAddress = "'.SOY2PageController::createLink("Page.Editor.InsertBlogLink").'";' .
					'var siteURL = "'.UserInfoUtil::getSiteURL().'";'.
					'var mobileLinkAddress = "'.SOY2PageController::createLink("Page.Editor.InsertMobileLink").'";'.
					'var foreignLinkAddress = "'.SOY2PageController::createLink("Page.Editor.InsertSiteLink").'";'.
					'var siteId = "'.((isset($arg[0]))? $arg[0]: '').'";'
		));



		$this->createAdd("create_label","HTMLForm");

		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->createAdd("file_manager_iframe","HTMLModel",array(
			"target_src"=>SOY2PageController::createLink("FileManager.File")
		));
		if(is_array(@$old)){
			$this->restoreDsn($old);
		}
	}

	function getParentPageList(){
		return SOY2ActionFactory::createInstance("Page.PageListAction",array(
			"buildTree" => true
		))->run()->getAttribute("PageTree");
	}

	/**
	 * ページのid,uri,title,pagetypeのArrayを作る
	 */
	function blogPageList(){
		$pages =  $this->run("Page.ListAction")->getAttribute("list");

		$ret_val = array();
		foreach($pages as $page){
			$obj = new stdClass();
			$obj->id = $page->getId();
			$obj->title = $page->getTitle();
			$obj->uri = $page->getUri();
			$obj->pageType  = $page->getPageType();
			$ret_val[$page->getId()] = $obj;
		}

		return $ret_val;
	}

	function changeDsn($siteId = null){
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		if($siteId == null){
			return array("dsn"=>$oldDsn,
			"user"=>$oldUser,
			"pass"=>$oldPass);
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
