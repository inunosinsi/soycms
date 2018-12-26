<?php

/**
 * 初めてサイトにログインしたときの初期ページ
 * 「ページ新規作成」と「ダミーデータを作成」の２者択一
 */

class IndexPage extends CMSWebPageBase{

	function __construct() {

		$initDetect = $this->run("Init.InitDetectAction");
		if($initDetect->success() && $initDetect->getAttribute("detect")){
			//処理を続ける
		}else{
			$this->jump("");
		}

		WebPage::__construct();

		$this->addLabel("cms_name", array(
			"text" => CMSUtil::getCMSName()
		));

		$this->addModel("has_zip_archive",array(
				"visible" => CMSUtil::checkZipEnable(true),
		));
		$this->addModel("no_zip_archive",array(
				"visible" => !CMSUtil::checkZipEnable(true),
		));

	}

}
