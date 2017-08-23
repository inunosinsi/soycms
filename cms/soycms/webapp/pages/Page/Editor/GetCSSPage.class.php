<?php
SOY2::import("util.CMSFileManager");
class GetCSSPage extends CMSWebPageBase{

	function __construct() {
		$result = $this->run("Page.GetCSSAction");
		if($result->success()){
			echo $result->getAttribute("css");
			exit;
		}else{
			header("HTTP/1.0 404 Not Found");
			exit;
		}
	}
}
