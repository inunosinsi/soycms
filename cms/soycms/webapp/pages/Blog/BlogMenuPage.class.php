<?php

class BlogMenuPage extends CMSHTMLPageBase{

	private $rule = array(
		"Blog" => "blog_top_link",
		"Blog.Entry" => "blog_entry_link",
		"Blog.EntryList" => "blog_entry_list_link",
		"Blog.Config" => "blog_config_link",
		"Blog.Trackback" => "blog_trackback_link",
		"Blog.Comment" => "blog_comment_link",
		"Blog.Template" => "blog_template_link",
	);

	function __construct($args) {

		$id = $args[0];

		parent::__construct();

		$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
		$blog = $dao->getById($id);

		//ラベル設定の有無
		if(is_null($blog->getBlogLabelId())){
			$this->addErrorMessage("SOYCMS_BLOGPAGE_NO_LABEL_FOR_BLOG");
		}

		//パンクズリストの自ブログリンク
		$this->addLink("blog_name_link",array(
				"link" => SOY2PageController::createLink("Blog.".$id),
				"text" => $blog->getTitle(),
		));


		/* ブログメニュー */

		//上部メニューのリンク
		$this->createAdd("blog_top_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.".$id),
				"class" => $this->getMenuStatus("blog_top_link")
		));
		$this->addModel("blog_top_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_top_link"),
				"visible" => (UserInfoUtil::hasEntryPublisherRole())
		));

		$this->createAdd("blog_entry_list_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.EntryList.".$id),
				"class" => $this->getMenuStatus("blog_entry_list_link")
		));
		$this->addModel("blog_entry_list_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_entry_list_link")
		));

		$this->createAdd("blog_config_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Config.".$id),
				"class" => $this->getMenuStatus("blog_config_link"),
				"visible" => (UserInfoUtil::hasSiteAdminRole())
		));
		$this->addModel("blog_config_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_config_link"),
				"visible" => (UserInfoUtil::hasSiteAdminRole())
		));

		$this->createAdd("blog_entry_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Entry.".$id),
				"class" => $this->getMenuStatus("blog_entry_link")
		));
		$this->addModel("blog_entry_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_entry_link")
		));

		$this->createAdd("blog_trackback_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Trackback.".$id),
				"class" => $this->getMenuStatus("blog_trackback_link")
		));
		$this->addModel("blog_trackback_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_trackback_link"),
				"visible" => UserInfoUtil::hasEntryPublisherRole(),//記事公開権限のある場合のみ
		));

		$this->createAdd("blog_comment_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Comment.".$id),
				"class" => $this->getMenuStatus("blog_comment_link")
		));
		$this->addModel("blog_comment_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_comment_link"),
				"visible" => UserInfoUtil::hasEntryPublisherRole(),//記事公開権限のある場合のみ
		));
		$this->createAdd("blog_category_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Category.".$id),
				"class" => $this->getMenuStatus("blog_comment_link")
		));

		$this->addModel("blog_category_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_comment_link"),
				"visible" => !UserInfoUtil::hasSiteAdminRole(),//サイト管理者で無い場合
		));

		$this->createAdd("blog_template_link","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Template.".$id.".top"),
				"class" => $this->getMenuStatus("blog_template_link"),
				"visible" => (UserInfoUtil::hasSiteAdminRole())
		));
		$this->addModel("blog_template_link_wrapper",array(
				"class" => $this->getMenuStatus("blog_template_link"),
				"visible" => (UserInfoUtil::hasSiteAdminRole())
		));

		$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($blog->getUri()) >0) ? $blog->getUri() ."/" : "" ) ;
		if(strlen($blog->getTopPageUri())) $pageUrl = rtrim($pageUrl, "/") . "/" . $blog->getTopPageUri();
		$this->createAdd("blog_confirm","HTMLLink",array(
			"link" => $pageUrl,
			"visible" => $blog->isActive()
		));

		$this->createAdd("blog_preview","HTMLLink",array(
			"link" => SOY2PageController::createLink("Page.Preview.".$id),
		));
	}

	/**
	 * リクエストされたパスから現在のパスを判別し、一致すればactiveを返す
	 * @param String $tabName
	 * @return string
	 */
	private function getMenuStatus($tabName){
		static $activeTab;

		if(!$activeTab){
			$requestPath = SOY2PageController::getRequestPath();

			foreach($this->rule as $rule => $tab){
				if(strpos($rule,$requestPath) === 0){
					$activeTab = $tab;
					break;
				}
			}
		}

		if($tabName == $activeTab){
			return "active";
		}else{
			return "";
		}
	}
}
