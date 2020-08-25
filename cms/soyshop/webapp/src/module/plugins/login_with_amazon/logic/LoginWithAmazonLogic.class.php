<?php

class LoginWithAmazonLogic extends SOY2LogicBase {

	const FIELD_ID = "social_login_with_amazon";

	function __construct(){
		SOY2::import("module.plugins.login_with_amazon.util.LoginWithAmazonUtil");
	}

	function access($token){
		$c = curl_init('https://api.amazon.com/auth/o2/tokeninfo?access_token=' . urlencode($token));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

		$r = curl_exec($c);
		curl_close($c);
		$d = json_decode($r);

		$cnf = LoginWithAmazonUtil::getConfig();
		if ($d->aud != $cnf["client_id"]) {
			return null;
		}

		return $d;
	}

	function getProfile($token){
		// アクセストークンをユーザープロファイルと交換する
		$c = curl_init('https://api.amazon.com/user/profile');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $token));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

		$r = curl_exec($c);
		curl_close($c);
		return json_decode($r);
	}

	/** ログイン周り **/
	function register($amazonId, $name, $mailAddress){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $dao->getByMailAddress($mailAddress);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}

		//新規登録の場合
		if(is_null($user->getId())){
			$user->setName($name);
			$user->setMailAddress($mailAddress);
			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
			try{
				$id = $dao->insert($user);
				$user->setId($id);
			}catch(Exception $e){
				//
			}
		}

		//登録に成功した場合
		if(is_numeric($user->getId())){
			$attrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$attr = new SOYShop_UserAttribute();
			$attr->setUserId($user->getId());
			$attr->setFieldId(self::FIELD_ID);
			$attr->setValue($amazonId);

			try{
				$attrDao->insert($attr);
			}catch(Exception $e){
				return null;
			}
		}

		return $user->getId();
	}

	function getUserIdByAmazonId($amazonId){
		$attrDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$sql = "SELECT user_id FROM soyshop_user_attribute ".
				"WHERE user_field_id = '" . self::FIELD_ID . "' ".
				"AND user_value = :amazonId";

		try{
			$res = $attrDao->executeQuery($sql, array(":amazonId" => $amazonId));
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

	function getAmazonIdByUserId($userId){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO")->get($userId, self::FIELD_ID)->getValue();
		}catch(Exception $e){
			return null;
		}
	}
}
