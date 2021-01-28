<?php
SOY2::import("domain.config.SOYShop_ServerConfig");

class MailLogic extends SOY2LogicBase{

	const MODE_USER = "user";
	const MODE_ADMIN = "admin";

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
	const MAIL_CONFIG_TYPES = array("active" => 1, "output" => 1, "plugin" => 1, "title" => "[SOY Shop]", "header" => "", "footer" => "");

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
	 * @隠しモード sendToにadminを指定すると管理者のみにメールを送信する
	 */
	function sendMail($sendTo="admin", $title="", $body="", $sendToName = "", $order = null, $orderFlag = false){

		if(is_null($this->send)){
			$this->prepareSend();
		}

		if($sendTo == "admin") $sendTo = $this->serverConfig->getAdministratorMailAddress();

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
			$addEmails = SOYShopPlugin::invoke("soyshop.add.mailaddress", array(
				"order" => $order,
				"orderFlag" => $orderFlag
			))->getMailAddress();

			if(is_array($addEmails) && count($addEmails) > 0){
				$additionalEmails = array_merge($additionalEmails, $addEmails);
			}
		}
		if(is_array($additionalEmails) && count($additionalEmails)){
			foreach($additionalEmails as $email){
				$email = trim($email);
				if(strlen($email) && strpos($email, "@") !== false){
					$this->send->addBccRecipient($email);
				}
			}
		}

		//メールログ用に送信先のすべてのメールアドレスを一つの配列にまとめておく
		array_unshift($additionalEmails, $sendTo);

		try{
			//送信元メールアドレスが空の場合は成功フラグをtrueにするのみ
			if(strlen($this->serverConfig->getAdministratorMailAddress())) $this->send->send();
			$isSuccess = true;
		}catch(Exception $e){
			$isSuccess = false;
		}

