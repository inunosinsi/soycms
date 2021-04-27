<?php

class SSLListComponent extends HTMLList{

	private $pluginObj;

	function populateItem($entity){
		$pageId = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "ssl_per_page[".$pageId."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->ssl_per_page[$pageId])) ? $this->pluginObj->ssl_per_page[$pageId] : null,
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})",
			"class"    => ( ($entity->getPageType() == Page::PAGE_TYPE_BLOG ) ? "blog" : "" ),
			"elementId"=> "blog-{$pageId}",
			"onclick"  => "update_blog_pages('blog-{$pageId}');"
		));

		$this->addModel("for_blog_page", array(
			"visible" => $entity->isBlog()
		));
		$this->addCheckBox("blog_top", array(
			"type"     => "checkbox",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_TOP])) ? $this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_TOP] : null,
			"label"    => "トップページ",
			"elementId"=> "blog-{$pageId}-top"
		));
		$this->addCheckBox("blog_month", array(
			"type"     => "checkbox",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
			"selected" =>(isset($this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_MONTH_ARCHIVE])) ? $this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_MONTH_ARCHIVE] : null,
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$pageId}-month"
		));
		$this->addCheckBox("blog_category", array(
			"type"     => "checkbox",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_CATEGORY_ARCHIVE])) ? $this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_CATEGORY_ARCHIVE] : null,
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$pageId}-category"
		));
		$this->addCheckBox("blog_entry", array(
			"type"     => "checkbox",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_ENTRY])) ? $this->pluginObj->ssl_per_blog[$pageId][CMSBlogPage::MODE_ENTRY] : null,
			"label"    => "記事毎ページ",
			"elementId"=> "blog-{$pageId}-entry"
		));


		//hidden
		$this->addInput("page_item_hidden", array(
			"type"     => "hidden",
			"name"     => "ssl_per_page[".$pageId."]",
			"value"    => 0,
		));
		$this->addCheckBox("blog_top_hidden", array(
			"type"     => "hidden",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 0,
		));
		$this->addInput("blog_month_hidden", array(
			"type"     => "hidden",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_category_hidden", array(
			"type"     => "hidden",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_entry_hidden", array(
			"type"     => "hidden",
			"name"     => "ssl_per_blog[".$pageId."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 0,
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
