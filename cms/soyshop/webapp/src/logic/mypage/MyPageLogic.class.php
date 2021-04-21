<?php
SOY2::import("domain.order.SOYShop_ItemOrder");
SOY2::import("domain.order.SOYShop_ItemModule");
SOY2::import("domain.user.SOYShop_User");
SOY2::import("domain.user.SOYShop_UserToken");

/**
 * マイページ全般
 *
 * セッションを使ってユーザ情報を保存
 */
class MyPageLogic extends SOY2LogicBase{

	const REGISTER_REDIRECT_KEY = "register_redirect";

	/**
	 * マイページを取得
	 * @param integer $myPageId
	 * @return MyPageLogic
	 */
	public static function getMyPage($myPageId = null){

		if(!$myPageId)$myPageId = SOYSHOP_CURRENT_MYPAGE_ID;
		$userSession = SOY2ActionSession::getUserSession();
		$myPage = $userSession->getAttribute("soyshop_mypage_" . SOYSHOP_ID . $myPageId);
		//セッションから直接オブジェクトの形で取得できた場合は、strlen($myPage)ではじくことができる
		while(is_string($myPage) && strlen($myPage) > 0){//原因不明だが二重にserializeされていることがあるのでifではなくwhileにしておく
			$myPage = soy2_unserialize($myPage);
		}

		if(!$myPage instanceof MyPageLogic) $myPage = new MyPageLogic($myPageId);

		/* auto login */
		if(!$myPage->getIsLoggedin() && isset($_COOKIE["soyshop_mypage_" . SOYSHOP_ID . $myPageId . "_auto_login"])){
			$cookieKey = "soyshop_mypage_" . SOYSHOP_ID . $myPageId . "_auto_login";
			$token = $_COOKIE[$cookieKey];
			soy2_setcookie($cookieKey);	//同じIDでcookieを作成してしまう問題を解決する
			unset($_COOKIE[$cookieKey]);

			$autoLoginDao = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");
			try{
				$autoLogin = $autoLoginDao->getByToken($token);
			}catch(Exception $e){
				$autoLogin = new SOYShop_AutoLoginSession();
			}

			//事前に同じUserIdのデータを削除
			try{
				$autoLoginDao->deleteByUserId($autoLogin->getUserId());
				$autoLoginDao->deleteOldObjects();	//古いトークンを削除
			}catch(Exception $e){
				//
			}

			//time limit
			if(is_numeric($autoLogin->getUserId()) && $autoLogin->getLimit() > time()){
				/* change key */
				$token = md5(time() . $autoLogin->getUserId() . rand(0, 65535));
				$myPage->setAttribute("loggedin", true);
				$myPage->setAttribute("userId", $autoLogin->getUserId());
				$myPage->setAttribute("autoLoginToken", $token);

				//SOY CMS側でMyPageLogicを利用する場合に必要な時がある
				if(!function_exists("soyshop_get_site_url")) SOY2::import("base.func.common",".php");

				$autoLogin->setToken($token);
				try{
					$autoLoginDao->insert($autoLogin);
					soy2_setcookie($cookieKey, $token, array("path" => soyshop_get_site_url(), "expires" => $autoLogin->getLimit()));
				}catch(Exception $e){
					//
				}
			}
		}

		return $myPage;
	}

	/**
	 * マイページを保存
	 */
	public static function saveMyPage(MyPageLogic $myPage){
		SOY2ActionSession::getUserSession()->setAttribute("soyshop_mypage_" . SOYSHOP_ID . $myPage->getId(), soy2_serialize($myPage));
	}
	function save(){
		MypageLogic::saveMyPage($this);
	}

	/**
	 * マイページを削除
	 */
	public static function clearMyPage($myPageId){
		SOY2ActionSession::getUserSession()->setAttribute("soyshop_mypage_" . SOYSHOP_ID . $myPageId, null);
	}
	function clear(){
		MyPageLogic::clearMyPage($this->getId());
		CartLogic::clearCart();
	}

