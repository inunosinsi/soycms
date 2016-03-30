<?php

class IndexPage extends WebPage{
	
	private $itemId;
	
	function IndexPage($args){
		$this->itemId = (isset($args[0])) ? (int)$args[0] : null;
		
		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		$users = $noticeLogic->getUsersByItemId($this->itemId, SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
		
		WebPage::WebPage();
		
		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));
	}
}
?>