<?php

class CurrentCategoryOrArchiveComponent extends SOYBodyComponentBase{

	var $entryCount = array();

	function setPage($page){
		$alias = null;
		$link = null;
		$description = null;

		switch(SOYCMS_BLOG_PAGE_MODE){
			case CMSBlogPage::MODE_CATEGORY_ARCHIVE :
				$isLabelObj = (property_exists($page, "label") && $page->label instanceof Label);
				$this->createAdd("archive_name","CMSLabel",array(
					"text"=> ($isLabelObj) ? $page->label->getBranchName() : "",
					"soy2prefix"=>"cms"
				));
				if($isLabelObj){
					$link = $page->getCategoryPageURL(true) . rawurlencode($page->label->getAlias());
					$alias = $page->label->getAlias();
					$description = $page->label->getDescription();
				}
				break;

			case CMSBlogPage::MODE_MONTH_ARCHIVE :
			default:
				if(!$page->year){
					$date = time();
					$link = date("Y/m", time());
				}elseif(!$page->month){
					$date = @mktime(0,0,0,1,1,$page->year);
					$link = date("Y",$date);
				}elseif(!$page->day){
					$date = @mktime(0,0,0,$page->month,1,$page->year);
					$link = date("Y/m",$date);
				}else{
					$date = @mktime(0,0,0,$page->month,$page->day,$page->year);
					$link = date("Y/m/d",$date);
				}
				$this->createAdd("archive_name","DateLabel",array(
					"year"  => $page->year,
					"month" => $page->month,
					"day"   => $page->day,
					"soy2prefix"=>"cms",
					"defaultFormat"=>"Y年n月"
				));
				$link = $page->getMonthPageURL(true) . $link;
				break;
		}
		$this->addLink("archive_link", array(
			"link"=> $link,
			"soy2prefix"=>"cms"
		));

		$this->createAdd("category_alias","CMSLabel",array(
			"text"=>$alias,
			"soy2prefix"=>"cms"
		));

		$isDescription = (strlen(trim((string)$description)) > 0);
		$this->addModel("is_description", array(
			"visible" => $isDescription,
			"soy2prefix" => "cms"
		));

		$this->addModel("no_description", array(
			"visible" => !$isDescription,
			"soy2prefix" => "cms"
		));

		$this->createAdd("category_description","CMSLabel",array(
			"text"=>$description,
			"soy2prefix"=>"cms"
		));
		$this->addLabel("category_description_raw", array(
			"html" => $description,
			"soy2prefix" => "cms"
		));

		$label = (property_exists($page, "label") && $page->label instanceof Label) ? $page->label : new Label();
		$this->addLabel("entry_count", array(
			"text" => (is_array($this->entryCount) && property_exists($page, "label") && ($page->label instanceof Label) && isset($this->entryCount[$page->label->getId()]) && is_numeric($this->entryCount[$page->label->getId()])) ? $this->entryCount[$page->label->getId()] : 0,
			"soy2prefix" => "cms"
		));

		// ラベルカスタムフィールドの拡張ポイントはカテゴリーアーカイブの時のみ
		switch(SOYCMS_BLOG_PAGE_MODE){
			case CMSBlogPage::MODE_CATEGORY_ARCHIVE :
				if(property_exists($page, "label") && $page->label instanceof Label){
					CMSPlugin::callEventFunc('onLabelOutput',array("labelId"=>$page->label->getId(),"SOY2HTMLObject"=>$this,"label"=>$page->label));
				}
				break;
			default:
				//
		}
	}

	function setEntryCount($entryCount) {
		$this->entryCount = $entryCount;
	}
}
