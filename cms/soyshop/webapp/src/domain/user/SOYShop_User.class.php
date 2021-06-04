<?php
SOY2::import("domain.config.SOYShop_Area");

/**
 * @table soyshop_user
 */
class SOYShop_User {

	/* ユーザタイプ */
	const USERTYPE_TMP = 10;//仮登録
	const USERTYPE_REGISTER = 1;//本登録

	const USER_NO_ERROR = 0;
	const USER_IS_ERROR = 1;		//エラーが出たユーザ(SOY Mail)

	const USER_SEND = 0;
	const USER_NOT_SEND = 1;		//送信しないユーザ(SOY Mail)

	const USER_NO_PUBLISH = 0;		//カスタムサーチフィールド利用時で検索対象にするか？
    const USER_IS_PUBLISH = 1;

	/* 削除フラグ */
	const USER_NOT_DISABLED = 0;	//アクティブユーザ
	const USER_IS_DISABLED = 1;		//削除されたユーザ

	const PROFILE_NO_DISPLAY = 0;	//プロフィール非表示
	const PROFILE_IS_DISPLAY = 1;	//プロフィール表示

	const USER_SEX_MALE = 0;	//男性
	const USER_SEX_FEMALE = 1;	//女性

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column mail_address
	 */
	private $mailAddress;

	/**
	 * @column user_code
	 */
	private $userCode;

	private $password;

	private $attribute1;
	private $attribute2;
	private $attribute3;

	private $name;
	private $nickname;
	private $honorific;
	private $reading;

	/**
	 * @column account_id
	 */
	private $accountId;

	/**
	 * @column profile_id
	 */
	private $profileId;

	/**
	 * @column image_path
	 */
	private $imagePath;

	private $gender;

	private $birthday;

	/**
	 * @column zip_code
	 */
	private $zipCode;

	private $area;
	private $address1;
	private $address2;
	private $address3;

	/**
	 * @column telephone_number
	 */
	private $telephoneNumber;

	/**
	 * @column fax_number
	 */
	private $faxNumber;

	/**
	 * @column cellphone_number
	 */
	private $cellphoneNumber;
	private $url;

	/**
	 * @column job_name
	 */
	private $jobName;

	/**
	 * @column job_zip_code
	 */
	private $jobZipCode;

	/**
	 * @column job_area
	 */
	private $jobArea;

	/**
	 * @column job_address1
	 */
	private $jobAddress1;

	/**
	 * @column job_address2
	 */
	private $jobAddress2;

	/**
	 * @column job_address3
	 */
	private $jobAddress3;

	/**
	 * @column job_telephone_number
	 */
	private $jobTelephoneNumber;

	/**
	 * @column job_fax_number
	 */
	private $jobFaxNumber;

	private $memo;

	/**
	 * @column mail_error_count
	 */
	private $mailErrorCount = 0;

	/**
	 * @column not_send
	 */
	private $notSend = 0;

	/**
	 * SOY Mail用のエラーフラグ
	 * @column is_error
	 */
	private $isError = 0;

	/**
     * @column is_publish
     */
    private $isPublish = 1;

	/**
	 * 削除フラグ
	 * @column is_disabled
	 */
	private $isDisabled = 0;

	/**
	 * プロフィール公開フラグ
	 * @column is_profile_display
	 */
	private $isProfileDisplay = 0;

	/**
	 * @column register_date
	 */
	private $registerDate;

	/**
	 * @column update_date
	 */
	private $updateDate;
	/**
	 * @column real_register_date
	 */
	private $realRegisterDate;

	/**
	 * 登録ステータス
	 * @column user_type
	 */
	private $userType;

	/**
	 * @column address_list
	 */
	private $addressList;

