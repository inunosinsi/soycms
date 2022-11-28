<?php

define('APPLICATION_ID', "calendar");
/**
 * ページ表示
 */
class SOYCalendar_PageApplication{

	var $page;
	var $serverConfig;


	function init(){
		CMSApplication::main(array($this,"main"));

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			return;
		}
	}

	function prepare(){}

	function main($page){

		$this->page = $page;

		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		//設定ファイルの読み込み
		include_once(dirname(__FILE__) . "/config.php");
		$this->prepare();

		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")) SOY2Logic::createInstance("logic.InitLogic")->init();

		$arguments = CMSApplication::getArguments();

		//app:id="soycalendar"
		$this->page->createAdd("soycalendar","SOYCalendar_ItemComponent",array(
			"application" => $this,
			"page" => $page,
			"soy2prefix" => "app"
		));

		//app:id="mobilecalendar"
		if(false){	//廃止
			$this->page->createAdd("mobilecalendar","SOYCalendar_MobileComponent",array(
				"application" => $this,
				"page" => $page,
				"soy2prefix" => "app"
			));
		}
		
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);
	}
}

class SOYCalendar_ItemComponent extends SOYBodyComponentBase{

	private $page;
	private $application;


	function setPage($page){
		$this->page = $page;
	}

	function execute(){
		self::_calendar();
		parent::execute();
	}

	private function _calendar(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.CalendarLogic");
		$prefix = "cms";

		$isPrev = $this->getAttribute("app:prev");
		if(is_null($isPrev)) $isPrev = 1;
		
		$this->addLabel("prev_calendar" ,array(
			"soy2prefix" => $prefix,
			"html" => ((int)$isPrev === 1) ? $logic->getPrevCalendar(false, false) : ""
		));
		
		$this->addLabel("current_calendar", array(
			"soy2prefix" => $prefix,
			"html" => $logic->getCurrentCalendar(false, true)
		));

		$isNext = $this->getAttribute("app:next");
		if(is_null($isNext)) $isNext = 1;
		
		$this->addLabel("next_calendar", array(
			"soy2prefix" => $prefix,
			"html" => ((int)$isNext === 1) ? $logic->getNextCalendar(false, true) : ""
		));
		
		$isSpecify = $this->getAttribute("app:specify");
		if(is_null($isSpecify)) $isSpecify = 1;

		if($isSpecify === 1){
			for($i = 1; $i <= 10; $i++){
				$this->createAdd("specify_calender_" . $i, "SpecifyCalendarDisplay", array(
					"soy2prefix" => $prefix,
				));
			}
		}
	}

	function getApplication(){
		return $this->application;
	}

	function setApplication($application){
		$this->application = $application;
	}
}

class SpecifyCalendarDisplay extends HTMLLabel{

	function execute(){
		$specify = (int)$this->getAttribute("cms:specify");
		if(isset($specify) && $specify !== 0){	//cms:spacifyが0の時は使用不可	current_calendarを使いましょう
			$html = self::_logic()->getSpecifyCalendar(false, true, $specify);
		}else{
			$html = "";
		}
		$this->setHtml($html);
		parent::execute();
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.CalendarLogic");
		return $logic;
	}
}

class SOYCalendar_MobileComponent extends SOYBodyComponentBase{

	private $page;
	private $application;


	function setPage($page){
		$this->page = $page;
	}

	function execute(){
		self::_calendar();
		parent::execute();
	}

	private function _calendar(){

		$page = $this->application->page;
		$url = $page->siteRoot.$page->page->getUri();
		if(strpos($url,"/") != 0){
			$url = "/".$url;
		}

		$logic = SOY2Logic::createInstance("logic.CalendarLogic");
		$prefix = "cms";

		$this->createAdd("mobile_calendar", "MobileCalendarDisplay", array(
			"soy2prefix" => "block",
			"list" => $logic->getMobileCalendar()
		));

		/** モバイル用のページャ **/

		$this->addLink("prev_link", array(
			"soy2prefix" => $prefix,
			"link" => $logic->getPrevPager($url),
			"visible" => $logic->isPrev()
		));

		$this->addLink("next_link", array(
			"soy2prefix" => $prefix,
			"link" => $logic->getNextPager($url)
		));
	}

	function getApplication(){
		return $this->application;
	}

	function setApplication($application){
		$this->application = $application;
	}
}

class MobileCalendarDisplay extends HTMLList{

	protected function populateItem($entity){

		$prefix = "cms";

		$this->addLabel("date", array(
			"soy2prefix" => $prefix,
			"html" => (isset($entity["schedule"])) ? $entity["schedule"] : ""
		));
	}
}

$app = new SOYCalendar_PageApplication();
$app->init();
