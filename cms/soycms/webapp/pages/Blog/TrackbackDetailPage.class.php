<?php

class TrackbackDetailPage extends CMSWebPageBase{

	function __construct($arg) {
		$trackbackId = @$arg[0];

		//記事公開管理者権限が必要
		if(!UserInfoUtil::hasEntryPublisherRole()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		$result = $this->run("EntryTrackback.TrackbackDetailAction",array("trackbackId"=>$trackbackId));

		if(!$result->success()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		parent::__construct();
		$trackback = $result->getAttribute("entity");

		$result = $this->run("Entry.EntryDetailAction",array("id"=>$trackback->getEntryId()));

		if(!$result->success()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		$entry = $result->getAttribute("Entry");
		$title = $trackback->getTitle();

		if(strlen($title) == 0){
			$title = CMSMessageManager::get("SOYCMS_NO_TITLE");
		}

		$this->createAdd("title","HTMLLabel",array(
			"text"=>$title
		));

		$this->createAdd("blogName","HTMLLabel",array(
			"text"=>$trackback->getBlogName()
		));

		$this->createAdd("entry_title","HTMLLabel",array(
			"text"=>$entry->getTitle()
		));

		$this->createAdd("blogAddress","HTMLLabel",array(
			"text"=>$trackback->getUrl()
		));

		$this->createAdd("submit_date","HTMLLabel",array(
			"text"=>date("Y-m-d H:i:s",$trackback->getSubmitDate())
		));

		$this->createAdd("state","HTMLLabel",array(
			"text"=>($trackback->getCertification() == 0)? CMSMessageManager::get("SOYCMS_DENY") : CMSMessageManager::get("SOYCMS_ALLOW")
		));

		$this->createAdd("content","HTMLLabel",array(
			"text"=>$trackback->getExcerpt()
		));

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/table.css")
		));

		//記事テーブルのCSS
		HTMLHead::addLink("style_CSS",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/style.css")
		));

	}
}
