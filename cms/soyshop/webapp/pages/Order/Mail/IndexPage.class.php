<?php
SOY2::import("domain.order.SOYShop_ItemModule");

/**
 * @class Order.Mail.IndexPage
 * @date 2009-08-03T19:54:15+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	private $id;
	private $mail;
	private $error;
	private $type;
	private $mailLogic;

	function doPost(){

		if(isset($_POST["send"]) && isset($_POST["mail_value"])){

			try{
				$order = soyshop_get_order_object($this->id);

				//送信メールのタイプによって、注文の状況を変更する
				switch($this->type){
					case SOYShop_Order::SENDMAIL_TYPE_CONFIRM:
						$order->setStatus(SOYShop_Order::ORDER_STATUS_RECEIVED);
						break;
					case SOYShop_Order::SENDMAIL_TYPE_PAYMENT:
						$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
						break;
					case SOYShop_Order::SENDMAIL_TYPE_DELIVERY:
						$order->setStatus(SOYShop_Order::ORDER_STATUS_SENDED);
						break;
					case SOYShop_Order::SENDMAIL_TYPE_ORDER:
					case SOYShop_Order::SENDMAIL_TYPE_OTHER:
						//何もしない
						break;
					default:
						//拡張ポイントを調べる
						SOYShopPlugin::load("soyshop.order.detail.mail");
						$statusList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array("mode" => "aftersend", "type" => $this->type))->getList();
						if(count($statusList)){
							foreach($statusList as $status){
								if(isset($status) && is_numeric($status) && (int)$status > 0){
									$order->setStatus($status);
								}
							}
						}
				}
				SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);

				SOYShopPlugin::load("soyshop.order.status.update");
    			SOYShopPlugin::invoke("soyshop.order.status.update", array(
    				"order" => $order,
    				"mode" => "status"
    			));

				$sendToName = "";
				$mail = unserialize(base64_decode($_POST["mail_value"]));
				$this->mailLogic->sendMail($mail["sendTo"], $mail["title"], $mail["content"], $sendToName, $order);


				$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

				$session = SOY2ActionSession::getUserSession();
				$author = (!is_null($session->getAttribute("loginid"))) ? $session->getAttribute("loginid") :  null;

				//ヒストリーに追加
				$orderLogic->addHistory($this->id, self::_getMailText($this->type) . "を送信しました", null, $author);

				//ステータスに登録
				$orderLogic->setMailStatus($this->id, $this->type, time());

				SOY2PageController::jump("Order.Detail." . $this->id . "?sended");
			}catch(Exception $e){
				$this->error = true;
			}
		}else{
			$this->mail = $_POST["Mail"];
		}
	}

	function __construct($args){

		$this->id = (isset($args[0])) ? (int)$args[0] : null;

		try{
			$order = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Order");
		}

		$user = soyshop_get_user_object($order->getUserId());
		if(!$user->isUsabledEmail()) SOY2PageController::jump("Order.Detail" . $order->getId());

		$this->mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

		//メール送信時の言語設定
		self::_checkLanguageConfig($order);

		$type = (isset($_GET["type"])) ? $_GET["type"] : SOYShop_Order::SENDMAIL_TYPE_ORDER;
		$this->type = $type;

		parent::__construct();

		$sendTo = $user->getMailAddress();

		$mail = $this->mailLogic->getUserMailConfig($type);

		$this->addForm("form");

		$this->addInput("send_to", array(
			"name" => "Mail[sendTo]",
			"value" => (isset($this->mail["sendTo"])) ? $this->mail["sendTo"] : $user->getMailAddress()
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => (isset($this->mail["title"])) ? $this->mail["title"] : $this->mailLogic->convertMailContent($mail["title"], $user, $order),
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => (isset($this->mail["content"])) ? $this->mail["content"] : self::_getMailContent($type, $order, $mail, $user),
		));

		$this->addLabel("mail_type_text", array(
			"text" => self::_getMailText($type)
		));

		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $this->id),
		));

		$this->addInput("send_button", array(
			"value" => (is_null($this->mail)) ? "送信" : "修正"
		));

		DisplayPlugin::toggle("on_confirm", !is_null($this->mail));

		$this->addInput("mail_value", array(
			"name" => "mail_value",
			"value" => base64_encode(serialize($this->mail))
		));

		DisplayPlugin::toggle("error", isset($this->error));

		DisplayPlugin::toggle("storage", class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("store_user_folder")));
		$this->addLabel("storage_url", array(
			"text" => SOY2PageController::createLink("User.Storage." . $order->getUserId())
		));
	}

	private function _checkLanguageConfig($order){
		$attr = $order->getAttribute("util_multi_language");
		if(isset($attr["value"]) && strlen($attr["value"])){
			if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", $attr["value"]);
			if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", $attr["value"]);
		}
		MessageManager::addMessagePath("admin");
	}

	private function _getMailText($type){
		$array = array(
			"order" => "注文受付メール",
			"confirm" => "注文確認メール",
			"payment" => "支払確認メール",
			"delivery" => "配送連絡メール",
			"other" => "その他のメール",
			"other2" => "その他のメール"
		);

		if(isset($array[$type])) return $array[$type];

		//プラグインから出力したものを調べる
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_add_mail_type")) {
			SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
			$configs = AddMailTypeUtil::getConfig();

			if(isset($configs[$type])) return $configs[$type]["title"];
		//メール送信種類追加プラグイン以外
		}else{
			SOYShopPlugin::load("soyshop.order.detail.mail");
			$mailConfList = SOYShopPlugin::invoke("soyshop.order.detail.mail")->getList();
			if(count($mailConfList)){
				foreach($mailConfList as $mailConf){
					foreach($mailConf as $mailType => $conf){
						if($mailType == $type) return $conf["title"];
					}
				}
			}
		}

		return $array["order"];
	}

	private function _getMailContent($type, SOYShop_Order $order, $array, SOYShop_User $user){

		//システムからの出力を行うか？
		if(isset($array["output"]) && $array["output"] === 1){
			//メール本文を取得
	    	$body = SOY2Logic::createInstance("logic.mail.MailBuilder")->buildOrderMailBodyForUser($order, $user);
		}else{
			$body = "";
		}

		//プラグインを実行してメール本文の取得 プラグインの拡張ポイントはメールの種類で分ける
		if(isset($array["plugin"]) && $array["plugin"] === 1){
			SOYShopPlugin::load("soyshop.order.mail");
	    	$delegate = SOYShopPlugin::invoke($this->mailLogic->getOrderMailExtension($type), array(
					"order" => $order,
					"mail" => $array
			));

			$append_body = (!is_null($delegate)) ? $delegate->getBody() : "";
			if(strlen($append_body)) $body .= $append_body;
		}

		$mailBody = $array["header"] ."\n". $body . "\n" . $array["footer"];

		//convert
		return $this->mailLogic->convertMailContent($mailBody, $user, $order);
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("メール送信", array("Order" => "注文管理", "Order.Detail." . $this->id => "注文詳細"));
	}
}