		//メールの送信状況を記録する
		return self::saveLog($isSuccess, $isAdmin, $additionalEmails, $title, $body, $order);
	}

	//メールの送信状況を記録する
	private function saveLog($isSuccess, $isAdmin, $mails, $title, $body, $order = null){

		$logDao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");

		$log = new SOYShop_MailLog();
		$orderId = (!is_null($order)) ? $order->getId() : null;
		$userId = (!is_null($order) && $isAdmin === false) ? $order->getUserId() : null;

		//マイページの方で、メールアドレスからユーザIDの取得を一度だけ試してみる
		if(is_null($orderId) && is_null($userId)){
			$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			try{
				$userId = $userDao->getByMailAddress($mails[0])->getId();
			}catch(Exception $e){
				//
			}
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
				return $logDao->insert($log);
			}catch(Exception $e){
				return null;
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
		$config = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "user",
			"type" => $type
		))->getConfig();

		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			$config = array();
			foreach(self::MAIL_CONFIG_TYPES as $t => $v){
				$config[$t] = SOYShop_DataSets::get("mail.user.$type." . $t, $v);
			}
			return $config;
		}
	}

	/**
	 * 管理者に送信するメール設定の取得
	 * 互換性維持のため、order以外のメールはなければorderを取るようにしておく
	 */
	function getAdminMailConfig($type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";

		SOYShopPlugin::load("soyshop.mail.config");
		$config = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "admin",
			"type" => $type
		))->getConfig();

		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			$config = array();
			if("order" == $type){
				foreach(self::MAIL_CONFIG_TYPES as $t => $v){
					$config[$t] = SOYShop_DataSets::get("mail.admin." . $t, $v);
				}
			}else{
				foreach(self::MAIL_CONFIG_TYPES as $t => $v){
					$config[$t] = SOYShop_DataSets::get("mail.admin.$type." . $t, SOYShop_DataSets::get("mail.admin." . $t, $v));
				}
			}
			return $config;
		}
	}

	/**
	 * マイページで送信するメール設定の取得
	 */
	function getMyPageMailConfig($type = null){
		if(is_null($type) || strlen($type) < 1) $type = "remind";

		SOYShopPlugin::load("soyshop.mail.config");
		$config = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "send",
			"target" => "mypage",
			"type" => $type
		))->getConfig();

		if(is_array($config) && isset($config["title"]) && isset($config["header"]) && isset($config["footer"])){
			return $config;
		}else{
			$config = array();
			foreach(self::MAIL_CONFIG_TYPES as $t => $v){
				$config[$t] = SOYShop_DataSets::get("mail.mypage.$type." . $t, $v);
			}
			return $config;
		}
	}


	/**
	 * ユーザに送信するメール設定の保存
	 */
	function setUserMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";

		foreach(self::MAIL_CONFIG_TYPES as $t => $v){
			if(isset($mail[$t])) SOYShop_DataSets::put("mail.user.$type." . $t, $mail[$t]);
		}
	}
	/**
	 * 管理者に送信するメール設定の保存
	 */
	function setAdminMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "order";
		if("order" == $type){
			foreach(self::MAIL_CONFIG_TYPES as $t => $v){
				if(isset($mail[$t])) SOYShop_DataSets::put("mail.admin." . $t, $mail[$t]);
			}
		}else{
			foreach(self::MAIL_CONFIG_TYPES as $t => $v){
				if(isset($mail[$t])) SOYShop_DataSets::put("mail.admin.$type." . $t, $mail[$t]);
			}
		}
	}

	/**
	 * マイページのメール設定の保存
	 */
	function setMyPageMailConfig($mail, $type = null){
		if(is_null($type) || strlen($type) < 1) $type = "remind";
		foreach(self::MAIL_CONFIG_TYPES as $t => $v){
			if(isset($mail[$t])) SOYShop_DataSets::put("mail.mypage.$type." . $t, $mail[$t]);
		}
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

		//マイページログイン
		$content = str_replace("#MYPAGE_LOGIN#", soyshop_get_mypage_url(true) . "/login", $content);

		$adminUrl = $config->getAdminUrl();
		if(false === strpos($adminUrl, "http")){
			$adminUrl = "http://" . $_SERVER["SERVER_NAME"] . $adminUrl;
		}

    	$content = str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);
    	$content = str_replace("#ADMIN_URL#", $adminUrl, $content);
    	//$content = str_replace("#ADMIN_URL#",SOY2PageController::createRelativeLink("index.php", true),$content);

		//拡張ポイントで追加した置換文字列分
		SOYShopPlugin::load("soyshop.order.mail.replace");
		$content = SOYShopPlugin::invoke("soyshop.order.mail.replace", array(
			"mode" => "replace",
			"order" => $order,
			"content" => $content
		))->getContent();

		//最初に改行が存在した場合は改行を削除する
		return trim($content);
	}

	function buildMailBodyAndTitle(SOYShop_Order $order, $mailType, $mode = self::MODE_USER){
		static $builder;
		if(is_null($builder)) $builder = SOY2Logic::createInstance("logic.mail.MailBuilder");
		try{
			$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
		}catch(Exception $e){
			$user = new SOYShop_User();
		}

		if($mode == self::MODE_USER){
			$mailConfig = self::getUserMailConfig($mailType);
		}else{
			$mailConfig = self::getAdminMailConfig($mailType);
		}

		$isOutput = (isset($mailConfig["output"]) && (int)$mailConfig["output"] === 1);	//システムからの内容を出力するか？

		//プラグインを実行してメール本文の取得
		SOYShopPlugin::load("soyshop.order.mail");

		//顧客向けメール文面
		if($mode == self::MODE_USER){
			$body = ($isOutput) ? $builder->buildOrderMailBodyForUser($order, $user) . "\n" : "";
			$mailId = self::getOrderMailExtension($mailType);

		//管理者向けメール文面
		}else if($mode == self::MODE_ADMIN){
			$body = ($isOutput) ? $builder->buildOrderMailBodyForAdmin($order, $user) . "\n" : "";
			$mailId = "soyshop.order.mail.admin";
		}

		if(isset($mailConfig["plugin"]) && (int)$mailConfig["plugin"] === 1){
			$delegate = SOYShopPlugin::invoke($mailId, array(
				"order" => $order,
				"mail" => $mailConfig
			));

			$append_body = (!is_null($delegate)) ? $delegate->getBody() : "";
			if(strlen($append_body)) $body .= $append_body;
		}


		$mailBody =
			$mailConfig["header"] ."\n".
			$body . "\n" .
			$mailConfig["footer"];


		//置換文字列
		$title = self::convertMailContent($mailConfig["title"], $user, $order);
		$mailBody = self::convertMailContent($mailBody, $user, $order);
		return array($mailBody, $title);
	}

	function getOrderMailExtension($type){
		if($type == "order") return "soyshop.order.mail.user";
		$id = "soyshop.order.mail." . $type;

		SOY2::import("domain.order.SOYShop_Order");
		$res = array_search($type, SOYShop_Order::getMailTypes());
		if(is_numeric($res)) return $id;

		//soyshop.order.mailの拡張ポイントを増やす
		SOYShopPlugin::load("soyshop.order.detail.mail");
    	$list = SOYShopPlugin::invoke("soyshop.order.detail.mail", array())->getList();
		if(!count($list)) return "soyshop.order.mail.user";

		foreach($list as $configs){
			if(!count($configs)) continue;
			foreach($configs as $mailType => $config){
				if($id === "soyshop.order.mail." . $mailType){
					SOYShopPlugin::registerExtension($id, "SOYShopOrderMailDeletageAction");
					return $id;
				}
			}
		}

		return "soyshop.order.mail.user";	//念の為
	}

	function getShopConfig() {
		return $this->shopConfig;
	}
	function setShopConfig($shopConfig) {
		$this->shopConfig = $shopConfig;
	}
}