	/** cookie **/
	private static function _setCookie(){

	}

	/**
	 * config:tmp_user_register
	 * @return Bool true:tmp_user on
	 */
	static function getTmpUserMode(){
		return SOYShop_DataSets::get("config.mypage.tmp_user_register", 1);
	}

	/**
	 * 取得したパスの書き換え
	 */
	public static function convertPath($path){
		$array = explode(".", $path);
		$last = count($array) - 1;
		if(isset($array[$last])) $array[$last] = ucfirst($array[$last]);
		return implode(".", $array);
	}

	/**
	 * construct
	 */
	function __construct($myPageId){
		$this->id = $myPageId;
	}

	protected $id;
	protected $modules = array();
	protected $attributes = array();

	/**
	 * 登録情報：編集中のセッション用
	 */
	protected $userInfo;
	protected $errorMessage = array();

	/**
	 * モジュール追加
	 */
	function addModule(SOYShop_ItemModule $module){
		$id = $module->getId();

		//同一タイプは削除する
		if(strlen($module->getType()) > 0){
			foreach($this->modules as $key => $value){
				if($value->getType() == $module->getType()){
					unset($this->modules[$key]);
				}
			}
		}

		$this->modules[$id] = $module;
	}

	/**
	 * モジュール削除
	 */
	function removeModule($moduleId){
		if(isset($this->modules[$moduleId])){
			$this->modules[$moduleId] = null;
			unset($this->modules[$moduleId]);
		}

		//子モジュールを削除
		foreach($this->modules as $id => $module){
			if(preg_match("/^$moduleId\..+/", $id)){
				unset($this->modules[$id]);
			}
		}

		//関連する設定値をクリア
		$this->clearOrderAttribute($moduleId);
	}

	/**
	 * モジュール取得
	 */
	function getModule($moduleId){
		return (isset($this->modules[$moduleId])) ? $this->modules[$moduleId] : null;
	}

	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	function setAttribute($id, $value){
		$this->attributes[$id] = $value;
		$this->save();
	}
	function getAttribute($id){
		return (isset($this->attributes[$id])) ? $this->attributes[$id] : null;
	}
	function clearAttribute($id){
		$this->attributes[$id] = null;
		unset($this->attributes[$id]);
		$this->save();
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}

	/**
	 * エラーメッセージ
	 */
	function addErrorMessage($id, $str){
		$this->errorMessage[$id] = $str;
	}

	/**
	 * エラーメッセージのクリア
	 */
	function removeErrorMessage($id){
		if(isset($this->errorMessage[$id])){
			unset($this->errorMessage[$id]);
		}
	}

	/**
	 * 取得
	 */
	function getErrorMessage($id){
		return (isset($this->errorMessage[$id])) ? $this->errorMessage[$id] : null;
	}

	/**
	 * チェック
	 * @return boolean
	 */
	function hasError($id = null){
		if(isset($id) && strlen($id) >0){
			return isset($this->errorMessage[$id]) && (count($this->errorMessage[$id]) > 0);
		}else{
			return (count($this->errorMessage) > 0);
		}
	}

	/**
	 * 全て
	 */
	function getErrorMessages(){
		return $this->errorMessage;
	}

	/**
	 *
	 */
	function clearErrorMessage(){
		$this->errorMessage = array();
	}

	function getPagePath($args){
		return "";
	}

