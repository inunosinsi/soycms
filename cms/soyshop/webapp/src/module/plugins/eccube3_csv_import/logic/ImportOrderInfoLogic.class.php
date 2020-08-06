<?php

class ImportOrderInfoLogic extends ExImportLogicBase{

	private $labels = array("注文ID", "お名前(姓)", "お名前(名)", "お名前(セイ)", "お名前(メイ)", "郵便番号1", "郵便番号2", "都道府県(ID)", "住所1", "住所2", "メールアドレス", "TEL1", "TEL2", "TEL3", "FAX1", "FAX2", "FAX3", "性別(名称)", "職業(名称)", "誕生日", "受注日", "合計", "商品コード", "個数", "税率");
	private $factors = array();

	private $type;
	private $orderDao;
	private $attrDao;
	private $orderLogic;

	private $userDao;
	private $itemDao;

	const ORDER_ID = 0;	//注文ID
	const NAME = 1;		//お名前
	const READING = 3;	//フリガナ
	const ZIP = 5;		//郵便番号
	const ADRS = 7;		//住所
	const MAILADRS = 10;	//メールアドレス
	const TEL = 11;		//電話番号
	const FAX = 14;		//FAX
	const SEX = 17;		//性別
	const JOB = 18;		//職業
	const BIRTH = 19;	//誕生日

	const ODATE = 20;	//注文日
	const SUM = 21;		//合計
	const CODE = 22;	//商品コード
	const CNT = 23;		//個数
	const TAX = 24;		//税率

	function __construct(){
		$this->setCharset("Shift_JIS");
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$this->attrDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$this->orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}

