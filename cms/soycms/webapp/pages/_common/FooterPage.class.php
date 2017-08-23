<?php

class FooterPage extends CMSHTMLPageBase{

	function execute(){
		$year = date("Y", SOYCMS_BUILD_TIME);
		if($year>2007) $year = "2007-".$year;
		$copyright = $this->getMessage("COMMON_FOOTER_COPYRIGHT", array("YEAR" => $year));

		$this->createAdd("copyright","HTMLLabel",array(
			"html" => $copyright
		));
	}
}
