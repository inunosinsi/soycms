<?php

class CurrentCategoryOrArchiveComponent extends SOYBodyComponentBase{
	function setPage($page){
		$alias = null;
		$link = null;
		$description = null;

		switch($page->mode){
			case CMSBlogPage::MODE_CATEGORY_ARCHIVE :
				$this->createAdd("archive_name","CMSLabel",array(
					"text"=> ( ($page->label) ? $page->label->getBranchName() : "" ),
					"soy2prefix"=>"cms"
				));
				if($page->label){
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
		$this->createAdd("category_description","CMSLabel",array(
			"text"=>$description,
			"soy2prefix"=>"cms"
		));
		$this->addLabel("category_description_raw", array(
			"html" => $description,
			"soy2prefix" => "cms"
		));
	}
}
