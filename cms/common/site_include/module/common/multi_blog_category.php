<?php
function soycms_multi_blog_category($html, $page){

	$obj = $page->create("multi_blog_category", "HTMLTemplatePage", array(
		"arguments" => array("multi_blog_category", $html)
	));

 	$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
	$labels = $labelDao->get();//表示順に並んでいる
	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");



	/** @ToDo ブログページをすべて取得 **/
	$blogDao = SOY2DAOFactory::create("cms.BlogPageDAO");
	try{
		$blogs = $blogDao->get();
	}catch(Exception $e){
		$blogs = array();
	}

	if(count($blogs)){
		foreach($blogs as $blog){
			$blogLabelId = $blog->getBlogLabelId();
			$categories = $blog->getCategoryLabelList();
			$categoryLabel = array();
			$entryCount = array();
			foreach($labels as $labelId => $label){
				if(in_array($labelId, $categories)){
					$categoryLabel[] =	$label;
					try{
						//記事の数を数える。
						$counts = $logic->getOpenEntryCountByLabelIds(array_unique(array($blogLabelId,$labelId)));
					}catch(Exception $e){
						$counts= 0;
					}
					$entryCount[$labelId] = $counts;
				}
			}

			$obj->createAdd("category_on_" . str_replace("/", "_", $blog->getUri()), "MultiCategoryList", array(
				"list" => $categoryLabel,
				"entryCount" => $entryCount,
				"categoryUrl" => convertUrlOnModuleBlogParts($blog->getCategoryPageURL(true)),
				"soy2prefix" => "b_block"
			));
		}
	}

	$obj->display();
}

function convertUrlOnModuleBlogParts($url){
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

class MultiCategoryList extends HTMLList{

	private $categoryUrl;
	private $entryCount = 0;

	function setCategoryUrl($categoryUrl){
		$this->categoryUrl = $categoryUrl;
	}

	protected function populateItem($entry){

		$this->addLink("category_link", array(
			"link"=>$this->categoryUrl . rawurlencode($entry->getAlias()),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_name","CMSLabel",array(
			"text"=>$entry->getBranchName(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_alias","CMSLabel",array(
			"text"=>$entry->getAlias(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("entry_count","CMSLabel",array(
			"text"=>$this->entryCount[$entry->getid()],
			"soy2prefix"=>"cms"
		));

		$this->createAdd("label_id","CMSLabel",array(
			"text"=>$entry->getid(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_description", "CMSLabel", array(
			"text" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));

		$this->addLabel("category_description_raw", array(
			"html" => $entry->getDescription(),
			"soy2prefix" => "cms"
		));

		$arg = substr(rtrim($_SERVER["REQUEST_URI"], "/"), strrpos(rtrim($_SERVER["REQUEST_URI"], "/"), "/") + 1);
		$alias = rawurlencode($entry->getAlias());
		$this->addModel("is_current_category", array(
			"visible" => ($arg === $alias),
			"soy2prefix" => "cms"
		));
		$this->addModel("no_current_category", array(
			"visible" => ($arg !== $alias),
			"soy2prefix" => "cms"
		));

		$this->addLabel("color", array(
			"text" => sprintf("%06X",$entry->getColor()),
			"soy2prefix" => "cms"
		));

		$this->addLabel("background_color", array(
			"text" => sprintf("%06X",$entry->getBackGroundColor()),
			"soy2prefix" => "cms"
		));
	}

	function getEntryCount() {
		return $this->entryCount;
	}
	function setEntryCount($entryCount) {
		$this->entryCount = $entryCount;
	}
}
