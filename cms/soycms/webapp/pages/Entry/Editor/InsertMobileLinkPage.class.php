<?php

class InsertMobileLinkPage extends CMSWebPageBase{

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

		if(is_null($this->pageId)){
			//$this->jump("Page");
		}

		$result = $this->run("Page.Mobile.GetMobileDetailPageAction",array("id"=>$this->pageId));

		if(!$result->success()){
			//$this->jump("Page");
		}
		$page = $result->getAttribute("Page");
		parent::__construct();
		$tree = "<div style=\"margin-bottom:10px;\">".$this->buildTree($page->getVirtual_tree())."<br /></div>";

		$this->createAdd("page_tree","HTMLLabel",array(
			"html"=>$tree
		));

		$this->createAdd("otherdata","HTMLScript",array(
			"type" => "text/JavaScript",
			"script" => 'var page_url = "'.UserInfoUtil::getSiteURL().$page->getUri().'";'.
						'var page_id = "'.$page->getId().'";'.
						'var back_link = "'.SOY2PageController::createLink('Entry.Editor.InsertLink').'";'.
						'var siteId = "'.((isset($arg[1]))? $arg[1]: '').'";'
		));

		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));
		$this->createAdd("jqueryuijs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery-ui.min.js")
		));
		$this->createAdd("commonjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/common.js")
		));

		$this->createAdd("page_url","HTMLLabel",array(
			"text"=>'URL：'.UserInfoUtil::getSiteUrl().$page->getUri()
		));

		$this->createAdd("page_title","HTMLLabel",array(
			"text"=>CMSMessageManager::get("SOYCMS_WEBPAGE_TITLE").':'.$page->getTitle()
		));

		if(is_array(@$old)){
			$this->restoreDsn($old);
		}
	}

	function buildTree($virtualTree,$root = 0){
		$current = $virtualTree[$root];
		$html = array();

		$title = $current->getTitle();
		if(strlen($title) == 0) $title = "<i>".CMSMessageManager::get("SOYCMS_NO_TITLE_2")."</i>";
		$html[] = '<input type="radio" name="mobile_link" value="'.$current->getId().'" id="mobile_link_'.$current->getId().'" />';
		$html[] = '<label id="mobile_label_'.$current->getId().'" for="mobile_link_'.$current->getId().'">'.$title.'</label>';


		if(count($current->getChild()) != 0){
			$html[] = '<ul  class="virtual_page_tree">';
			foreach($current->getChild() as $childId){
				$child = @$virtualTree[$childId];
				if(is_null($child)) continue;

				$html[] = '<li>'.$this->buildTree($virtualTree,$childId).'</li>';
			}
			$html[] = '</ul>';
		}
		return implode("\n",$html);
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
