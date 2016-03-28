<?php

class ButtonSocialConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function ButtonSocialConfigFormPage(){
		
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			
			$this->pluginObj->setAppId($_POST["Config"]["app_id"]);
			$this->pluginObj->setAdmins($_POST["Config"]["admins"]);
			$this->pluginObj->setDescription($_POST["Config"]["description"]);
			$this->pluginObj->setImage($_POST["Config"]["image"]);
			$this->pluginObj->setMixiCheckKey($_POST["Config"]["mixi_check_key"]);
			$this->pluginObj->setMixiLikeKey($_POST["Config"]["mixi_like_key"]);
			
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
		
		$this->addInput("app_id", array(
			"name" => "Config[app_id]",
			"value" => $this->pluginObj->getAppId(),
			"style" => "ime-mode:inactive;"
		));
		
		$this->addInput("admins", array(
			"name" => "Config[admins]",
			"value" => $this->pluginObj->getAdmins(),
			"style" => "ime-mode:inactive;"
		));
		
		$this->addInput("description", array(
			"name" => "Config[description]",
			"value" => $this->pluginObj->getDescription(),
			"style" => "width:80%"
		));
		
		$this->addInput("image", array(
			"name" => "Config[image]",
			"value" => $this->pluginObj->getImage(),
			"style" => "width:50%;"
		));
		
		$this->addInput("mixi_like_key", array(
			"name" => "Config[mixi_like_key]",
			"value" => $this->pluginObj->getMixiLikeKey(),
			"style" => "ime-mode:inactive;"
		));
		
		$this->addInput("mixi_check_key", array(
			"name" => "Config[mixi_check_key]",
			"value" => $this->pluginObj->getMixiCheckKey(),
			"style" => "ime-mode:inactive;"
		));
		
		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
		
		$this->createAdd("page_list", "PageList", array(
			"list"  => $this->getPages(),
			"pluginObj" => $this->pluginObj
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
			"selected" => (!isset($this->pluginObj->config_per_page[$entity->getId()]) || $this->pluginObj->config_per_page[$entity->getId()] == 1),
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
			"selected" => (!isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_TOP]) || $this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_TOP] == 1),
			"label"    => "トップページ",
			"elementId"=> "blog-{$entity->getId()}-top"
		));
		$this->addCheckBox("blog_month", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
			"selected" => (!isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_MONTH_ARCHIVE]) || $this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_MONTH_ARCHIVE] == 1),
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-month"
		));
		$this->addCheckBox("blog_category", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
			"selected" => (!isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_CATEGORY_ARCHIVE]) || $this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_CATEGORY_ARCHIVE] == 1),
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-category"
		));
		$this->addCheckBox("blog_entry", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
			"selected" => (!isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_ENTRY]) || $this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_ENTRY] == 1),
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