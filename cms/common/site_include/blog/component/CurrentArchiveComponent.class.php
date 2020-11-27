<?php

class CurrentArchiveComponent extends SOYBodyComponentBase{
	function setPage($page){

		if(!$page->year){
			$date = time();
			$link = date("Y/m", $date);
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

		$this->createAdd("archive_month","DateLabel",array(
			"year"  => $page->year,
			"month" => $page->month,
			"day"   => $page->day,
			"soy2prefix"=>"cms",
			"defaultFormat"=>"%Y:Y年%%M:n月%%D:j日%"
		));

		$this->createAdd("archive_date","DateLabel",array(
			"year"  => $page->year,
			"month" => $page->month,
			"day"   => $page->day,
			"soy2prefix"=>"cms",
			"defaultFormat"=> "%Y:Y年%%M:n月%%D:j日%"
		));

		$this->addLink("archive_link", array(
			"link"=> $page->getMonthPageURL(true) . $link,
			"soy2prefix"=>"cms"
		));
	}
}
