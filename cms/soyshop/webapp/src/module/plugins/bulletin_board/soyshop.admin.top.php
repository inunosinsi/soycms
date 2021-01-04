<?php
class BulletinBoardTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=bulletin_board");
	}

	function getLinkTitle(){
		return "掲示板の設定";
	}

	function getTitle(){
		return "掲示板";
	}

	function getContent(){
		return "<div class=\"alert alert-warning\">@ToDo 新着</div>";
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "bulletin_board", "BulletinBoardTop");
