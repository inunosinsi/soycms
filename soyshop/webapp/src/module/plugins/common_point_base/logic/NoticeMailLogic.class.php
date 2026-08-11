<?php

class NoticeMailLogic extends SOY2LogicBase{

	private $config;
	private $pointDao;
	private $shopConfig;

	function __construct(){
		SOY2::imports("module.plugins.common_point_base.util.*");
		$this->config = PointBaseUtil::getConfig();
	}

	function execute(){


		//設定が無い場合は処理を終了
		if(!isset($this->config["mail"]) || (int)$this->config["mail"] < 1) {
			echo "送信設定がないため、メールの送信を中止しました。";
			return false;
		}

		$users = $this->getUsers();

		if(count($users) === 0) {
			echo "該当する顧客がいないため、メールの送信を中止しました。";
			return false;
		}

		//取得したユーザ情報を元にメール送信の処理を開始する
		$title = PointBaseUtil::getMailTitle();
		$content = PointBaseUtil::getMailContent();
		$content = $this->convertCompanyInfomation($content);

		//MailLogicの呼び出し
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

		$counter = 0;
		foreach($users as $user){
			if(strlen($user->getMailAddress()) === 0) continue;
			$title = $this->convertMailTitle($title);
			$body = $this->convertMailContent($content, $user);
			$mailLogic->sendMail($user->getMailAddress(), $title, $body);
			$counter++;
		}

		//動作確認
		echo $counter . "人にメールを送信しました。";
		return true;
	}

	/**
	 * 設定からポイント切れ間近の顧客一覧を取得する
	 * @return array(SOYShop_User);
	 */
	function getUsers(){
		$noticeDate = (int)$this->config["mail"];

		//範囲を作成する
		$start = $this->convertDateByCuttedTime(time() + $noticeDate * 24 * 60 * 60);
		$end = $this->convertDateByCuttedTime(time() + ($noticeDate + 1) * 24 * 60 * 60);

		SOY2::imports("module.plugins.common_point_base.domain.*");
		$this->pointDao = SOY2DAOFactory::create("SOYShop_PointDAO");
		return $this->pointDao->getUsersByNoticeDate($start, $end);
	}

	function convertMailTitle($title){
		$title = $this->convertCompanyInfomation($title);
		return trim($title);
	}

	/**
	 * @params String content, object SOYShop_User, object SOYShop_Item item
	 * @return String body
	 */
	function convertMailContent($content, SOYShop_User $user){
		//ユーザー情報
		$content = str_replace("#NAME#", $user->getName(), $content);
		$content = str_replace("#READING#", $user->getReading(), $content);
		$content = str_replace("#MAILADDRESS#", $user->getMailAddress(), $content);
		$content = str_replace("#BIRTH_YEAR#", $user->getBirthdayYear(), $content);
		$content = str_replace("#BIRTH_MONTH#", $user->getBirthdayMonth(), $content);
		$content = str_replace("#BIRTH_DAY#", $user->getBirthdayDay(), $content);

		$content = $this->convertTimeLimit($content, $user->getId());

		//最初に改行が存在した場合は改行を削除する
		return trim($content);
	}

	function convertTimeLimit($content, $userId){
		//すでにpointDaoは読み込まれている
		try{
			$point = $this->pointDao->getByUserId($userId);
		}catch(Exception $e){
			return $content;
		}

		$content = str_replace("#TIME_LIMIT#", date("Y-m-d H:i:s", $point->getTimeLimit()), $content);
		return $content;
	}

	function convertCompanyInfomation($content){

		if(!$this->shopConfig){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$this->shopConfig = SOYShop_ShopConfig::load();
		}
		$config = $this->shopConfig;

		$content = str_replace("#SHOP_NAME#", $config->getShopName(), $content);

		$company = $config->getCompanyInformation();
		foreach($company as $key => $value){
			$content = str_replace(strtoupper("#COMPANY_" . $key ."#"), $value, $content);
		}
		$content = str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);

		return $content;
	}

	function convertDateByCuttedTime($time){
		$dateArray = explode("-", date("Y-m-d", $time));
		return mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
	}
}
