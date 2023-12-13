<?php
class GoogleAnalyticsPluginConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(isset($_POST["google_analytics_track_code"])){
			$this->pluginObj->google_analytics_track_code = $_POST["google_analytics_track_code"];
		}
		if(isset($_POST["google_analytics_track_code_mobile"])){
			$this->pluginObj->google_analytics_track_code_mobile = $_POST["google_analytics_track_code_mobile"];
		}
		if(isset($_POST["google_analytics_track_code_smartphone"])){
			$this->pluginObj->google_analytics_track_code_smartphone = $_POST["google_analytics_track_code_smartphone"];
		}
		if(isset($_POST["google_analytics_global_site_tag"])){
			$this->pluginObj->google_analytics_global_site_tag = $_POST["google_analytics_global_site_tag"];
		}
		if(isset($_POST["google_analytics_global_site_tag_conversion_tag"])){
			$this->pluginObj->google_analytics_global_site_tag_conversion_tag = $_POST["google_analytics_global_site_tag_conversion_tag"];
		}
		if(isset($_POST["google_analytics_gtm_header"])){
			$this->pluginObj->google_analytics_gtm_header = $_POST["google_analytics_gtm_header"];
		}
		if(isset($_POST["google_analytics_gtm_body"])){
			$this->pluginObj->google_analytics_gtm_body = $_POST["google_analytics_gtm_body"];
		}
		
		if(isset($_POST["google_analytics_position"])){
			$this->pluginObj->position = $_POST["google_analytics_position"];
		}
		if(isset($_POST["config_per_page"])){
			$this->pluginObj->config_per_page = $_POST["config_per_page"];
		}
		if(isset($_POST["config_per_blog"])){
			$this->pluginObj->config_per_blog = $_POST["config_per_blog"];
		}


		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig(GoogleAnalytics::PLUGIN_ID,$this->pluginObj);
		CMSPlugin::redirectConfigPage();

	}

	function execute(){
		parent::__construct();

		//1.2.7以上ではページ毎の設定が可能
		$this->createAdd("config_per_page_panel","HTMLModel",array(
			"visible"  => $this->pluginObj->isConfigPerPageEnabled()
		));

		//PC用
		$this->createAdd("google_analytics_track_code","HTMLTextArea",array(
			"text"  => $this->pluginObj->google_analytics_track_code,
			"style" => "display:block; margin:0; width:90%; height:250px;"
		));

		//UtilMobileCheckPluginとの連携
		$this->addModel("has_util_mobile_check",array(
			"visible" => class_exists("UtilMobileCheckPlugin"),
		));

		//モバイル用
		$this->createAdd("google_analytics_track_code_mobile","HTMLInput",array(
			"name" => "google_analytics_track_code_mobile",
			"value"  => $this->pluginObj->google_analytics_track_code_mobile,
			"style" => "margin:0 0 2px; width:90%;"
		));

		//スマホ用
		$this->createAdd("google_analytics_track_code_smartphone","HTMLTextArea",array(
			"text"  => $this->pluginObj->google_analytics_track_code_smartphone,
			"style" => "display:block; margin:0; width:90%; height:250px;"
		));

		//グローバルサイトタグ(gtag.js)
		$this->addTextArea("google_analytics_global_site_tag", array(
			"text" => $this->pluginObj->google_analytics_global_site_tag,
			"style" => "display:block; margin:0; width:90%; height:200px;"
		));

		$this->addTextArea("google_analytics_global_site_tag_conversion_tag", array(
			"text" => $this->pluginObj->google_analytics_global_site_tag_conversion_tag,
			"style" => "display:block; margin:0; width:90%; height:80px;"
		));

		$this->addTextArea("google_analytics_gtm_header", array(
			"name" => "google_analytics_gtm_header",
			"value" => $this->pluginObj->google_analytics_gtm_header,
			"style" => "display:block; margin:0; width:90%; height:120px;"
		));

		$this->addTextArea("google_analytics_gtm_body", array(
			"name" => "google_analytics_gtm_body",
			"value" => $this->pluginObj->google_analytics_gtm_body,
			"style" => "display:block; margin:0; width:90%; height:120px;"
		));
		
		//挿入箇所の指定
		$this->createAdd("insert_into_the_beginning_of_head","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_INTO_THE_BEGINNING_OF_HEAD,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_INTO_THE_BEGINNING_OF_HEAD),
			"name"  => "google_analytics_position",
			"label" => "<head>タグの直後に挿入する"
		));
		$this->createAdd("insert_into_the_end_of_head","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_INTO_THE_END_OF_HEAD,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_INTO_THE_END_OF_HEAD),
			"name"  => "google_analytics_position",
			"label" => "</head>タグの直前に挿入する"
		));
		$this->createAdd("insert_into_the_beginning_of_body","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_INTO_THE_BEGINNING_OF_BODY,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_INTO_THE_BEGINNING_OF_BODY),
			"name"  => "google_analytics_position",
			"label" => "<body>タグの直後に挿入する"
		));
		$this->createAdd("insert_into_the_end_of_body","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_INTO_THE_END_OF_BODY,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_INTO_THE_END_OF_BODY),
			"name"  => "google_analytics_position",
			"label" => "</body>タグの直前に挿入する"
		));
		$this->createAdd("insert_after_the_end_of_body","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_AFTER_THE_END_OF_BODY,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_AFTER_THE_END_OF_BODY),
			"name"  => "google_analytics_position",
			"label" => "</body>タグの直後に挿入する"
		));

		$this->createAdd("insert_into_the_end_of_html","HTMLCheckBox",array(
			"value" => GoogleAnalytics::INSERT_INTO_THE_END_OF_HTML,
			"selected" => ($this->pluginObj->position == GoogleAnalytics::INSERT_INTO_THE_END_OF_HTML),
			"name"  => "google_analytics_position",
			"label" => "HTMLの末尾に追加"
		));

		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
		//SOY2HTMLFactory::importWebPage("CMSBlogPage");

		$this->createAdd("page_list","PageList",array(
			"list"  => $this->getPages(),
			"pluginObj" => $this->pluginObj
		));
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	$list = $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");

    	return $list;

	}
}

