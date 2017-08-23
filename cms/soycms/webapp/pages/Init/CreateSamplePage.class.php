<?php

/**
 * 初めてサイトにログインしたときの初期ページ
 * 「ページ新規作成」と「ダミーデータを作成」の２者択一
 */

class CreateSamplePage extends CMSWebPageBase{

	function doPost(){
		$body = array();

		if(soy2_check_token()){
			try {
				$result = SOY2Logic::createInstance("logic.site.Init.CreateSampleLogic")->createSampleData();
			} catch(Exception $e) {
				$result = false;
				//var_dump($e);
			}

			if ($result) {
				$this->addMessage("SOYCMS_CREATED_WEBSITE_WITH_SAMPLEDATA");

				//親Windowをトップページへ遷移
				$body[] = CMSMessageManager::get("SOYCMS_CREATED_WEBSITE_WITH_SAMPLEDATA");
				$body[] = "<br/>";
				$body[] = CMSMessageManager::get("SOYCMS_MOVE_TO_WEBSITE_CONTROLPANEL");
				$body[] = "<script type=\"text/javascript\">window.parent.location.href='".SOY2PageController::createLink("Page")."';</script>";
			} else {
				$this->addErrorMessage("SOYCMS_ERROR_CREATING_SAMPLEDATA");

				$body[] = CMSMessageManager::get("SOYCMS_ERROR_CREATING_SAMPLEDATA");
				$body[] = "<br/>";
				if( !CMSUtil::checkZipEnable(true) ){
					$body[] = CMSMessageManager::get("SOYCMS_SAMPLE_NO_ZIP");
					$body[] = "<br/>";
				}
				$body[] = CMSMessageManager::get("SOYCMS_MOVE_TO_CREATE_NEW_PAGE", array(
					_PAGE_CREATE_ => SOY2PageController::createLink("Page.Create")
				));
			}
		}else{
			$this->addErrorMessage("SOYCMS_ERROR_CREATING_SAMPLEDATA");

			$body[] = CMSMessageManager::get("SOYCMS_ERROR_CREATING_SAMPLEDATA");
			$body[] = "<script type=\"text/javascript\">window.parent.location.href='".SOY2PageController::createLink("Init")."';</script>";
		}

		CMSMessageManager::save();

		echo '<html><head>';
		echo '</head><body>';
		echo implode("\n", $body);
		echo "</body></html>";

		exit;
	}

	function __construct() {
		WebPage::__construct();

		$this->createAdd("create_sample_form","HTMLForm");
	}

}