	function execute(){
		set_time_limit(0);

		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = self::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(self::encodeFrom($lines[0]));

		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;

		unset($lines[0]);

		$this->orderDao->begin();
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = self::explodeLine(self::encodeFrom($line));

			//今からインポートする注文がすでに登録されていないか
			$odate = self::convertTimestamp($values[$this->factors[self::ODATE]]);
			if(!self::checkExistedOrder($odate)) continue;

			//商品が登録されているか調べる
			$itemId = self::getItemIdByCode($values[$this->factors[self::CODE]]);
			if(is_null($itemId)) continue;

			//顧客情報を調べる。なければ登録
			$userId = self::getUserIdByMailAddress($values[$this->factors[self::MAILADRS]]);
			if(is_null($userId)) $userId = self::registerUser($values);
			if(is_null($userId)) continue;

			$order = new SOYShop_Order();
			$order->setUserId($userId);
			$order->setPrice($values[$this->factors[self::SUM]]);
//			$order->setAddress();
//			$order->setClaimedAddress();
			$order->setAttribute("memo", null);
			$order->setModules(null);
			$order->setOrderDate($odate);
			$order->setTrackingNumber($this->orderLogic->getTrackingNumber($order));

			/** @ToDo 送料はどうする？ **/

			try{
				$id = $this->orderDao->insert($order);
			}catch(Exception $e){
				continue;
			}

			//注文があった場合は
			if(isset($id)){
				try{
					$item = $this->itemDao->getById($itemId);
				}catch(Exception $e){
					continue;
				}

				$iorder = new SOYShop_ItemOrder();
				$iorder->setOrderId($id);
				$iorder->setItemId($itemId);
				$iorder->setItemCount($values[$this->factors[self::CNT]]);

				$p = (int)$item->getPrice();
				if(isset($values[$this->factors[self::TAX]]) && is_numeric($values[$this->factors[self::TAX]])){
					$t = ((int)$values[$this->factors[self::TAX]] + 100) / 100;
					$p *= $t;
				}

				$iorder->setItemPrice($p);
				$iorder->setTotalPrice($p * (int)$values[$this->factors[self::CNT]]);
				$iorder->setItemName($item->getName());
				$iorder->setCdate($odate);

				try{
					$this->attrDao->insert($iorder);
				}catch(Exception $e){
					//
				}
			}
		}
		$this->orderDao->commit();
	}

	/**
	 * EC CUBEからダウンロードしてきたCSVにある表示されている項目の状況を調べる
	 * @param String カンマ区切りの文字列
	 */
	private function setFactors($line){
		foreach(explode(",", $line) as $n => $t){
			$i = array_search($t, $this->labels);
			if($i === false) continue;
			$this->factors[$i] = $n;
			unset($this->labels[$i]);
		}
	}

	/**
	 * @登録されている商品か調べる。登録されていればtrueを返す
	 * @param String code
	 * @return boolean
	 */
	private function getItemIdByCode($code){
		if(is_null($code)) return null;

		try{
			return $this->itemDao->getByCode($code)->getId();
		}catch(Exception $e){
			return null;
		}
	}

	private function getUserIdByMailAddress($email){
		try{
			return $this->userDao->getByMailAddress($email)->getId();
		}catch(Exception $e){
			return null;
		}
	}

	/**
	 * @受注管理ですでに登録されていないか調べる。なければtrueを返す
	 */
	private function checkExistedOrder($odate){
		try{
			$res = $this->orderDao->executeQuery("SELECT id FROM soyshop_order WHERE user_id = :uid AND order_date = :date LIMIT 1", array(":uid" => $res[0]["id"], ":date" => $odate));
		}catch(Exception $e){
			return true;
		}

		return (count($res) === 0);
	}

	private function registerUser($values){
		$user = new SOYShop_User();
		//メールアドレス
		if(isset($values[$this->factors[self::MAILADRS]])){
			$user->setMailAddress($values[$this->factors[self::MAILADRS]]);
		}

		//名前
		if(isset($values[$this->factors[self::NAME]])){
			$name = $values[$this->factors[self::NAME]];
			$name .= (isset($values[$this->factors[self::NAME + 1]])) ? $values[$this->factors[self::NAME + 1]] : "";
			$user->setName($name);
		}

		//フリガナ
		if(isset($values[$this->factors[self::READING]])){
			$reading = $values[$this->factors[self::READING]];
			$reading .= (isset($values[$this->factors[self::READING + 1]])) ? $values[$this->factors[self::READING + 1]] : "";
			$user->setReading($reading);
		}

		//郵便番号
		if(isset($values[$this->factors[self::ZIP]])){
			$zip = self::c($values[$this->factors[self::ZIP]]);
			$zip .= (isset($values[$this->factors[self::ZIP + 1]])) ? self::c($values[$this->factors[self::ZIP + 1]]) : "";
			$user->setZipCode($zip);
		}

		//住所
		if(isset($values[$this->factors[self::ADRS]])){
			$user->setArea($values[$this->factors[self::ADRS]]);
			if(isset($values[$this->factors[self::ADRS + 1]])) $user->setAddress1($values[$this->factors[self::ADRS + 1]]);
			if(isset($values[$this->factors[self::ADRS + 2]])) $user->setAddress2($values[$this->factors[self::ADRS + 2]]);
		}

		//電話番号
		if(isset($values[$this->factors[self::TEL]])){
			$tel = self::c($values[$this->factors[self::TEL]]);
			if($tel[0] != "0") $tel = "0" . $tel;
			if(isset($values[$this->factors[self::TEL + 1]])){
				$tel .= self::c($values[$this->factors[self::TEL + 1]]);
				if(isset($values[$this->factors[self::TEL + 2]])){
					$tel .= self::c($values[$this->factors[self::TEL + 1]]);
				}
			}
			if(strlen($tel) < 9) $tel = null;
			$user->setTelephoneNumber($tel);
		}

		//FAX
		if(isset($values[$this->factors[self::FAX]])){
			$fax = self::c($values[$this->factors[self::FAX]]);
			if($fax[0] != "0") $fax = "0" . $fax;
			if(isset($values[$this->factors[self::FAX + 1]])){
				$fax .= self::c($values[$this->factors[self::FAX + 1]]);
				if(isset($values[$this->factors[self::FAX + 2]])){
					$fax .= self::c($values[$this->factors[self::FAX + 1]]);
				}
			}
			if(strlen($fax) < 9) $fax = null;
			$user->setFaxNumber($fax);
		}

		//性別
		if(isset($values[$this->factors[self::SEX]])){
			if(mb_strpos($values[$this->factors[self::SEX]], "男") !== false){
				$user->setGender(SOYShop_User::USER_SEX_MALE);
			}else{
				$user->setGender(SOYShop_User::USER_SEX_FEMALE);
			}
		}

		//職業
		if(isset($values[$this->factors[self::JOB]])){
			$user->setJobName($values[$this->factors[self::JOB]]);
		}

		/**
		 * @ToDo 誕生日の取り扱い方法は不明
		 */

		//必ず入れる
		$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
		$user->setAttribute3("EC CUBE 3");

		try{
			return $this->userDao->insert($user);
		}catch(Exception $e){
			return null;
		}
	}

	private function c($s){
		if(strlen($s) === 0) return "";
		return mb_convert_kana(trim($s), "a");
	}

	private function convertTimestamp($time){
		$array = explode(" ", trim($time)); //[0]には日、[1]には時間
		$dates = explode("-", trim($array[0]));
		$times = explode(":", trim($array[1]));
		return mktime($times[0], $times[1], $times[2], $dates[1], $dates[2], $dates[0]);		//時、分、秒、月、日、年
	}

	function setType($type){
		$this->type = $type;
	}
}