	private $attributes;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getUserCode(){
		return $this->userCode;
	}
	function setUserCode($userCode){
		$this->userCode = $userCode;
	}
	function getAttribute1() {
		return $this->attribute1;
	}
	function setAttribute1($attribute1) {
		$this->attribute1 = $attribute1;
	}
	function getAttribute2() {
		return $this->attribute2;
	}
	function setAttribute2($attribute2) {
		$this->attribute2 = $attribute2;
	}
	function getAttribute3() {
		return $this->attribute3;
	}
	function setAttribute3($attribute3) {
		$this->attribute3 = $attribute3;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getHonorific(){
		return $this->honorific;
	}
	function setHonorific($honorific){
		$this->honorific = $honorific;
	}
	function getNickname(){
		return $this->nickname;
	}
	function setNickname($nickname){
		$this->nickname = $nickname;
	}
	function getReading() {
		return $this->reading;
	}
	function setReading($reading, $convert = true) {
		if($convert) $reading = mb_convert_kana($reading, "CKr", "UTF-8");
		$this->reading = $reading;
	}
	function getAccountId(){
		return $this->accountId;
	}
	function setAccountId($accountId){
		$this->accountId = $accountId;
	}
	function getProfileId(){
		return $this->profileId;
	}
	function setProfileId($profileId){
		$this->profileId = $profileId;
	}
	function getImagePath(){
		return $this->imagePath;
	}
	function setImagePath($imagePath){
		$this->imagePath = $imagePath;
	}
	function getGender() {
		return $this->gender;
	}
	function setGender($gender) {
		if(!isset($gender) || !strlen($gender)){
			$this->gender = null;
		}else if(is_numeric($gender)){
			$this->gender = (int)$gender;
		}else{
			if(is_numeric(stripos($gender, "M")) || is_numeric(strpos($gender, "男"))){
				$this->gender = self::USER_SEX_MALE;
			}else if(is_numeric(stripos($gender, "F")) || is_numeric(stripos($gender, "W")) || is_numeric(strpos($gender, "女"))){
				$this->gender = self::USER_SEX_FEMALE;
			}else{
				$this->gender = $gender;
			}
		}
	}

	/**
	 * $flagがtrueなら配列で返す
	 * 誕生日は Y-M-D の形式で保存する
	 */
	function getBirthday($flag = false) {
		if($flag && $this->birthday){
			if(strpos($this->birthday,"-") === false && strlen($this->birthday) <= 8){
				$r = strrev($this->birthday);
				$year = strrev(substr($r,4,4));
				$month = strrev(substr($r,2,2));
				$day = strrev(substr($r,0,2));
				$birthday = array($year,$month,$day);
			}else{
				$birthday = explode("-",$this->birthday);
			}
			return $birthday;
		}
		return $this->birthday;
	}
	function setBirthday($birthday) {
		if(!is_array($birthday)){
			if(is_string($birthday) && strpos($birthday,"-") !== false){
				$birthday = explode("-",$birthday);
				if(count($birthday) == 2){
					array_unshift($birthday,"");
				}
			}elseif(is_numeric($birthday) && $birthday > 0){
				$birthday = date("Y-m-d", $birthday);
				$birthday = explode("-",$birthday);
			}
		}

		if(empty($birthday[1]) || empty($birthday[2])){
			$this->birthday = null;
			return;
		}

		if(is_array($birthday)){
			$birthday = implode("-", $birthday);
		}

		$this->birthday = $birthday;
	}

	function getBirthdayYear() {
		$birthday = $this->getBirthday(true);
		return (is_array($birthday) && isset($birthday[0]) && $birthday[0] > 0) ? (int)$birthday[0] : "" ;
	}
	function getBirthdayMonth() {
		$birthday = $this->getBirthday(true);
		return (is_array($birthday) && isset($birthday[1]) && $birthday[1] > 0) ? (int)$birthday[1] : "" ;
	}
	function getBirthdayDay() {
		$birthday = $this->getBirthday(true);
		return (is_array($birthday) && isset($birthday[2]) && $birthday[2] > 0) ? (int)$birthday[2] : "" ;
	}

	function getZipCode() {
		return $this->zipCode;
	}
	function setZipCode($zipCode) {
		$this->zipCode = $zipCode;
	}
	function getArea() {
		return $this->area;
	}
	function setArea($area) {
		if(!is_numeric($area))$area = SOYShop_Area::getArea($area);
		$this->area = $area;
	}
	function getAddress1() {
		return $this->address1;
	}
	function setAddress1($address1) {
		$this->address1 = $address1;
	}
	function getAddress2() {
		return $this->address2;
	}
	function setAddress2($address2) {
		$this->address2 = $address2;
	}
	function getAddress3() {
		return $this->address3;
	}
	function setAddress3($address3) {
		$this->address3 = $address3;
	}
	function getTelephoneNumber() {
		return $this->telephoneNumber;
	}
	function setTelephoneNumber($telephoneNumber) {
		$this->telephoneNumber = $telephoneNumber;
	}
	function getFaxNumber() {
		return $this->faxNumber;
	}
	function setFaxNumber($faxNumber) {
		$this->faxNumber = $faxNumber;
	}
	function getCellphoneNumber() {
		return $this->cellphoneNumber;
	}
	function setCellphoneNumber($cellphoneNumber) {
		$this->cellphoneNumber = $cellphoneNumber;
	}
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}
	function getJobName() {
		return $this->jobName;
	}
	function setJobName($jobName) {
		$this->jobName = $jobName;
	}
	function getJobZipCode() {
		return $this->jobZipCode;
	}
	function setJobZipCode($jobZipCode) {
		$this->jobZipCode = $jobZipCode;
	}
	function getJobArea() {
		return $this->jobArea;
	}
	function setJobArea($jobArea) {
		if(!is_numeric($jobArea))$jobArea = SOYShop_Area::getArea($jobArea);
		$this->jobArea = $jobArea;
	}
	function getJobAddress1() {
		return $this->jobAddress1;
	}
	function setJobAddress1($jobAddress1) {
		$this->jobAddress1 = $jobAddress1;
	}
	function getJobAddress2() {
		return $this->jobAddress2;
	}
	function setJobAddress2($jobAddress2) {
		$this->jobAddress2 = $jobAddress2;
	}
	function getJobAddress3() {
		return $this->jobAddress3;
	}
	function setJobAddress3($jobAddress3) {
		$this->jobAddress3 = $jobAddress3;
	}
	function getJobTelephoneNumber() {
		return $this->jobTelephoneNumber;
	}
	function setJobTelephoneNumber($jobTelephoneNumber) {
		$this->jobTelephoneNumber = $jobTelephoneNumber;
	}
	function getJobFaxNumber() {
		return $this->jobFaxNumber;
	}
	function setJobFaxNumber($jobFaxNumber) {
		$this->jobFaxNumber = $jobFaxNumber;
	}
	function getMemo() {
		return $this->memo;
	}
	function setMemo($memo) {
		$this->memo = $memo;
	}
	function getMailErrorCount() {
		return $this->mailErrorCount;
	}
	function setMailErrorCount($mailErrorCount) {
		$this->mailErrorCount = $mailErrorCount;
	}
	function getNotSend(){
		return $this->notSend;
	}
	function setNotSend($notSend){
		$this->notSend = $notSend;
	}
	function getIsError(){
		return $this->isError;
	}
	function setIsError($isError){
		$this->isError = $isError;
	}
	function getIsPublish(){
		return $this->isPublish;
	}
	function setIsPublish($isPublish){
		$this->isPublish = $isPublish;
	}
	function getIsDisabled() {
		return $this->isDisabled;
	}
	function setIsDisabled($isDisabled) {
		$this->isDisabled = $isDisabled;
	}
	function getIsProfileDisplay(){
		return $this->isProfileDisplay;
	}
	function setIsProfileDisplay($isProfileDisplay){
		$this->isProfileDisplay = $isProfileDisplay;
	}

	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}

	function getRegisterDate() {
		if(is_null($this->registerDate))$this->registerDate = time();
		return $this->registerDate;
	}
	function setRegisterDate($registerDate) {
		$this->registerDate = $registerDate;
	}
	function getUpdateDate() {
		if(is_null($this->updateDate))$this->updateDate = time();
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}
	function getRealRegisterDate() {
		return $this->realRegisterDate;
	}
	function setRealRegisterDate($realRegisterDate) {
		$this->realRegisterDate = $realRegisterDate;
	}
	function getUserType() {
		return $this->userType;
	}
	function setUserType($userType) {
		$this->userType = $userType;
	}

	function getPassword() {
		return $this->password;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getSendAddress() {
		return $this->sendAddress;
	}
	function setSendAddress($sendAddress) {
		$this->sendAddress = $sendAddress;
	}

	function getAddressList() {
		return $this->addressList;
	}
	function setAddressList($addressList) {
		//array_mergeでキーを振り直す
		if(is_array($addressList))$addressList = serialize(array_merge($addressList));
		$this->addressList = $addressList;
	}

	function getAddressListArray(){
		$array = unserialize($this->addressList);
		if(is_array($array))return $array;

		return array();
	}

	function getAddress($index){

		if($index < 0){
			$res = array(
				"name" => $this->getName(),
				"reading" => $this->getReading(),
				"zipCode" => $this->getZipCode(),
				"area" => $this->getArea(),
				"address1" => $this->getAddress1(),
				"address2" => $this->getAddress2(),
				"address3" => $this->getAddress3(),
				"telephoneNumber" => $this->getTelephoneNumber(),
				"office" => $this->getJobName(),
			);

			return $res;
		}

		$list = $this->getAddressListArray();
		return (isset($list[$index])) ? $list[$index] : $this->getEmptyAddressArray();
	}

	/**
	 * アドレス帳の入力チェック
	 * 住所２（address2）と法人名（office）以外は必須
	 * @return -1 | 0 | 1
	 * -1: 何も入力されていない
	 * 0 : NG 入力漏れがある
	 * 1 : OK
	 *
	 */
	function checkValidAddress($address){
		//何かしら入力があるかどうか確認
		$isNotEmpty = (
			strlen(@$address["name"]) > 0 ||
			strlen(@$address["reading"]) > 0 ||
			strlen(@$address["zipCode"]) > 0 ||
			strlen(@$address["area"]) > 0 ||
			strlen(@$address["address1"]) > 0 ||
			/**strlen(@$address["address2"]) > 0 ||**/
			strlen(@$address["telephoneNumber"]) > 0
		);

		//何も入力されていないなら -1 を返す
		if(!$isNotEmpty)return -1;

		if($isNotEmpty && (
			strlen(@$address["name"]) == 0 ||
			strlen(@$address["reading"]) == 0 ||
			strlen(@$address["zipCode"]) == 0 ||
			strlen(@$address["area"]) == 0 ||
			strlen(@$address["address1"]) == 0 ||
			/**strlen(@$address["address2"]) == 0 ||**/
			strlen(@$address["telephoneNumber"]) == 0
		)){
			return 0;
		}

		return 1;
	}

	function getEmptyAddressArray(){
		return array(
			"name" => "",
			"reading" => "",
			"zipCode" => "",
			"area" => "",
			"address1" => "",
			"address2" => "",
			"address3" => "",
			"telephoneNumber" => "",
			"office" => ""
		);
	}

	function getAttributes() {
		return $this->attributes;
	}

	function getAttributesArray() {
		$array = unserialize($this->attributes);
		if(is_array($array))return $array;
		return array();
	}

	function setAttributes($attribute) {
		if(is_array($attribute))$attribute = serialize($attribute);
		$this->attributes = $attribute;
	}

	function getAttribute($key){
		$array = $this->getAttributesArray();
		return (array_key_exists($key,$array))?$array[$key]:null;
	}

	function setAttribute($key,$val){
		$array = $this->getAttributesArray();
		$array[$key] = $val;
		$this->setAttributes($array);
	}

	function clearAttribute($key){
		$array = $this->getAttributesArray();
		if(array_key_exists($key,$array))unset($array[$key]);
		$this->setAttributes($array);
	}

	/* 便利メソッド */

	function getGenderText(){
		if($this->getGender() === self::USER_SEX_MALE || $this->getGender() === self::USER_SEX_MALE){
			return "M";
		}elseif($this->getGender() === self::USER_SEX_FEMALE || $this->getGender() === self::USER_SEX_FEMALE){
			return "F";
		}else{
			return "";
		}
	}
	function getAreaText(){
		return SOYShop_Area::getAreaText($this->getArea());
	}
	function getJobAreaText(){
		return SOYShop_Area::getAreaText($this->getJobArea());
	}
	function getBirthdayText(){
		$birthday = $this->getBirthday(true);
		$birthday = $this->getBirthday(true);
		if(@$birthday[0]>0 && @$birthday[1]>0 && @$birthday[2]> 0){
			return "{$birthday[0]}-{$birthday[1]}-{$birthday[2]}";
		}elseif(@$birthday[1]>0 && @$birthday[2]> 0){
			return "{$birthday[1]}-{$birthday[2]}";
		}else{
			return "";
		}
	}

	/**
	 * @return string ユーザディレクトリのパス
	 */
	function getAttachmentsPath(){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/user/" . $this->getId() . "/";
		if(!file_exists($dir)){
			@mkdir($dir,0777,true);
		}

		return $dir;
	}

	/**
	 * @return string ユーザディレクトリのURL
	 */
	function getAttachmentsUrl(){
		return soyshop_get_site_path() . "files/user/" . $this->getId() . "/";
	}

	/**
	 * 添付ファイルを取得
	 */
	function getAttachments(){
		$dir = $this->getAttachmentsPath();
		$url = $this->getAttachmentsUrl();
		$files = scandir($dir);
		$res = array();

		foreach($files as $file){
			if($file[0] == ".")continue;
			$res[] = $url . $file;
		}

		return $res;
	}

	function getTmpPath(){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/tmp/";
		if(!file_exists($dir)){
			@mkdir($dir,0777,true);
		}

		return $dir;
	}
	function getTmpUrl(){
		return soyshop_get_site_path() . "files/tmp/";
	}

	function getDisplayName(){
		return (strlen($this->getNickname())) ? $this->getNickname() : $this->getName();
	}

	//一覧に表示させる時のメソッド
	function getListName(){
		$display = $this->getName();
		$checkDisplay = str_replace(" ","",$display);
		if(strlen($checkDisplay) == 0){
			$display = $this->getNickname();
		}

		return $display;
	}

	/**
	 * ポイント
	 * @return number
	 */
	function getPoint(){
		$dao = new SOY2DAO();
		$sql = "SELECT point FROM soyshop_point WHERE user_id = :userId;";
		try{
			$result = $dao->executeQuery($sql, array(":userId" => $this->getId()));
			$point = (isset($result[0])) ? (int)$result[0]["point"] : 0;
		}catch(Exception $e){
			$point = 0;
		}
		return $point;
	}

	/**
	 * ポイントの有効期限
	 * @return number
	 */
	function getPointTimeLimit(){
		SOYShopPlugin::load("soyshop.point");

		$delegate = SOYShopPlugin::invoke("soyshop.point", array(
				"userId" => $this->id,
		));
		return $delegate->getTimeLimit();
	}

	/**
	 * 有効なメールアドレスかどうかチェック
	 */
	function isValidEmail(){
		static $logic;
		if(!$logic) $logic = SOY2Logic::createInstance("logic.mail.MailLogic");
		return $logic->isValidEmail($this->mailAddress);
	}

	/**
	 * ダミーではないメールアドレスであるか？
	 */
	function isUsabledEmail(){
		static $isUse;
		if(is_null($isUse)){
			$isUse = false;
			if(strlen($this->mailAddress) && self::isValidEmail()){
				preg_match('/@' . DUMMY_MAIL_ADDRESS_DOMAIN . '$/', $this->mailAddress, $tmp);
				$isUse = (!isset($tmp[0]));
			}
		}
		return $isUse;
	}

	/**
     * 公開ユーザであるかどうか
     *
     * @return boolean
     */
    function isPublished(){
		if((int)$this->isDisabled === self::USER_IS_DISABLED || (int)$this->isPublish !== self::USER_IS_PUBLISH) return false;
		return true;
    }

	/* パスワードの暗号化関連 */

	/**
	 * パスワードが正しいかチェックする
	 *
	 * @param String 入力されたパスワード
	 * @param String 保存されているハッシュを含む文字列（algo/salt/hash）
	 */
	function checkPassword($input){
		$stored = $this->getPassword();
		$array = explode("/", $stored);
		if(count($array) == 3){
			list($algo, $salt, $hash) = $array;
			return ( $stored == self::_hashString($input, $salt, $algo) );

		//EC CUBEで使われている暗号化の仕組みでパスワードのチェックを行う
		}else{
			return ($stored == self::_hashStringEcCube($input));
		}
	}

	/**
	 * 新規にパスワードをハッシュ化する
	 *
	 * @param String ハッシュ化する文字列
	 * @return String ハッシュ化された文字列（algo/salt/hash）
	 *
	 */
	function hashPassword($rawPassword){
		//saltは乱数をmd5にしたもの
		$salt = md5(mt_rand());

		if(function_exists("hash")){
			// hash関数があればSHA512で
			return self::_hashString($rawPassword, $salt, "sha512");
		}else{
			// なければMD5
			return self::_hashString($rawPassword, $salt, "md5");
		}
	}

	/**
	 * 文字列をハッシュ化する。algo/salt/hashの形式で返す。
	 *
	 * @param String ハッシュ化する文字列
	 * @param String ハッシュ化の際のsalt
	 * @param String ハッシュ化アルゴリズム
	 * @return String ハッシュ化された文字列（algo/salt/hash）
	 */
	private static function _hashString($string, $salt, $algo){
		$algo = strtolower($algo);

		if($algo == "md5"){
			//md5はhashが使えないときための保険
			$hash = md5($salt.$string);
		}else{
			$hash = hash($algo, $salt.$string);
		}

		return "$algo/$salt/$hash";
	}

	private function _hashStringEcCube($input){
		if(!isset($input) || !is_string($input)) return "";	//想定していないタイミングで読まれることがある

		//ec cubeから移行した会員のパスワードをそのまま使用するためのチェック
//		if(strpos($input, ":") !== false){

			/** @Todo 暗号化の仕組みを調べる **/

		//ec cube 2.11より前のバージョン saltを利用していない　:での区切りがない
//		}else{
			if(file_exists(SOY2::rootDir() . "module/plugins/eccube_data_import/util/EccubeDataImportUtil.class.php")){
				SOY2::import("module.plugins.eccube_data_import.util.EccubeDataImportUtil");
				$authMagic = EccubeDataImportUtil::getAuthMagic();
			}else{
				$authMagic = "";
			}
			return sha1($input . ":" . $authMagic);
//		}
	}

	public static function getTableName(){
		return "soyshop_user";
	}
}
