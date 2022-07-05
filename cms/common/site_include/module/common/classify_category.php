<?php
function soycms_classify_category($html, $page){

	$obj = $page->create("soycms_classify_category", "HTMLTemplatePage", array(
		"arguments" => array("soycms_classify_category", $html)
	));

	$blogPageId = 0;
	if(!$page instanceof CMSBlogPage){
		switch($page->page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				//何もしない
				break;
			default:
				$template = $page->page->getTemplate();
				if(preg_match('/(<[^>]*[^\/]cms:module=\"common.classify_category\"[^>]*>)/', $template, $tmp)){
					if(preg_match('/cms:blog=\"(.*?)\"/', $tmp[1], $ctmp)){
						if(isset($ctmp[1]) && is_numeric($ctmp[1])) $blogPageId = (int)$ctmp[1];
					}
				}
			}
	}else{
		$blogPageId = $page->page->getId();
	}
	
	$blog = soycms_get_blog_page_object($blogPageId);

	//b_block:id="category"
	$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
	$labels = $labelDao->get();//表示順に並んでいる

	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

	$blogLabelId = $blog->getBlogLabelId();

	//カテゴリリンク
	$categories = $blog->getCategoryLabelList();
	$categoryLabels = array();
	$entryCount = array();
	foreach($labels as $labelId => $label){
		if(in_array($labelId, $categories)){
			$categoryLabels[] =	$label;
			try{
				//記事の数を数える。
				$counts = $logic->getOpenEntryCountByLabelIds(array_unique(array($blogLabelId,$labelId)));
			}catch(Exception $e){
				$counts= 0;
			}
			$entryCount[$labelId] = $counts;
		}
	}

	$classifies = array();
	$classifyList = array();
	$classifyList[] = "";	//分類なし
	foreach($categoryLabels as $categoryLabel){
		if(is_numeric(strpos($categoryLabel->getCaption(), "/"))){
			$div = explode("/", $categoryLabel->getCaption());
			$classify = trim($div[0]);
			$idx = array_search($classify, $classifyList);
			if(is_bool($idx)){
				$classifyList[] = $classify;
				$idx = count($classifyList) - 1;
			}
		}else{
			$idx = 0;
		}
		$classifies[$idx][] = $categoryLabel;
	}
	
	if(!class_exists("CategoryListComponent")) SOY2::import("site_include.blog.component.CategoryListComponent");
	$obj->createAdd("classify_category", "CategoryParentListComponent", array(
		"list" => $classifies,
		"classifyList" => $classifyList,
		"entryCount" => $entryCount,
		"blog" => $blog,
		"soy2prefix" => "b_block"
	));
	
    $obj->display();
}

class CategoryParentListComponent extends HTMLList {

	private $classifyList;
	private $entryCount = array();
	private $blog;

	protected function populateItem($entity, $classifyIdx){
		$this->addLabel("classify", array(
			"soy2prefix" => "cms",
			"text" => (is_numeric($classifyIdx) && isset($this->classifyList[$classifyIdx])) ? $this->classifyList[$classifyIdx] : ""
		));
		
		$this->createAdd("category_list", "CategoryListComponent", array(
			"list" => $entity,
			"entryCount" => $this->entryCount,
			"categoryUrl" => convertUrlOnModuleBlogParts($this->blog->getCategoryPageURL(true)),
			"soy2prefix" => "b_block"
		));
	}

	function setClassifyList($classifyList){
		$this->classifyList = $classifyList;
	}
	function setEntryCount($entryCount){
		$this->entryCount = $entryCount;
	}
	function setBlog($blog){
		$this->blog = $blog;
	}
}

if(!function_exists("convertUrlOnModuleBlogParts")){
	/**
	 * @param string
	 * @return string
	 */
	function convertUrlOnModuleBlogParts(string $url){
		static $siteUrl;
		if(is_null($siteUrl)){
			if(defined("SOYCMS_SITE_ID")){
				$siteId = SOYCMS_SITE_ID;
			}else{
				//SOY CMSの場合
				if(defined("_SITE_ROOT_")){
					$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
				}else{
					$siteId = UserInfoUtil::getSite()->getSiteId();
				}
			}
	
			$old = CMSUtil::switchDsn();
			try{
				$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
			}catch(Exception $e){
				$site = new Site();
			}
			CMSUtil::resetDsn($old);
			$siteUrl = "/";
			if(!$site->getIsDomainRoot()) $siteUrl .= $site->getSiteId() . "/";
		}
		return $siteUrl . $url;
	}	
}
