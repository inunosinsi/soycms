<?php
class FooterPage extends CMSWebPageBase{

	var $copyRight = "";

    function __construct() {
    	HTMLPage::HTMLPage();

    	$year = date("Y", SOYCMS_BUILD_TIME);
    	if($year > 2007) $year = "2007-" . $year;
    	$this->copyRight = $this->getMessage("COMMON_FOOTER_COPYRIGHT", array("YEAR" => $year));

    }

    function execute(){

    	$this->addLabel("copyright", array(
    		"html" => $this->copyRight
    	));

    }
}
?>