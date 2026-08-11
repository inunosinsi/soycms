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
				$order = new SOYShop_Order();
				$order->setUserId($this->id);

				$sendToName = "";
				$mail = unserialize(base64_decode($_POST["mail_value"]));
				$this->mailLogic->sendMail($mail["sendTo"], $mail["title"], $mail["content"], $sendToName, $order);

				//管理者に送信するメール
				$this->mailLogic->sendMail("admin", "【確認用】" . $mail["title"], $mail["content"], $sendToName, $order);

				SOY2PageController::jump("User.Detail." . $this->id . "?sended");
			}catch(Exception $e){
				$this->error = true;
			}
		}else{
			$this->mail = $_POST["Mail"];
		}
	}

	function __construct($args){

		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		$user = soyshop_get_user_object($this->id);
		if(!$user->isUsabledEmail()) SOY2PageController::jump("User.Detail." . $this->id);

		SOY2::import("domain.order.SOYShop_Order");
		$this->mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

		//メール送信時の言語設定
		//$this->checkLanguageConfig($order);

		$type = (isset($_GET["type"])) ? $_GET["type"] : "user";
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
			"value" => (isset($this->mail["title"])) ? $this->mail["title"] : $this->mailLogic->convertMailContent($mail["title"], $user, new SOYShop_Order()),
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => (isset($this->mail["content"])) ? $this->mail["content"] : self::_getMailContent($type, $mail, $user),
		));

		$this->addLabel("mail_type_text", array(
			"text" => self::_getMailText($type)
		));

		$this->addLink("user_detail_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $this->id),
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
			"text" => SOY2PageController::createLink("User.Storage." . $user->getId())
		));
	}

	private function checkLanguageConfig($order){
		$attr = $order->getAttribute("util_multi_language");
		if(isset($attr["value"]) && strlen($attr["value"])){
			define("SOYSHOP_MAIL_LANGUAGE", $attr["value"]);
			define("SOYSHOP_PUBLISH_LANGUAGE", $attr["value"]);
		}
		MessageManager::addMessagePath("admin");
	}

	private function _getMailText($type){
		$array = array(
			"user" => "顧客宛メール",
			"other" => "その他のメール"
		);

		if(isset($array[$type])) return $array[$type];

		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_add_mail_type")) {
			SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
			$configs = AddMailTypeUtil::getConfig(AddMailTypeUtil::MAIL_TYPE_USER);

			if(isset($configs[$type])) return $configs[$type]["title"];
		//メール送信種類追加プラグイン以外
		}else{
			// @ToDo　必要になったら実装する
		}

		return $array["other"];
	}

	private function _getMailContent($type, $array, SOYShop_User $user){
		$body = "";
		$mailBody = $array["header"] ."\n". $body . "\n" . $array["footer"];

		//convert
		return $this->mailLogic->convertMailContent($mailBody, $user, new SOYShop_Order());
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("メール送信", array("User" => SHOP_USER_LABEL . "管理", "User.Detail." . $this->id => SHOP_USER_LABEL . "情報詳細"));
	}
}
