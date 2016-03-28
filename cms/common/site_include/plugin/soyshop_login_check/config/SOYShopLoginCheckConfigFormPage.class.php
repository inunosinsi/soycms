<?php

class SOYShopLoginCheckConfigFormPage extends WebPage{
	
	private $pluginObj;
	private $configLogic;
	
	function SOYShopLoginCheckConfigFormPage(){
		$this->configLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.ConfigLogic");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			//キャッシュを捨てる
			$root = dirname(SOY2::RootDir());
			CMSUtil::unlinkAllIn($root . "/soycms/cache/");
			CMSUtil::unlinkAllIn($root . "/soyshop/cache/");
						
			$this->pluginObj->setSiteId($_POST["Config"]["siteId"]);
			$this->pluginObj->setPoint((int)$_POST["Config"]["point"]);
			
			$isInsertCommentForm = (isset($_POST["Config"]["isInsertCommentForm"]) && $_POST["Config"]["isInsertCommentForm"] == 1);
			$this->pluginObj->setIsInsertCommentForm($isInsertCommentForm);
			
			$allowBrowseEntryByPurchased = (isset($_POST["Config"]["allowBrowseEntryByPurchased"]) && $_POST["Config"]["allowBrowseEntryByPurchased"] == 1) ? 1 : 0;
			$this->pluginObj->setAllowBrowseEntryByPurchased($allowBrowseEntryByPurchased);
				
			$this->pluginObj->setDoRedirectAfterLogin($_POST["Config"]["doRedirectAfterLogin"]);
			$this->pluginObj->setDoRedirectAfterRemind($_POST["Config"]["doRedirectAfterRemind"]);
			
			if(isset($_POST["config_per_page"])){
				$this->pluginObj->config_per_page = $_POST["config_per_page"];
			}
			if(isset($_POST["config_per_blog"])){
				$this->pluginObj->config_per_blog = $_POST["config_per_blog"];
			}
			
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		
		WebPage::WebPage();
				
		$this->addForm("form");
		
		$this->addSelect("shop_list", array(
			"name" => "Config[siteId]",
			"options" => $this->configLogic->getList(),
			"selected" => $this->pluginObj->getSiteId()
		));
		
		$this->addCheckBox("is_insert_comment_form", array(
			"name" => "Config[isInsertCommentForm]",
			"value" => 1,
			"selected" => ($this->pluginObj->getIsInsertCommentForm()),
			"label" => " ログイン中のユーザ情報をコメントフォームに挿入する"
		));
		
		$this->addInput("point", array(
			"name" => "Config[point]",
			"value" => $this->pluginObj->getPoint(),
			"style" => "width: 40px;padding:3px;text-align:right;ime-mode:inactive;"
		));
		
		$this->addInput("do_redirect_after_login_hidden", array(
			"name" => "Config[doRedirectAfterLogin]",
			"value" => 0,
		));
		
		$this->addCheckBox("do_redirect_after_login", array(
			"name" => "Config[doRedirectAfterLogin]",
			"value" => 1,
			"selected" => $this->pluginObj->getDoRedirectAfterLogin(),
			"label" => "ログイン後に元のページに戻る"
		));
		
		$this->addInput("do_redirect_after_remind_hidden", array(
			"name" => "Config[doRedirectAfterRemind]",
			"value" => 0,
		));
		
		$this->addCheckBox("do_redirect_after_remind", array(
			"name" => "Config[doRedirectAfterRemind]",
			"value" => 1,
			"selected" => $this->pluginObj->getDoRedirectAfterRemind(),
			"label" => "パスワードリマインダの送信後に元のページに戻る"
		));
		
		
		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
		
		$this->createAdd("page_list", "PageList", array(
			"list"  => $this->getPages(),
			"pluginObj" => $this->pluginObj
		));
		
		$this->addCheckBox("allow_browse_entry_by_purchased", array(
			"name" => "Config[allowBrowseEntryByPurchased]",
			"value" => 1,
			"selected" => ($this->pluginObj->getAllowBrowseEntryByPurchased() == 1),
			"label" => "記事詳細を開く時に特定の商品を購入していることを条件とする"
		));
	}
	
	function getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction", array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	$list = $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");

    	return $list;
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}

class PageList extends HTMLList{

	private $pluginObj;

	function populateItem($entity){

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$entity->getId()."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_page[$entity->getId()],
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})",
			"class"    => ( ($entity->getPageType() == Page::PAGE_TYPE_BLOG ) ? "blog" : "" ),
			"elementId"=> "blog-{$entity->getId()}",
			"onclick"  => "update_blog_pages('blog-{$entity->getId()}');"
		));

		$this->addModel("for_blog_page", array(
			"visible" => $entity->isBlog()
		));
		$this->addCheckBox("blog_top", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_TOP],
			"label"    => "トップページ",
			"elementId"=> "blog-{$entity->getId()}-top"
		));
		$this->addCheckBox("blog_month", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_MONTH_ARCHIVE],
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-month"
		));
		$this->addCheckBox("blog_category", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_CATEGORY_ARCHIVE],
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-category"
		));
		$this->addCheckBox("blog_entry", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_ENTRY],
			"label"    => "記事毎ページ",
			"elementId"=> "blog-{$entity->getId()}-entry"
		));


		//hidden
		$this->addInput("page_item_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_page[".$entity->getId()."]",
			"value"    => 0,
		));
		$this->addCheckBox("blog_top_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 0,
		));
		$this->addInput("blog_month_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_category_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_entry_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 0,
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
?>