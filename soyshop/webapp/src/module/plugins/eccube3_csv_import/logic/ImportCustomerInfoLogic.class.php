<?php

class ImportCustomerInfoLogic extends ExImportLogicBase{

	private $labels = array("会員ID","お名前(姓)","お名前(名)","お名前(セイ)","お名前(メイ)","郵便番号1","郵便番号2","都道府県(ID)","住所1","住所2","メールアドレス","TEL1","TEL2","TEL3","FAX1","FAX2","FAX3","性別(名称)","職業(名称)","誕生日","ショップ用メモ欄","登録日","更新日");
	private $factors = array();

	const NAME = 1;	//お名前
	const READING = 3;	//フリガナ
	const ZIP = 5;		//郵便番号
	const ADRS = 7;		//住所
	const MAILADRS = 10;	//メールアドレス
	const TEL = 11;		//電話番号
	const FAX = 14;		//FAX
	const SEX = 17;		//性別
	const JOB = 18;		//職業
	const BIRTH = 19;	//誕生日
	const MEMO = 20;	//備考
	const REG = 21;		//登録日
	const UPD = 22;		//更新日

	private $type;
	private $userDao;

	function __construct(){
		$this->setCharset("Shift_JIS");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	function execute(){
		set_time_limit(0);

		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = parent::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(parent::encodeFrom($lines[0]));

		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;

		unset($lines[0]);

		//ループが始まる前に住所の配列を読み込んでおく
		$areas = SOYShop_Area::getAreas();

		$this->userDao->begin();
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = parent::explodeLine(parent::encodeFrom($line));

			//メールアドレスがない、もしくは重複する場合はcontinue
			if(!self::checkExistedUser($values[$this->factors[self::MAILADRS]])) continue;

			$user = new SOYShop_User();

			//$i = 0;は会員IDのためなし。$i = 1;から始める
			for($i = 1; $i < count($this->factors); $i++){

				//項目がない場合はスルー
				if(!isset($this->factors[$i]) || !strlen($values[$this->factors[$i]])) continue;

				//$this->factors[$i]に該当するカラム番号が収納されている
				switch($i){
					case self::NAME:	//お名前
					case self::READING:	//フリガナ
						$name = $values[$this->factors[$i]];
						//名を取得
						if(isset($values[$this->factors[++$i]])) $name .= " " . $values[$this->factors[$i]];
						if($i < self::READING){
							$user->setName($name);		//名前
						}else{
							$user->setReading($name);	//フリガナ
						}
						break;
					case self::ZIP:		//郵便番号
						$zip = $values[$this->factors[$i]];
						if(isset($values[$this->factors[++$i]])) $zip .= $values[$this->factors[$i]];
						$user->setZipCode($zip);
						break;
					case self::ADRS:		//住所　連続して調べる。ループで戻るよりかマシ
						$pref = array_search($values[$this->factors[$i]], $areas);
						if(isset($pref) && is_numeric($pref) && $pref > 0) $user->setArea($pref);
						if(isset($values[$this->factors[++$i]])) $user->setAddress1($values[$this->factors[$i]]);
						if(isset($values[$this->factors[++$i]])) $user->setAddress2($values[$this->factors[$i]]);
						break;
					case self::MAILADRS:	//メールアドレス
						$user->setMailAddress($values[$this->factors[$i]]);
						break;
					case self::TEL:	//電話番号
					case self::FAX:	//FAX
						$tel = ((string)$values[$this->factors[$i]][0] != 0) ? "0" . $values[$this->factors[$i]] : $values[$this->factors[$i]];
						if(isset($values[$this->factors[++$i]])) $tel .= $values[$this->factors[$i]];
						if(isset($values[$this->factors[++$i]])) $tel .= $values[$this->factors[$i]];
						if($i < self::FAX) {
							$user->setTelephoneNumber($tel);	//電話番号
						}else{
							$user->setFaxNumber($tel);			//FAX
						}
						break;
					case self::SEX:	//性別
						if(!isset($values[$this->factors[$i]]) || !strlen($values[$this->factors[$i]])) break;
						if(mb_strpos($values[$this->factors[$i]], "男") !== false){
							$user->setGender(SOYShop_User::USER_SEX_MALE);
						}else{
							$user->setGender(SOYShop_User::USER_SEX_FEMALE);
						}
						break;
					case self::JOB:	//職業
						$user->setJobName($values[$this->factors[$i]]);
						break;
					case self::BIRTH:	//誕生日
						/**
						 * @ToDo 値の持ち方が分かったら実装
						 */
						break;
					case self::MEMO:	//備考
						$user->setMemo($values[$this->factors[$i]]);
						break;
					case self::REG:		//登録日
						$d = self::convertTimestamp($values[$this->factors[$i]]);
						$user->setRegisterDate($d);
						$user->setRealRegisterDate($d);
						break;
					case self::UPD:		//更新日
						$d = self::convertTimestamp($values[$this->factors[$i]]);
						$user->setUpdateDate($d);
						break;
				}
			}

			//必ず入れる値 本登録ユーザにする
			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
			$user->setAttribute3("EC CUBE 3");

			try{
				$this->userDao->insert($user);
			}catch(Exception $e){
				//
			}
		}

		$this->userDao->commit();
	}

	/**
	 * EC CUBEからダウンロードしてきたCSVにある表示されている項目の状況を調べる
	 * @param String カンマ区切りの文字列
	 */
	private function setFactors($line){
		foreach(explode(",", $line) as $n => $t){
			$i = array_search($t, $this->labels);
			if(!$i) continue;
			$this->factors[$i] = $n;
			unset($this->labels[$i]);
		}
	}

	/**
	 * 既に顧客が存在していればfalseを返す
	 * @param String
	 * @return boolean
	 */
	private function checkExistedUser($email){
		if(!isset($email) || strlen($email) === 0) return false;

		try{
			$res = $this->userDao->executeQuery("SELECT id FROM soyshop_user WHERE mail_address = :mail_address", array(":mail_address" => $email));
		}catch(Exception $e){
			return true;
		}

		return ((int)$res[0]["id"] < 1);
	}

	private function convertTimestamp($time){
		$array = explode(" ", substr($time, 0, strrpos($time, "."))); //[0]には日、[1]には時間
		$dates = explode("-", $array[0]);
		$times = explode(":", $array[1]);
		return mktime($times[0], $times[1], $times[2], $dates[1], $dates[2], $dates[0]);		//時、分、秒、月、日、年
	}

	function setType($type){
		$this->type = $type;
	}
}
