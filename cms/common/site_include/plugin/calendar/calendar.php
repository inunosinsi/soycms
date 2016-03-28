<?php
/*
 * ブログページで使用
 * カレンダーを表示するプラグイン
 * 
 */

CalendarPlugin::registerPlugin();

class CalendarPlugin{
	
	const PLUGIN_ID = "calendar";
	var $format = "Y年m月";
	
	function setCms_format($format){
		$this->format = $format;
	}
	
	function getId(){
		return self::PLUGIN_ID;
	}
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"カレンダープラグイン",
			"description"=>"ブログページ内部でカレンダーを出力することが出来ます。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.5"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
		
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
			CMSPlugin::addBlock($this->getId(),"page",array(
				$this,"block"
			));
		}
	}
	
	function config_page($message){
		return file_get_contents(dirname(__FILE__)."/info.html");
	}
	
	function block($html,$pageId){
		try{
			$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
			$blog = $pageDao->getById($pageId);
			
			$page = SOY2PageController::init();
			@$mode = $page->webPage->mode;
			@$args = $page->args;

			if($mode === CMSBlogPage::MODE_MONTH_ARCHIVE){
				if($blog->getMonthPageUri() == ""){
					$year  = @$args[0];
					$month = @$args[1];
				}else{
					$year  = @$args[1];
					$month = @$args[2];
				}
			}elseif($mode === CMSBlogPage::MODE_ENTRY){
				if($blog->getEntryPageUri() == ""){
					$entryId = @$args[0];
				}else{
					$entryId = @$args[1];
				}
				$date = SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->getBlogEntry($blog->getBlogLabelId(),$entryId)->getCdate();
				$year = date("Y",$date);
				$month = date("n",$date);
			}else{
				$year = date("Y");
				$month = date("n");
			}

			if(strlen($year)){
				$month = (strlen($month)) ? $month : 1 ;
			}else{
				$year  = date("Y");
				$month = date("n");
			}
			$monthStart = mktime(0,0,0,$month,1,$year);
			$monthEnd = mktime(0,0,0,$month+1,1,$year);

			$monthLink = "";
			if(!$blog->getGenerateMonthFlag())throw new Exception();
			
			if(defined("CMS_PREVIEW_MODE")){
				$monthLink = SOY2PageController::createLink("Page.Preview") ."/". $blog->getId() ."?uri=/". $blog->getMonthPageUri();
			}else{
				$monthLink = SOY2PageController::createLink("") . ((strlen($blog->getUri())>0) ? $blog->getUri() ."/" : "") . $blog->getMonthPageUri();
			}
			
			$labelId = $blog->getBlogLabelId();
			
			$query = new SOY2DAO_Query();
			$query->prefix  = "select";
			$query->sql = "count(Entry.id) as count";
			$query->table = " Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id) ";
			$where  = array();
			$where[] = "EntryLabel.label_id = :labelId";
			$where[] = "Entry.isPublished = 1";
			$where[] = "(openPeriodEnd >= ".time()." AND openPeriodStart < ".time().")";
			$where[] = "(cdate >= :start AND cdate < :end)";
			$query->where = implode(" AND ",$where);
			
			$pdo = SOY2DAO::_getDataSource();
			$stmt = $pdo->prepare($query->__toString());
			$stmt->bindParam(":labelId",$labelId);
			
			$calendar = array();
			
			for($i=$monthStart;$i<$monthEnd;){
				$start = $i;
				$end = $i + 24*60*60;
				
				$stmt->bindParam(":start",$start);
				$stmt->bindParam(":end",$end);
				
				$result = $stmt->execute();
				$row =  $stmt->fetch(PDO::FETCH_ASSOC);				
				$calendar[date("Y/m/d",$i)] = $row["count"];
				$i = $end;
			}
			
			
		}catch(Exception $e){
			return "";
		}
		
		
		$diff = ((int)date("w",$monthStart) > 0) ? (date("w",$monthStart) - 1) : 6;
		$tmpDate = $monthStart - 24*60*60 * $diff;
		$today_text = date("Y/m/d");
		$this_month = false;
				
		$days = array(
			"月","火","水","木","金","土","日"
		);
		$days_class = array(
			'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',
		);
		
		$buff = array();
		$buff[] = '<div class="caption">';
		$buff[] = '<span class="prev"><a  href="'.$monthLink."/".date("Y/m",$monthStart - 1).'">&lt;&lt;</a></span>';
		$buff[] = '<span class="current_month">'.date($this->format,$monthStart).'</span>';
		$buff[] = '<span class="next"><a href="'.$monthLink."/".date("Y/m",$monthEnd + 1).'">&gt;&gt;</a></span>';
		$buff[] = '</div>';
		$buff[] ="<table class=\"calendar_table\">\n";
		$buff[] ="<thead><tr>\n";
		for($day=0;$day<7;$day++){
			$buff[] = "<th class=\"{$days_class[$day]}\">".$days[$day]."</th>";
		}
		$buff[] ="</tr></thead>\n";
		$buff[] ="<tbody>";
		for($row=0;$row<6;$row++){
			$buff[] ="<tr>";
			for($day=0;$day<7;$day++){
				$date_text = date("Y/m/d",$tmpDate);
				
				$class = $days_class[$day];
				if($date_text == $today_text) $class .= ' Today';
				
				if(isset($calendar[$date_text])){
					$this_month = true;
					$count = $calendar[$date_text];
					$text = ($count>0) ? '<a href="'.$monthLink."/".$date_text.'">'.date("j",$tmpDate).'</a>' : date("j",$tmpDate) ;
					$class .= ($count>0) ? ' HasEntry' : ' NoEntry';
				}else{
					$text = "&nbsp;";
					$class .= $this_month ? ' NextMonth' : ' PrevMonth';
				}

				$buff[] = "<td class=\"{$class}\">{$text}</td>";
				
				$tmpDate+=24*60*60;
			}
			$buff[] ="</tr>\n";
			if($tmpDate >= $monthEnd)break;			
		}
		$buff[] ="</tbody>";
		$buff[] ="</table>";
		return implode("",$buff);
	}
	
	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new CalendarPlugin();
			
			//この時プラグインを強制的に有効にする
			$filepath = CMSPlugin::getSiteDirectory().'/.plugin/'. self::PLUGIN_ID;
			if(!file_exists($filepath . ".inited")){
				@file_put_contents($filepath .".active","active");
				@file_put_contents($filepath .".inited","inited");
			}
		}
		
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}	
}
?>