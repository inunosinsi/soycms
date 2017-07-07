<?php
SOY2::import("domain.config.SOYShop_ServerConfig");

class MailLogic extends SOY2LogicBase{

	//admin user
	const TYPE_ORDER   = "order";
	const TYPE_PAYMENT = "payment";
	//user
	const TYPE_CONFIRM  = "confirm";
	const TYPE_DELIVERY = "delivery";
	//mypage
	const TYPE_REMIND       = "remind";
	const TYPE_TMP_REGISTER = "tmp_register";
	const TYPE_REGISTER     = "register";

	//管理者宛
	const TYPE_NAME_FOR_ORDER        = "注文受付メール";
	const TYPE_NAME_FOR_PAYMENT      = "支払通知メール";
	//購入者宛
	const TYPE_NAME_FOR_ORDER_TO_USER   = "注文受付メール";
	const TYPE_NAME_FOR_PAYMENT_TO_USER = "支払確認メール";
	const TYPE_NAME_FOR_CONFIRM      = "注文確定メール";
	const TYPE_NAME_FOR_DELIVERY     = "配送連絡メール";
	const TYPE_NAME_FOR_REMIND       = "パスワード再設定メール";
	const TYPE_NAME_FOR_TMP_REGISTER = "仮登録完了メール";
	const TYPE_NAME_FOR_REGISTER     = "登録完了メール";

	const TYPE_NAME_FOR_UNKNOWN = "種類の不明なメール";

	private $serverConfig;
	private $shopConfig;
	private $send;
	private $receive;

	function getServerConfig() {
		return $this->serverConfig;
	}
	function setServerConfig($serverConfig) {
		$this->serverConfig = $serverConfig;
	}
	function getSend() {
		return $this->send;
	}
	function setSend($send) {
		$this->send = $send;
	}
	function getReceive() {
		return $this->receive;
	}
	function setReceive($receive) {
		$this->receive = $receive;
	}

	/**
	 * pop
	 */
	function prepare(){
		$serverConfig = $this->serverConfig;

		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_POP
			&& $serverConfig->getReceiveServerType() != SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_IMAP
			){
				throw new Exception("invalid receive server type");
			}

