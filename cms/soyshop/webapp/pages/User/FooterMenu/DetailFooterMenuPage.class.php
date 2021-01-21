<?php

class DetailFooterMenuPage extends HTMLPage{

	private $id;

	function __construct($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		$user = soyshop_get_user_object($this->id);

		self::_buildMailForm($user);		//顧客宛メール
		self::_buildMailLogForm($user);	//メールログ
		self::_buildPointForm($user);	//ポイント
		self::_buildTicketForm($user);	//チケット
		self::_buildOperateForm($user);	//カード会員操作

		//ノートパッド
		SOYShopPlugin::load("soyshop.notepad");
		$this->addLabel("notepad_extension", array(
			"html" => SOYShopPlugin::invoke("soyshop.notepad", array(
				"mode" => "user",
				"id" => $user->getId()
			))->getHtml()
		));
	}

	private function _buildMailForm(SOYShop_User $user){
		DisplayPlugin::toggle("mail", $user->isUsabledEmail());
		$this->addLink("send_mail_link", array(
			"link" => SOY2PageController::createLink("User.Mail." . $user->getId())
		));

		//メールの拡張
		$this->createAdd("mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
			"list" => self::_getMailPluginList(),
			"userId" => $user->getId()
		));
	}

	private function _getMailPluginList(){
    	SOYShopPlugin::load("soyshop.order.detail.mail");
    	$mailList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array("mode" => "user"))->getList();
		if(!count($mailList)) return array();

    	$list = array();
    	foreach($mailList as $values){
    		if(!is_array($values)) continue;
   			foreach($values as $value){
   				$list[] = $value;
   			}
    	}
    	return $list;
    }

	private function _buildMailLogForm(SOYShop_User $user){
		$mailLogDao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");
		$mailLogDao->setLimit(10);
		try{
			$mailLogs = $mailLogDao->getByUserId($user->getId());
		}catch(Exception $e){
			$mailLogs = array();
		}

		DisplayPlugin::toggle("display_mail_history", count($mailLogs));
		$this->createAdd("mail_history_list", "_common.Order.MailHistoryListComponent", array(
    		"list" => $mailLogs
    	));
	}

	/**
	 * ポイントフォーム
	 * @param SOYShop_User $user
	 */
	private function _buildPointForm(SOYShop_User $user){
		/* ここ以下はポイント有効時 */
		$histories = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_point_base"))) ? self::_getPointHistories($user->getId()) : array();
		DisplayPlugin::toggle("point_history", (count($histories) > 0));
		$this->createAdd("point_history_list", "_common.User.PointHistoryListComponent", array(
			"list" => $histories
		));
	}

	private function _getPointHistories($userId){
		SOY2::imports("module.plugins.common_point_base.domain.*");
		try{
			return SOY2DAOFactory::create("SOYShop_PointHistoryDAO")->getByUserId($userId);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * チケットフォーム
	 * @param SOYShop_User $user
	 */
	private function _buildTicketForm(SOYShop_User $user){

		//チケット
		$histories = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_ticket_base"))) ? self::_getTicketHistories($user->getId()) : array();

    	DisplayPlugin::toggle("ticket_history", (count($histories) > 0));
    	$this->createAdd("ticket_history_list", "_common.User.TicketHistoryListComponent", array(
    		"list" => $histories
    	));
	}

	private function _getTicketHistories($userId){
		SOY2::imports("module.plugins.common_ticket_base.domain.*");
		try{
			return SOY2DAOFactory::create("SOYShop_TicketHistoryDAO")->getByUserId($userId);
		}catch(Exception $e){
			return array();
		}
	}

	private function _buildOperateForm($user){
		SOYShopPlugin::load("soyshop.operate.credit");
		$list = SOYShopPlugin::invoke("soyshop.operate.credit", array(
			"user" => $user,
			"mode" => "user_detail",
		))->getList();

		DisplayPlugin::toggle("operate_credit_menu", (is_array($list) && count($list) > 0));
		$this->createAdd("operate_list", "_common.User.OperateListComponent", array(
			"list" => $list
		));
	}
}
