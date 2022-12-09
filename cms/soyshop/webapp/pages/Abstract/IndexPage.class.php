<?php

class IndexPage extends WebPage {

	function __construct(){

		parent::__construct();

		DisplayPlugin::toggle("button", AUTH_ABSTRACT);
		DisplayPlugin::toggle("script", AUTH_ABSTRACT);

		$content = SOYShop_DataSets::get("soyshop_abstract", "");

		//SOY Board on SOY Shopから整形用のロジックを拝借
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
		$this->addLabel("content", array(
			"html" => BulletinBoardUtil::nl2br(BulletinBoardUtil::abbrUrlText(BulletinBoardUtil::autoInsertAnchorTag($content)))
		));
	}
}