	function getIsLoggedin(){
		static $isLoggedIn, $try;
		if(is_null($try)) $try = 0;
		if(is_null($isLoggedIn)){
			//最低3回確認する
			if(++$try > 2) {
				$isLoggedIn = false;
				return $isLoggedIn;
			}

			//一度もログイン関係の動作をしていない時は調べる前にfalseを返す→様々なマイページの処理を止めることができる
			//ログインするとuser_idとloggedinの値を持つので、配列の値が2未満であればログインのフローは通過していないことになる
			$attrs = $this->getAttributes();
			if(!is_array($attrs) || count($attrs) < 2) return false;

			//拡張機能を介してログインしているか？
			SOYShopPlugin::load("soyshop.mypage.login");
			$extendIsLoggedIn = SOYShopPlugin::invoke("soyshop.mypage.login", array(
				"mode" => "isLoggedIn"
			))->getResult();

			if(is_bool($extendIsLoggedIn)){
				$isLoggedIn = $extendIsLoggedIn;
			}else{
				$res = $this->getAttribute("loggedin");
				$isLoggedIn = (is_bool($res) && $res);
			}

			//ログインしているユーザが削除されている場合はログアウトにする
			if($isLoggedIn && !$this->getUser()->isPublished()){
				$this->logout();
				$isLoggedIn = false;
			}
		}

		return $isLoggedIn;
	}

	/**
	 * タイトルフォーマット
	 * @param array args
	 * @return titleFormat
	 */
	function getTitleFormat($args){
		SOYShopPlugin::load("soyshop.mypage");
		$titleFormat = SOYShopPlugin::invoke("soyshop.mypage", array(
			"mode" => "title"
		))->getTitleFormat();

		if(is_null($titleFormat)){
			if(isset($args[0])){
				switch($args[0]){
					case "profile":
						if(isset($args[1]) && strlen($args[1]) > 0){
							$user = $this->getProfileUser($args[1]);
							if(strlen($user->getDisplayName()) > 0) $titleFormat = $user->getDisplayName() . "さんのプロフィール";
						}
						if(is_null($titleFormat)) $titleFormat = "プロフィール";
						break;
					//ログインしていない時の表示
					case "login":
					case "logout":
					case "remind":
					case "register":
						$titleFormat = SOYShop_DataSets::get("config.mypage.title.no_logged_in", "マイページ");
						break;
					//マイページにお客様の名前を挿入する
					default:
						$titleFormat = SOYShop_DataSets::get("config.mypage.title", "マイページ");
						if(strpos($titleFormat, "#") !== false){
							if(strpos($titleFormat, "#USERNAME#") !== false){
								$titleFormat = str_replace("#USERNAME#", $this->getUser()->getName(), $titleFormat);
							}elseif(strpos($titleFormat, "#NICKNAME#") !== false){
								$titleFormat = str_replace("#NICKNAME#", $this->getUser()->getDisplayName(), $titleFormat);
							}
						}
						break;
				}
			}else{
				$titleFormat = SOYShop_DataSets::get("config.mypage.title", "マイページ");
			}
		}

		return $titleFormat;
	}

	function getUserId(){
		static $userId;
		if($this->getIsLoggedin() && is_null($userId)){
			SOYShopPlugin::load("soyshop.mypage.login");
			$userId = SOYShopPlugin::invoke("soyshop.mypage.login", array(
				"mode" => "user_id"
			))->getUserId();

			if(!is_numeric($userId)){
				$userId = $this->getAttribute("userId");
			}

			if(is_null($userId)) $userId = 0;
		}

		return $userId;
	}

	/**
	 * 登録情報：編集中のセッション用
	 */
	function getUserInfo(){
		return $this->userInfo;
	}
	function setUserInfo($userInfo){
		$this->userInfo = $userInfo;
	}
	function clearUserInfo(){
		$this->userInfo = null;
	}

	/**
	 * @return Object SOYShop_User
	 */
	function getUser(){
		return soyshop_get_user_object($this->getUserId());
	}

	//ダミーの値を表示したりといろいろできる
	function getUserName(){
		$userName = $this->getAttribute("userName");
		if(is_null($userName)){
			$user = $this->getUser();
			$userName = $user->getDisplayName();
		}

		return $userName;
	}

