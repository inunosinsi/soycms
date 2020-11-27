<?php

class ListPage extends CMSWebPageBase{

	function __construct() {
		parent::__construct();

		$list = $this->run("Blog.BlogListAction")->getAttribute("list");

		$this->createAdd("page_list","_component.Blog.BlogPageListComponent",array(
			"list" => $list
		));

		$cnt = count($list);
		$this->addModel("exists_blog_page", array(
			"visible"=> ($cnt > 0)
		));
		$this->addModel("no_blog_message", array(
			"visible"=> ($cnt === 0)
		));
		$this->addModel("link_to_create_blog", array(
			"visible"=> ($cnt === 0 && UserInfoUtil::hasSiteAdminRole())
		));

		HTMLHead::addLink("page_list",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/soycms/pagelist.css")
		));

		//ページテンプレート管理
		DisplayPlugin::toggle("is_page_template_enabled", CMSUtil::isPageTemplateEnabled());
	}
}
