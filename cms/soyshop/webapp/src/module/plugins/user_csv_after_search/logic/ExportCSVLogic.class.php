<?php

class ExportCSVLogic extends SOY2LogicBase{

	private $userDao;

	function __construct(){
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("util.SOYShopPluginUtil");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	function export($users){

		set_time_limit(0);
		$lines = array();

		foreach($users as $user){
			$lines[] = self::convertLineByUserObject($user);
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";

		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=user_csv_after_search" . date("YmdHis") . ".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

		ob_start();
		echo implode("," , self::getLabels());
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();

		echo mb_convert_encoding($csv,$charset,"UTF-8");
	}

	private function convertLineByUserObject(SOYShop_User $user){
		$line = array();

		$line[] = $user->getId();
		$line[] = $user->getMailAddress();
		$line[] = $user->getName();
		$line[] = $user->getReading();
		$line[] = $user->getNickName();

		//性別
		if(!is_null($user->getGender())){
			$gender = ($user->getGender() == SOYShop_User::USER_SEX_MALE) ? "男性" : "女性";
		}else{
			$gender = "";
		}
		$line[] = $gender;

		//誕生日
		$line[] = $user->getBirthday();

		$line[] = $user->getZipCode();
		$line[] = (!is_null($user->getArea())) ? SOYShop_Area::getAreaText($user->getArea()) : null;
		$line[] = $user->getAddress1();
		$line[] = $user->getAddress2();
		$line[] = $user->getAddress3();

		$line[] = (!is_null($user->getTelephoneNumber()) && strlen($user->getTelephoneNumber())) ? "=\"" . $user->getTelephoneNumber() . "\"" : null;
		$line[] = (!is_null($user->getFaxNumber()) && strlen($user->getFaxNumber())) ? "=\"" . $user->getFaxNumber() . "\"" : null;
		$line[] = (!is_null($user->getCellphoneNumber()) && strlen($user->getCellphoneNumber())) ? "=\"" . $user->getCellphoneNumber() . "\"" : null;
		$line[] = $user->getUrl();

		$line[] = $user->getJobName();
		$line[] = $user->getJobZipCode();
		$line[] = (!is_null($user->getJobArea())) ? SOYShop_Area::getAreaText($user->getJobArea()) : null;
		$line[] = $user->getJobAddress1();
		$line[] = $user->getJobAddress2();

		$line[] = (!is_null($user->getJobTelephoneNumber()) && strlen($user->getJobTelephoneNumber())) ? "=\"" . $user->getJobTelephoneNumber() . "\"" : null;
		$line[] = (!is_null($user->getJobFaxNumber()) && strlen($user->getJobFaxNumber())) ? "=\"" . $user->getJobFaxNumber() . "\"" : null;

		$line[] = $user->getAttribute1();
		$line[] = $user->getAttribute2();
		$line[] = $user->getAttribute3();

		$line[] = $user->getMemo();

		if(SOYShopPluginUtil::checkIsActive("common_point_base")){
			$line[] = $user->getPoint();
		}

		return implode(",", $line);
	}

	private function getLabels(){
		$labels = array(
			"id" => "ID",

			"mailAddress" => "メールアドレス",
			"name" => "名前",
			"reading" => "フリガナ",
			"nickname" => "ニックネーム",
			"genderText" => "性別",
			"birthdayText" => "生年月日",

			"zipCode" => "郵便番号",
			"areaText" => "住所（都道府県）",
			"address1" => "住所１",
			"address2" => "住所２",
			"address3" => "住所３",
			"telephoneNumber" => "電話番号",
			"faxNumber" => "FAX番号",

			"cellphoneNumber" => "携帯電話",
			"url" => "URL",
			"jobName" => "勤務先名称・職種",
			"jobZipCode" => "勤務先郵便番号",
			"jobAreaText" => "勤務先住所（都道府県）",
			"jobAddress1" => "勤務先住所１",
			"jobAddress2" => "勤務先住所２",

			"jobTelephoneNumber" => "勤務先電話番号",
			"jobFaxNumber" => "勤務先FAX番号",
			"attribute1" => "属性１",
			"attribute2" => "属性２",
			"attribute3" => "属性３",
			"memo" => "備考",
		);

		if(SOYShopPluginUtil::checkIsActive("common_point_base")){
			$labels["point"] = "ポイント";
		}

		return $labels;
	}

	function getUserIdListByOrders($orders){
		if(!count($orders)) return array();

		$list = array();
		foreach($orders as $order){
			if(array_search($order->getUserId(), $list) === false){
				$list[] = (int)$order->getUserId();
			}
		}

		if(!count($list)) return array();

		sort($list);

		try{
			$res = $this->userDao->executeQuery("SELECT * FROM soyshop_user WHERE id IN (" . implode(",", $list) . ")", array());
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$users = array();
		foreach($res as $v){
			$users[] = $this->userDao->getObject($v);
		}

		return $users;
	}
}
?>
