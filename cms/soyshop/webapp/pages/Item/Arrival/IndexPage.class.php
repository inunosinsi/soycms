<?php

class IndexPage extends WebPage{

	private $itemId;

	function __construct($args){
		$this->itemId = (isset($args[0])) ? (int)$args[0] : null;

		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		$users = $noticeLogic->getUsersByItemId($this->itemId, SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);

		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));
	}
}
