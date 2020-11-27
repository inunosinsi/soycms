<?php

class PageListComponent extends HTMLList{

	private $pluginObj;

	function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$id."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_page[$id],
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})",
			"class"    => ( ($entity->getPageType() == Page::PAGE_TYPE_BLOG ) ? "blog" : "" ),
			"elementId"=> "blog-{$id}",
			"onclick"  => "update_blog_pages('blog-{$id}');"
		));

		$this->addModel("for_blog_page", array(
			"visible" => $entity->isBlog()
		));
		$this->addCheckBox("blog_top", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_TOP],
			"label"    => "トップページ",
			"elementId"=> "blog-{$id}-top"
		));
		$this->addCheckBox("blog_month", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_MONTH_ARCHIVE],
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$id}-month"
		));
		$this->addCheckBox("blog_category", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_CATEGORY_ARCHIVE],
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$id}-category"
		));
		$this->addCheckBox("blog_entry", array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
			"selected" => @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_ENTRY],
			"label"    => "記事毎ページ",
			"elementId"=> "blog-{$id}-entry"
		));


		//hidden
		$this->addInput("page_item_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_page[".$id."]",
			"value"    => 0,
		));
		$this->addCheckBox("blog_top_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 0,
		));
		$this->addInput("blog_month_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_category_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 0,
		));
		$this->addInput("blog_entry_hidden", array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 0,
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