	/**
	 * プロフィール表示用のユーザを取得する
	 * @param string profileId
	 * @return object SOYShop_User
	 */
	private function getProfileUser($profileId){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByProfileId($profileId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}

	/**
	 * プロフィールページへ遷移させるリンクを取得する
	 * @param int userId
	 * @return string url
	 */
	function getProfileUserLink($userId){
		$user = soyshop_get_user_object($userId);
		if(is_null($user->getId())) return null;

		if($user->getIsProfileDisplay() != SOYShop_User::PROFILE_IS_DISPLAY) return null;
		if(is_null($user->getProfileId())) return null;

		return soyshop_get_mypage_url() . "/profile/" . $user->getProfileId();
	}

	/**
	 * ログアウト
	 */
	function logout(){
		SOYShopPlugin::load("soyshop.mypage.login");
		SOYShopPlugin::invoke("soyshop.mypage.login", array(
			"mode" => "loguot"
		));

		/* auto login */
		if(!is_null($this->getAttribute("autoLoginToken"))) $this->autoLogout();
		soy2_setcookie("soyshop_mypage_" . SOYSHOP_ID . $this->getId() . "_auto_login");

		$this->clear();
	}

	/**
	 * ログイン(メールアドレスかログインIDのどちらかでログインを試みる)
	 * @param string loginId
	 * @param string password
	 * #error login_error
	 */
	function login($loginId, $password){
		//プラグインのログイン周りの拡張ポイントを持つプラグインがあるか？
		SOYShopPlugin::load("soyshop.mypage.login");
		$isExtendLogin = SOYShopPlugin::invoke("soyshop.mypage.login")->getResult();

		if(isset($isExtendLogin) && is_bool($isExtendLogin) && $isExtendLogin){	//ログインの拡張
			$res = SOYShopPlugin::invoke("soyshop.mypage.login", array(
				"mode" => "login"
			))->getResult();
		}else{	//通常ログイン
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
			$hasRegister = true;

			SOY2::import("domain.config.SOYShop_ShopConfig");
			$config = SOYShop_ShopConfig::load();

			//ログインIDでログインを試みる
			if($config->getAllowLoginIdLogin() && !soyshop_valid_email($loginId)){
				try{
					$user = $userDAO->getByAccountId($loginId);
				}catch(Exception $e){
					$user = new SOYShop_User();
				}
			//メールアドレスでログインにを試みる
			}elseif($config->getAllowMailAddressLogin()){
				try{
					$user = $userDAO->getByMailAddress($loginId);
				}catch(Exception $e){
					$user = new SOYShop_User();
				}
			//ログインを許可していない
			}else{
				$user = new SOYShop_User();
			}

			//仮登録状態もしくは退会しているか調べる。パスワードが正しいかも調べる
			if(
				is_null($user->getUserType()) ||
				$user->getUserType() != SOYShop_User::USERTYPE_REGISTER ||
				$user->getIsDisabled() == SOYShop_User::USER_IS_DISABLED ||
				!$user->checkPassword($password)
			){
				$hasRegister = false;
			}

			//登録されていないログインIDのエラー通知
			if(!$hasRegister){
				$this->addErrorMessage("login_error", MessageManager::get("LOGIN_NOT_REGISTER"));
				$this->save();
				return false;
			}

			//セッションに追加
			$this->setAttribute("userId", $user->getId());
			$this->setAttribute("loggedin", true);
			$this->save();

			$res = true;
		}

		if($res) self::_logMypageLogin();

		return $res;
	}

	function noPasswordLogin($userId){
		/**
		 * @ログイン周りのチェック
		 */

		//セッションに追加
		$this->setAttribute("loggedin", true);
		$this->setAttribute("userId", $userId);
		$this->save();

		self::_logMypageLogin();

		return true;
	}

	/* auto login */

	/**
	 * auto login
	 * @param int defult
	 * @param str defult documentRoot
	 */
	function autoLogin($expire = SOYSHOP_AUTOLOGIN_EXPIRE, $url = null){
		$userId = $this->getAttribute("userId");	//このコードを読む時に$this->getUserId()が使えない事がある
		$token = md5(time() . $userId . mt_rand(0, 65535));
		$expire += time();

		if(is_null($url)) $url = soyshop_get_site_url(true);

		if(strpos($url, "http://") === 0 || strpos($url, "https://") === 0){
			preg_match("/^https?:\\/\\/([^:\\/]+)(?::[0-9]+)?(?:(\\/[^\\?#]*))?/", $url, $matches);
			$domain = isset($matches[1]) ? $matches[1] : "" ;
			$path   = isset($matches[2]) ? $matches[2] : "/" ;
		}else{
			$path = $url;
		}

		if($url == ""){
			$path = "/";
		}elseif($path[strlen($path)-1] !== "/"){
			$path .= "/";
		}

		//if(isset($domain)) $opts["domain"] = $domain;

		//Cookie
		soy2_setcookie("soyshop_mypage_" . SOYSHOP_ID . $this->getId() . "_auto_login", $token, array("expires" => $expire, "path" => $path));

		$autoLoginDao = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");
		try{
			$autoLoginDao->deleteByUserId($userId);
			$autoLoginDao->deleteOldObjects();
		}catch(Exception $e){
			//
		}
		$autoLogin = new SOYShop_AutoLoginSession();
		$autoLogin->setUserId($userId);
		$autoLogin->setToken($token);
		$autoLogin->setLimit($expire);
		try{
			$autoLoginDao->insert($autoLogin);
			$this->setAttribute("autoLoginToken", $autoLogin->getToken());
		}catch(Exception $e){
			//
		}
	}

	function autoLogout(){
		try{
			SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO")->deleteByUserId($this->getUserId());
		}catch(Exception $e){
			//
		}
		soy2_setcookie("soyshop_mypage_" . SOYSHOP_ID . $this->getId() . "_auto_login");
	}

	private function _logMypageLogin(){
		$dao = SOY2DAOFactory::create("logging.SOYShop_MypageLoginLogDAO");
		$obj = new SOYShop_MypageLoginLog();
		$obj->setUserId($this->getAttribute("userId"));
		try{
			$dao->insert($obj);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * 自動ログインIDが存在していた場合はtrueを返す
	 * @return boolean
	 */
	function getIsAutoLogin(){
		return (!is_null($this->getAttribute("autoLoginToken")));	//値が存在している場合はtrueを返す
	}

	/**
	 * token発行
	 */
	function createQuery($mail = null){
   		$crypt = md5(time() . "_" . $mail);
   		$query = (base64_encode(substr($crypt, strlen($crypt) - 15)));
   		return $query;
	}

	/**
	 * @param String mailaddress
	 * @return String token
	 * @return String time_limit
	 */
	function createToken($mail){

		$tokenDAO = SOY2DAOFactory::create("user.SOYShop_UserTokenDAO");

		//翌日の00:00まで
		$limit = time();
		$year = date("Y", $limit);
		$month = date("m", $limit);
		$day = date("d", $limit);

		$limit = mktime(0, 0, 0, $month, $day, $year);
		$limit = $limit + 60 * 60 * 24 -1;

		try{
			$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mail);
			$query = $this->createQuery($user->getMailAddress());

		}catch(Exception $e){
			$user = new SOYShop_User();
			$query = "";

		}

		try{
			$token = $tokenDAO->getByUserId($user->getId());
			$token->setToken($query);
			$token->setLimit($limit);
			$tokenDAO->update($token);

		}catch(Exception $e){
			$token = new SOYShop_UserToken();
			$token->setUserId($user->getId());
			$token->setToken($query);
			$token->setLimit($limit);

			$tokenDAO->insert($token);
		}

		return array($query, $limit);
	}

	/**
	 * パスワードリマインダー機能バッチ　メール文面の追加
	 */
	function updateBatch(){
		$mail = array(
			"title" => "[#SHOP_NAME#]パスワード再設定",
				"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/header.txt"),
				"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/footer.txt")
		);

		SOYShop_DataSets::put("mail.mypage.remind.title", $mail["title"]);
		SOYShop_DataSets::put("mail.mypage.remind.header", $mail["header"]);
		SOYShop_DataSets::put("mail.mypage.remind.footer", $mail["footer"]);
	}
}
