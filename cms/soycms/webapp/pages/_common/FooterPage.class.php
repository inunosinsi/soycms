<?php

class FooterPage extends CMSHTMLPageBase{

	function execute(){
		$this->addLabel("cms_name", array(
			"text" => CMSUtil::getCMSName()
		));

		$year = date("Y", SOYCMS_BUILD_TIME);
		if($year>2007) $year = "2007-".$year;
		$copyright = $this->getMessage("COMMON_FOOTER_COPYRIGHT", array("YEAR" => $year));

		$this->createAdd("copyright","HTMLLabel",array(
			"html" => $copyright
		));

		//バージョン番号
		$this->addLabel("version",array(
				"text" => SOYCMS_VERSION,
		));

		$this->addLabel("developer_name", array(
			"text" => CMSUtil::getDeveloperName()
		));

		include_once(dirname(__FILE__) . "/Widget/MemoWidgetComponent.class.php");
		$component = new MemoWidgetComponent();
		$this->addLabel("memo_widget", array(
			"html" => $component->buildWidget()
		));
	}
}
