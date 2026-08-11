<?php

class RPCFConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.research_page_class_file.util.ResearchPageClassFileUtil");
		SOY2::import("module.plugins.research_page_class_file.component.NotCompatibleFileListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			$results = ResearchPageClassFileUtil::research(ResearchPageClassFileUtil::MODE_PAGE_ID);
			ResearchPageClassFileUtil::save($results);
			$this->configObj->redirect("finished");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("finished", isset($_GET["finished"]));

		//調査結果
		$results = ResearchPageClassFileUtil::get();
		$isRes = (is_array($results));
		DisplayPlugin::toggle("result_area", $isRes);

		$cnt = ($isRes) ? count($results) : 0;
		DisplayPlugin::toggle("is_result", $cnt > 0);
		DisplayPlugin::toggle("no_result", $cnt === 0);

		$this->createAdd("file_list", "NotCompatibleFileListComponent", array(
			"list" => ($isRes) ? $results : array()
		));

		$this->addForm("form");
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
