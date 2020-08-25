<?php

class LINELoginLogic extends SOY2LogicBase {

	const FIELD_ID = "social_login_line_login";

	function __construct(){
		SOY2::import("module.plugins.line_login.util.LINELoginUtil");
	}

	function getChannelId(){
		static $id;
		if(is_null($id)) {
			$config = LINELoginUtil::getConfig();
			$id = (isset($config["channel_id"])) ? htmlspecialchars($config["channel_id"], ENT_QUOTES, "UTF-8") : null;
		}
		return $id;
	}

	function getChannelSecret(){
		static $secret;
		if(is_null($secret)) {
			$config = LINELoginUtil::getConfig();
			$secret = (isset($config["channel_secret"])) ? htmlspecialchars($config["channel_secret"], ENT_QUOTES, "UTF-8") : null;
		}
		return $secret;
	}

	function createAuthorizeLink(){
		$channelId = self::getChannelId();
		return "https://access.line.me/oauth2/v2.1/authorize".
				"?response_type=code".
				"&client_id=". $channelId.
				"&redirect_uri=". rawurlencode(self::createRedirectUrl()).
				"&state=". self::makeRandStrWithAccessToken($channelId).
				"&scope=profile";
	}

	private function createRedirectUrl(){
		return soyshop_get_mypage_url(true) . "?soyshop_download=line_login&line_login";
	}

	private function makeRandStrWithAccessToken($channelId){
		$r = self::makeRandStr(10);
		SOY2ActionSession::getUserSession()->setAttribute("line_login." . $channelId, $r);
		return $r;
	}

	function checkLoggedIn($state){
		$r = SOY2ActionSession::getUserSession()->getAttribute("line_login." . self::getChannelId());
		return ($r == $state);
	}

	function getTokenByCode($code){
		$params = array(
			"grant_type" => "authorization_code",
			"code" => $code,
			"redirect_uri" => self::createRedirectUrl(),
			"client_id" => self::getChannelId(),
			"client_secret" => self::getChannelSecret()
		);

		$conn = curl_init("https://api.line.me/oauth2/v2.1/token");
		//curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($conn, CURLOPT_POST, TRUE);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($params));
		$result = curl_exec($conn);
		curl_close($conn);

		//確実に取得するため、配列にしてから値を取得
		$values = self::convertArrayFromCUrlResult($result);

		return (isset($values["access_token"])) ? $values["access_token"] : null;
	}

	function getLINEProfileByToken($token){
		$conn = curl_init("https://api.line.me/v2/profile");
		curl_setopt($conn, CURLOPT_HTTPHEADER, array("Authorization: Bearer ". $token));
		//curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		//curl_setopt($conn, CURLOPT_POST, TRUE);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		//curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($params));
		$result = curl_exec($conn);
		curl_close($conn);

		//確実に取得するため、配列にしてから値を取得
		return self::convertArrayFromCUrlResult($result);
	}

	private function convertArrayFromCUrlResult($result){
		$array = explode(",", str_replace(array("{", "}"), "", $result));
		$values = array();
		foreach($array as $v){
			$s = explode(":", $v);
			$values[trim($s[0], "\"")] = trim($s[1], "\"");
		}
		return $values;
	}

	private function makeRandStr($length=10) {
		$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
		$r_str = null;
		for ($i = 0; $i < $length; $i++) {
			$r_str .= $str[rand(0, count($str) - 1)];
		}
		return $r_str;
	}

	/** ログイン周り **/
	function registerMemberOnSOYShop($values){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$user = new SOYShop_User();
		$user->setName($values["displayName"]);
		$user->setMailAddress(self::createLineLoginDummyMailAddress($values["userId"], $values["displayName"]));
		$user->setUserType(SOYShop_User::USERTYPE_REGISTER);

		try{
			$userId = $userDao->insert($user);
		}catch(Exception $e){
			return null;
		}

		$userAttrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$attr = new SOYShop_UserAttribute();
		$attr->setUserId($userId);
		$attr->setFieldId(self::FIELD_ID);
		$attr->setValue($values["userId"]);

		try{
			$userAttrDao->insert($attr);
		}catch(Exception $e){
			return null;
		}

		return $userId;
	}

	private function createLineLoginDummyMailAddress($userId, $name){
		$r = md5($userId + $name);
		//10文字で切る
		$r = substr($r, 0, 10);
		return "line_" . $r . time() . "@" . DUMMY_MAIL_ADDRESS_DOMAIN;
	}

	function getUserIdByLineId($lineId){
		$userAttrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$sql = "SELECT user_id FROM soyshop_user_attribute ".
				"WHERE user_field_id = '" . self::FIELD_ID . "' ".
				"AND user_value = :lineId";

		try{
			$res = $userAttrDao->executeQuery($sql, array(":lineId" => $lineId));
		}catch(Exception $e){
			return null;
		}

		if(!isset($res[0]["user_id"])) return null;

		//取得したユーザが削除されている場合はアマゾンIDを削除してnullを返す
		$userId = (int)$res[0]["user_id"];
		$user = soyshop_get_user_object($userId);
		if($user->getIsDisabled() != SOYShop_User::USER_IS_DISABLED) return $userId;

		//削除
		try{
			$attrDao->delete($userId, self::FIELD_ID);
		}catch(Exception $e){
			//
		}

		return null;
	}

	function getLINEIdByUserId($userId){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO")->get($userId, self::FIELD_ID)->getValue();
		}catch(Exception $e){
			return null;
		}
	}
}
