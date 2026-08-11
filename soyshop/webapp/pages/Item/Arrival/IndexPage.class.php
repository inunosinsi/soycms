<?php

class IndexPage extends WebPage{

	function __construct($args){
		$itemId = (isset($args[0])) ? (int)$args[0] : 0;

		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic")->getUsersByItemId($itemId, SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED)
		));
	}
}
