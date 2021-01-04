<?php

class BulletinBoardAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "掲示板詳細";
	}

	function getContent(){
		//
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "bulletin_board", "BulletinBoardAdminDetail");