			//before smtp
			$this->receive = $serverConfig->createReceiveServerObject();
			$this->receive->open();
			$this->receive->close();
		}
	}

	/**
	 * 準備
	 */
	function prepareSend(){

		$serverConfig = SOYShop_ServerConfig::load();
		$this->serverConfig = $serverConfig;

		$this->prepare();

		//SOY2Mail
		$this->send = $serverConfig->createSendServerObject();

	}

    /**
	 * 一通送信する
	 */
	function sendMail($sendTo, $title, $body, $sendToName = "", $order = null, $orderFlag = false){

		if(is_null($this->send)){
			$this->prepareSend();
		}

		//リセット
		$this->reset();

		//文字コード
		$encoding = $this->serverConfig->getEncodingByEmailAddress($sendTo);
		$this->send->setEncoding($encoding);
		$this->send->setSubjectEncoding($encoding);

		//送信元アドレス設定
		$this->send->setFrom($this->serverConfig->getAdministratorMailAddress(), $this->serverConfig->getAdministratorName());
		if(strlen($this->serverConfig->getReturnMailAddress()) > 0){
			$replyTo = new SOY2Mail_MailAddress($this->serverConfig->getReturnMailAddress(), $this->serverConfig->getReturnName(), $encoding);
			$this->send->setHeader("Reply-To", $replyTo->getString());
		}


		$this->send->setSubject($title);
		$this->send->setText($body);

		$this->send->addRecipient($sendTo, $sendToName);

		//管理者にコピーを送る設定の時
		if($this->serverConfig->isSendWithAdministrator() && $sendTo != $this->serverConfig->getAdministratorMailAddress()){
			$this->send->addBccRecipient($this->serverConfig->getAdministratorMailAddress(), $this->serverConfig->getAdministratorName());
		}

		//管理側に送るメールのフラグ
		$isAdmin = false;

		//追加の送信先
		if($sendTo != $this->serverConfig->getAdministratorMailAddress()){
			//ユーザー向けメールの時
			$additionalEmails = $this->serverConfig->getAdditionalMailAddressForUserMailArray();
		}else{
			//管理者向けメールの時
			$additionalEmails = $this->serverConfig->getAdditionalMailAddressForAdminMailArray();
			$isAdmin = true;

			//別アプリとの連携→メールの送り先を増やす
			SOYShopPlugin::load("soyshop.add.mailaddress");
			$delegate = SOYShopPlugin::invoke("soyshop.add.mailaddress", array(
				"order" => $order,
				"orderFlag" => $orderFlag
			));
			$addEmails = $delegate->getMailAddress();
			if(is_array($addEmails) && count($addEmails) > 0){
				$additionalEmails = array_merge($additionalEmails, $addEmails);
			}
		}
		if(is_array($additionalEmails) && count($additionalEmails)){
			foreach($additionalEmails as $email){
				if(strlen(trim($email)) && strpos($email, "@") !== false){
					$this->send->addBccRecipient(trim($email));
				}
			}
		}

		//メールログ用に送信先のすべてのメールアドレスを一つの配列にまとめておく
		array_unshift($additionalEmails, $sendTo);

		try{
			$this->send->send();
			$isSuccess = true;
		}catch(Exception $e){
			$isSuccess = false;
		}

		//メールの送信状況を記録する
		$this->saveLog($isSuccess, $isAdmin, $additionalEmails, $title, $body, $order);
	}

	//メールの送信状況を記録する
	function saveLog($isSuccess, $isAdmin, $mails, $title, $body, $order = null){

		$logDao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");

		$log = new SOYShop_MailLog();
		$orderId = (!is_null($order)) ? $order->getId() : null;
		$userId = (!is_null($order) && $isAdmin === false) ? $order->getUserId() : null;

		//マイページの方で、メールアドレスからユーザIDの取得を一度だけ試してみる
		if(is_null($orderId) && is_null($userId)){
			$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			try{
				$user = $userDao->getByMailAddress($mails[0]);
			}catch(Exception $e){
				$user = new SOYShop_User();
			}
			$userId = $user->getId();
		}

		if(count($mails) > 0){
			$log->setRecipient(implode(",", $mails));
			$log->setOrderId($orderId);
			$log->setUserId($userId);
			$log->setTitle($title);
			$log->setContent($body);
			$log->setIsSuccess($isSuccess);
			$log->setSendDate(time());

			try{
				$logDao->insert($log);
			}catch(Exception $e){
				//
			}
		}
	}

	/**
	 * 件名、本文、受信者、BCC受信者、ヘッダー、添付ファイルをリセット
	 * SOY2MailはsetTextだけではencodedTextが上書きされない
	 */
	function reset(){
		$this->send->clearSubject();
		$this->send->clearText();
		$this->send->clearRecipients();
		$this->send->clearBccRecipients();
		$this->send->clearHeaders();
		//1.6.1現時点で添付ファイルの仕様はないのでコメントアウトをしておく
//		$this->send->clearAttachments();
	}


	/**
	 * @return boolean
	 */
	public function isValidEmail($email){
		$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
		$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
		$d3     = '\d{1,3}';
		$ip     = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
		$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

		if(! preg_match('/'.$validEmail.'/i', $email) ) {
			return false;
		}

    	return true;
	}

	/**
	 * メールの種類名を返す
	 * @param String 種類
	 * @param boolean 管理者宛かどうか
	 * @return String
	 */
	public function getMailTypeName($type, $isAdmin = false){
		if($isAdmin){
			switch($type){
				case self::TYPE_ORDER:
					return self::TYPE_NAME_FOR_ORDER;
				case self::TYPE_PAYMENT:
					return self::TYPE_NAME_FOR_PAYMENT;
			}
		}else{
			switch($type){
				case self::TYPE_ORDER:
					return self::TYPE_NAME_FOR_ORDER_TO_USER;
				case self::TYPE_PAYMENT:
					return self::TYPE_NAME_FOR_PAYMENT_TO_USER;
				case self::TYPE_CONFIRM:
					return self::TYPE_NAME_FOR_CONFIRM;
				case self::TYPE_DELIVERY:
					return self::TYPE_NAME_FOR_DELIVERY;
				case self::TYPE_REMIND:
					return self::TYPE_NAME_FOR_REMIND;
				case self::TYPE_TMP_REGISTER:
					return self::TYPE_NAME_FOR_TMP_REGISTER;
				case self::TYPE_REGISTER:
					return self::TYPE_NAME_FOR_REGISTER;
			}
		}
		return self::TYPE_NAME_FOR_UNKNOWN;
	}

	/**
	 * ユーザに送信するメール設定の取得
	 */
	function getUserMailConfig($type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "user",
			"type" => $type
		));
		$config = $delegate->getConfig();
		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			return array(
				"active" => SOYShop_DataSets::get("mail.user.$type.active", 1),
				"title"  => SOYShop_DataSets::get("mail.user.$type.title", "[SOY Shop]"),
		    	"header" => SOYShop_DataSets::get("mail.user.$type.header", ""),
		    	"footer" => SOYShop_DataSets::get("mail.user.$type.footer", "")
		    );
		}
	}

	/**
	 * 管理者に送信するメール設定の取得
	 * 互換性維持のため、order以外のメールはなければorderを取るようにしておく
	 */
	function getAdminMailConfig($type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "admin",
			"type" => $type
		));
		$config = $delegate->getConfig();
		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			if("order" == $type){
				return array(
					"active" => SOYShop_DataSets::get("mail.admin.active", 1),
					"title"  => SOYShop_DataSets::get("mail.admin.title", "[SOY Shop]"),
					"header" => SOYShop_DataSets::get("mail.admin.header", ""),
					"footer" => SOYShop_DataSets::get("mail.admin.footer", "")
				);
			}else{
				return array(
					"active" => SOYShop_DataSets::get("mail.admin.$type.active",SOYShop_DataSets::get("mail.admin.active",1)),
					"title"  => SOYShop_DataSets::get("mail.admin.$type.title", SOYShop_DataSets::get("mail.admin.title","[SOY Shop]")),
					"header" => SOYShop_DataSets::get("mail.admin.$type.header",SOYShop_DataSets::get("mail.admin.header","")),
					"footer" => SOYShop_DataSets::get("mail.admin.$type.footer",SOYShop_DataSets::get("mail.admin.footer",""))
				);
			}
		}
	}

	/**
	 * マイページで送信するメール設定の取得
	 */
	function getMyPageMailConfig($type = null){
		if(is_null($type) || strlen($type) < 1) $type = "remind";

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "mypage",
			"type" => $type
		));
		$config = $delegate->getConfig();
		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			return array(
				"active" => SOYShop_DataSets::get("mail.mypage.$type.active",1),
				"title"  => SOYShop_DataSets::get("mail.mypage.$type.title", "[SOY Shop]"),
				"header" => SOYShop_DataSets::get("mail.mypage.$type.header",""),
				"footer" => SOYShop_DataSets::get("mail.mypage.$type.footer","")
			);
		}
	}


	/**
	 * ユーザに送信するメール設定の保存
	 */
	function setUserMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";
		if(isset($mail["active"]))SOYShop_DataSets::put("mail.user.$type.active",$mail["active"]);
		if(isset($mail["title"])) SOYShop_DataSets::put("mail.user.$type.title", $mail["title"]);
		if(isset($mail["header"]))SOYShop_DataSets::put("mail.user.$type.header",$mail["header"]);
	    if(isset($mail["footer"]))SOYShop_DataSets::put("mail.user.$type.footer",$mail["footer"]);
	}
	/**
	 * 管理者に送信するメール設定の保存
	 */
	function setAdminMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";
		if("order" == $type){
			if(isset($mail["active"]))SOYShop_DataSets::put("mail.admin.active",$mail["active"]);
			if(isset($mail["title"])) SOYShop_DataSets::put("mail.admin.title", $mail["title"]);
			if(isset($mail["header"]))SOYShop_DataSets::put("mail.admin.header",$mail["header"]);
			if(isset($mail["footer"]))SOYShop_DataSets::put("mail.admin.footer",$mail["footer"]);
		}else{
			if(isset($mail["active"]))SOYShop_DataSets::put("mail.admin.$type.active",$mail["active"]);
			if(isset($mail["title"])) SOYShop_DataSets::put("mail.admin.$type.title", $mail["title"]);
			if(isset($mail["header"]))SOYShop_DataSets::put("mail.admin.$type.header",$mail["header"]);
			if(isset($mail["footer"]))SOYShop_DataSets::put("mail.admin.$type.footer",$mail["footer"]);
		}
	}

	/**
	 * マイページのメール設定の保存
	 */
	function setMyPageMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "remind";
		if(isset($mail["active"]))SOYShop_DataSets::put("mail.mypage.$type.active",$mail["active"]);
		if(isset($mail["title"])) SOYShop_DataSets::put("mail.mypage.$type.title", $mail["title"]);
		if(isset($mail["header"]))SOYShop_DataSets::put("mail.mypage.$type.header",$mail["header"]);
    	if(isset($mail["footer"]))SOYShop_DataSets::put("mail.mypage.$type.footer",$mail["footer"]);
	}

	/**
	 * メール本文を置換
	 */
	function convertMailContent($content, SOYShop_User $user, SOYShop_Order $order){
		//ユーザー情報
		$content = str_replace("#NAME#", $user->getName(), $content);
		$content = str_replace("#READING#", $user->getReading(), $content);
		$content = str_replace("#MAILADDRESS#", $user->getMailAddress(), $content);
		$content = str_replace("#BIRTH_YEAR#", $user->getBirthdayYear(), $content);
		$content = str_replace("#BIRTH_MONTH#", $user->getBirthdayMonth(), $content);
		$content = str_replace("#BIRTH_DAY#", $user->getBirthdayDay(), $content);

		//注文情報
		$content = str_replace("#ORDER_RAWID#", $order->getId(), $content);
		$content = str_replace("#ORDER_ID#", $order->getTrackingNumber(), $content);
		$config = $this->getShopConfig();
		if(!$config){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$config = SOYShop_ShopConfig::load();
			$this->setShopConfig($config);
		}

		$content = str_replace("#SHOP_NAME#", $config->getShopName(), $content);

		$company = $config->getCompanyInformation();
    	foreach($company as $key => $value){
    		$content = str_replace(strtoupper("#COMPANY_" . $key ."#"), $value, $content);
    	}

		$adminUrl = $config->getAdminUrl();
		if(false === strpos($adminUrl, "http")){
			$adminUrl = "http://" . $_SERVER["SERVER_NAME"] . $adminUrl;
		}

    	$content = str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);
    	$content = str_replace("#ADMIN_URL#", $adminUrl, $content);
    	//$content = str_replace("#ADMIN_URL#",SOY2PageController::createRelativeLink("index.php", true),$content);

		//最初に改行が存在した場合は改行を削除する
		return trim($content);
	}

	function getShopConfig() {
		return $this->shopConfig;
	}
	function setShopConfig($shopConfig) {
		$this->shopConfig = $shopConfig;
	}
}
