<?php

class NoticeSendMailLogic extends SOY2LogicBase {

	private $mailLogic;
	private $builder;
	private $config;

	private $order;	//ロジックのコンストラクト時にセットしておくこと
	private $user;	//ロジックのコンストラクト時にセットしておくこと

	function __construct(){
		$this->mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$this->builder = SOY2Logic::createInstance("logic.mail.MailBuilder");
		SOY2::import("module.plugins.order_edit_on_mypage.util.OrderEditOnMyPageUtil");
		$this->config = OrderEditOnMyPageUtil::getMailConfig();
	}

	function send($content){
		$userName = $this->user->getName();
		if(strlen($userName) > 0) $userName .= " 様";

		$title = self::buildTitle();
		$body = self::buildBody($content);

		$this->mailLogic->sendMail($this->user->getMailAddress(), $title, $body, $userName, $this->order);
	}

	private function buildTitle(){
		return $this->mailLogic->convertMailContent($this->config["title"], $this->user, $this->order);
	}

	private function buildBody($content){

		//プラグインを実行してメール本文の取得
		SOYShopPlugin::load("soyshop.order.mail");
		$appned_body = SOYShopPlugin::invoke("soyshop.order.mail.user", array(
			"order" => $this->order,
			"mail" => $this->mailLogic->getUserMailConfig()
		))->getBody();

		$mailBody = $this->config["header"] ."\n\n" .
					"・変更内容\n" .
					$content . "\n\n\n".
					"・注文内容\n" .
					$this->builder->buildOrderMailBodyForUser($this->order, $this->user) . "\n" .
					$appned_body .
					$this->config["footer"];

		return $this->mailLogic->convertMailContent($mailBody, $this->user, $this->order);
	}

	function setOrder($order){
		$this->order = $order;
	}

	function setUser($user){
		$this->user = $user;
	}
}