class PageList extends HTMLList{

	private $pluginObj;

	function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->createAdd("page_item","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$id."]",
			"value"    => 0,
			"selected" => ! @$this->pluginObj->config_per_page[$id],
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})",
			"class"    => ( ($entity->getPageType() == Page::PAGE_TYPE_BLOG ) ? "blog" : "" ),
			"elementId"=> "blog-{$id}",
			"onclick"  => "update_blog_pages('blog-{$id}');"
		));

		$this->createAdd("for_blog_page","HTMLModel",array(
			"visible" => $entity->isBlog()
		));
		$this->createAdd("blog_top","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 0,
			"selected" => ! @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_TOP],
			"label"    => "トップページ",
			"elementId"=> "blog-{$id}-top"
		));
		$this->createAdd("blog_month","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 0,
			"selected" => ! @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_MONTH_ARCHIVE],
			"label"    => "月別アーカイブページ",
			"elementId"=> "blog-{$id}-month"
		));
		$this->createAdd("blog_category","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 0,
			"selected" => ! @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_CATEGORY_ARCHIVE],
			"label"    => "カテゴリーアーカイブページ",
			"elementId"=> "blog-{$id}-category"
		));
		$this->createAdd("blog_entry","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 0,
			"selected" => ! @$this->pluginObj->config_per_blog[$id][CMSBlogPage::MODE_ENTRY],
			"label"    => "記事毎ページ",
			"elementId"=> "blog-{$id}-entry"
		));


		//hidden
		$this->createAdd("page_item_hidden","HTMLInput",array(
			"type"     => "hidden",
			"name"     => "config_per_page[".$id."]",
			"value"    => 1,
		));
		$this->createAdd("blog_top_hidden","HTMLInput",array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_TOP."]",
			"value"    => 1,
		));
		$this->createAdd("blog_month_hidden","HTMLInput",array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_MONTH_ARCHIVE."]",
			"value"    => 1,
		));
		$this->createAdd("blog_category_hidden","HTMLInput",array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_CATEGORY_ARCHIVE."]",
			"value"    => 1,
		));
		$this->createAdd("blog_entry_hidden","HTMLInput",array(
			"type"     => "hidden",
			"name"     => "config_per_blog[".$id."][".CMSBlogPage::MODE_ENTRY."]",
			"value"    => 1,
		));

	}


	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
