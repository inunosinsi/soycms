<?php
SOY2::import("site_include.CMSBlogPage");
class PageListComponent extends HTMLList{

	private $pluginObj;

	function populateItem($entity){

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$entity->getId()."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_page[$entity->getId()])),
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
			"selected" => (isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_TOP])),
			"label"    => "トップページ",
			"elementId"=> "blog-{$entity->getId()}-top"
		));
		$this->addCheckBox("blog_month", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_MONTH_ARCHIVE])),
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-month"
		));
		$this->addCheckBox("blog_category", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_CATEGORY_ARCHIVE])),
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$entity->getId()}-category"
		));
		$this->addCheckBox("blog_entry",array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$entity->getId()."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_blog[$entity->getId()][CMSBlogPage::MODE_ENTRY])),
			"label"    => "記事毎ページ",
			"elementId"=> "blog-{$entity->getId()}-entry"
		));

		if(!is_numeric($entity->getPageType())) return false;
		if($entity->getPageType() == Page::PAGE_TYPE_ERROR || $entity->getPageType() == Page::PAGE_TYPE_APPLICATION) return false;
	}


	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
