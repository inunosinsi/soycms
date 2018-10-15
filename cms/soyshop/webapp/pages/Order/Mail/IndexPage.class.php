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

	function doPost(){

		if(isset($_POST["send"]) && isset($_POST["mail_value"])){

			try{
				$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
				$order = $orderDAO->getById($this->id);

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
					default:
					case SOYShop_Order::SENDMAIL_TYPE_ORDER:
						break;
				}
				$orderDAO->updateStatus($order);

				SOYShopPlugin::load("soyshop.order.status.update");
    			SOYShopPlugin::invoke("soyshop.order.status.update", array(
    				"order" => $order,
    				"mode" => "status"
    			));

				$sendToName = "";
				$mail = unserialize(base64_decode($_POST["mail_value"]));
				$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
				$mailLogic->sendMail($mail["sendTo"], $mail["title"], $mail["content"], $sendToName, $order);


				$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

				$session = SOY2ActionSession::getUserSession();
				$author = (!is_null($session->getAttribute("loginid"))) ? $session->getAttribute("loginid") :  null;

				//ヒストリーに追加
				$orderLogic->addHistory($this->id, $this->getMailText($this->type) . "を送信しました", null, $author);

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
			$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$order = $orderDAO->getById($this->id);
		}catch(Exception $e){
			CMSPageController::jump("Order");
		}

		//メール送信時の言語設定
		$this->checkLanguageConfig($order);

		$type = (isset($_GET["type"])) ? $_GET["type"] : SOYShop_Order::SENDMAIL_TYPE_ORDER;
		$this->type = $type;

		parent::__construct();

		$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
		$sendTo = $user->getMailAddress();
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$mail = $mailLogic->getUserMailConfig($type);

		$this->addForm("form");

		$this->addInput("send_to", array(
			"name" => "Mail[sendTo]",
			"value" => (isset($this->mail["sendTo"])) ? $this->mail["sendTo"] : $user->getMailAddress()
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => (isset($this->mail["title"])) ? $this->mail["title"] : $this->convertMailContent($mail["title"], $mailLogic, $user, $order),
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => (isset($this->mail["content"])) ? $this->mail["content"] : $this->getMailContent($type, $order, $mail, $mailLogic, $user),
		));

		$this->addLabel("mail_type_text", array(
			"text" => $this->getMailText($type)
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

	function checkLanguageConfig($order){
		$attr = $order->getAttribute("util_multi_language");
		if(isset($attr["value"]) && strlen($attr["value"])){
			define("SOYSHOP_MAIL_LANGUAGE", $attr["value"]);
			define("SOYSHOP_PUBLISH_LANGUAGE", $attr["value"]);
		}
		MessageManager::addMessagePath("admin");
	}

	function getMailText($type){
		$array = array(
			"confirm" => "注文確認メール",
			"payment" => "支払確認メール",
			"delivery" => "配送連絡メール",
			"other" => "その他のメール",
			"other2" => "その他のメール"
		);

		if(isset($array[$type])) return $array[$type];

		//プラグインから出力したものを調べる
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_add_mail_type")) return "注文受付メール";

		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
		$configs = AddMailTypeUtil::getConfig();

		return (isset($configs[$type])) ? $configs[$type]["title"] : "注文受付メール";
	}

	function getMailContent($type, SOYShop_Order $order, $array, MailLogic $mailLogic, SOYShop_User $user){

		//システムからの出力を行うか？
		if(isset($array["output"]) && $array["output"] === 1){
			//メール本文を取得
			$builder = SOY2Logic::createInstance("logic.mail.MailBuilder");
	    	$body = $builder->buildOrderMailBodyForUser($order, $user);
		}else{
			$body = "";
		}


    	//プラグインを実行してメール本文の取得
    	SOYShopPlugin::load("soyshop.order.mail");

		//プラグインの拡張ポイントはメールの種類で分ける
    	$id = ($type == "order") ? "soyshop.order.mail.user" : "soyshop.order.mail." . $type;

    	//ダウンロードプラグインは拡張ポイントのIDはユーザにする
    	$pluginDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		try{
			$downloadPlugin = $pluginDAO->getByPluginId("download_assistant");
		}catch(Exception $e){
			$downloadPlugin = new SOYShop_PluginConfig();
		}

		$id = ($downloadPlugin->getIsActive() == 1) ? "soyshop.order.mail.user" : $id;
		
    	$delegate = SOYShopPlugin::invoke($id, array(
				"order" => $order,
				"mail" => $array
		));

		$appned_body = ($delegate) ? $delegate->getBody() : "";

		$mailBody = $array["header"] ."\n". $body . $appned_body . "\n" . $array["footer"];

		//convert
		$mailBody = $this->convertMailContent($mailBody, $mailLogic, $user, $order);

		return $mailBody;
	}

	function convertMailContent($str,MailLogic $mailLogic , SOYShop_User $user, SOYShop_Order $order){
		return $mailLogic->convertMailContent($str, $user, $order);
	}
}
