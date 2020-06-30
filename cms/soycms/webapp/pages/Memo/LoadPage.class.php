<?php

class LoadPage extends CMSHTMLPageBase {

	function __construct(){
		parent::__construct();

		//不安定なので廃止
		echo json_encode(array("content" => SOY2DAOFactory::create("cms.CmsMemoDAO")->getLatestMemo()->getContent()));
		exit;
	}
}
