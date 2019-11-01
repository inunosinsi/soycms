<?php

class InitLogic extends SOY2LogicBase{

    function init() {
    	self::initTable();
    	self::initInsert();
    }

        /**
     * テーブルを初期化する
     * @todo
     */
    private function initTable(){
    	$db = new SOY2DAO();

    	$sqls = explode(";", file_get_contents(dirname(__FILE__)."/table_". SOYCMS_DB_TYPE .".sql"));
    	foreach($sqls as $sql){
    		if(strlen(trim($sql))<1)continue;
    		$db->executeUpdateQuery($sql,array());
    	}
    	if(!file_exists(CMS_COMMON . "db/calendar.db"))file_put_contents(CMS_COMMON . "db/calendar.db", "created:" . date("Y-m-d H:i:s"));

    }

    private function initInsert(){
		$titles = array();
		$titles[] = array("title" => "午前");
		$titles[] = array("title" => "午後");
		$titles[] = array("title" => "夜間");

		$titleDao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");

		foreach($titles as $title){
			$title = SOY2::cast("domain.SOYCalendar_Title", $title);
			$titleDao->insert($title);
		}

	}
}
