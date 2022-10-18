<?php

class IndexPage extends WebPage{

    function __construct() {
		parent::__construct();

		//データベースの更新を調べる
		$checkVer = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic")->checkVersion();
		DisplayPlugin::toggle("has_db_update", $checkVer);

		//データベースの更新終了時に表示する
		$doUpdated = (isset($_GET["update"]) && $_GET["update"] == "finish");
		DisplayPlugin::toggle("do_db_update", $doUpdated);

		//上記二つのsoy:displayの表示用
		DisplayPlugin::toggle("do_update", ($checkVer || $doUpdated));

		$logic = SOY2Logic::createInstance("logic.CalendarLogic");
    	$this->addLabel("current_calendar", array(
    		"html" => $logic->getCurrentCalendar(true)
    	));

    	$this->addLabel("next_calendar", array(
    		"html" => $logic->getNextCalendar(true)
    	));

    }
}
