<?php

class LogPage extends WebPage{

	private $logId;
	private $orderId;

	function __construct($args){
		$this->logId = (isset($args[0])) ? $args[0] : null;

		parent::__construct();

		try{
			$log = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO")->getbyId($this->logId);
		}catch(Exception $e){
			SOY2PageController::jump("Order");
		}
		$this->orderId = $log->getOrderId();

		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $log->getOrderId())
		));

		$this->addLabel("send_date", array(
			"text" => date("Y-m-d H:i:s", $log->getSendDate())
		));

		$recipients = explode(",", $log->getRecipient());

		$mails = array();
		foreach($recipients as $recipient){
			$mails[] = "<a href=\"mailto:" . $recipient . "\">" . $recipient . "</a>";
		}

		$this->addLabel("recipient", array(
			"html" => implode("<br />", $mails)
		));

		$this->addLabel("title", array(
			"text" => $log->getTitle()
		));

		$this->addLabel("content", array(
			"html" => nl2br($log->getContent())
		));

		$user = soyshop_get_user_object($log->getUserId());
		DisplayPlugin::toggle("mail", $user->isUsabledEmail());
		$this->addLink("send_mail_link", array(
			"link" => SOY2PageController::createLink("User.Mail." . $user->getId())
		));

		self::_buildMailForm($user);		//顧客宛メール
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

	function getBreadcrumb(){
		return BreadcrumbComponent::build("送信メール詳細", array("Order" => "注文管理", "Order.Detail." . $this->orderId => "注文詳細"));
	}
}
