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

		if(is_null($myPage) || $myPage === false) $myPage = new MyPageLogic($myPageId);


		/* auto login */
		if(!$myPage->getIsLoggedin() && @$_COOKIE["soyshop_mypage_" . SOYSHOP_ID . $myPageId . "_auto_login"]){
			$token = $_COOKIE["soyshop_mypage_" . SOYSHOP_ID . $myPageId . "_auto_login"];
			$autoLoginDAO = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");

			try{

				$autoLogin = $autoLoginDAO->getByToken($token);

				//time limit
				if($autoLogin->getLimit() > time()){
					$myPage->setAttribute("loggedin", true);
					$myPage->setAttribute("userId", $autoLogin->getUserId());
					$myPage->setAttribute("autoLoginId", $autoLogin->getId());

					/* change key */
					$token = md5(time() . $autoLogin->getUserId() . rand(0, 65535));

					$expire = $autoLogin->getLimit();

					//SOY CMS側でMyPageLogicを利用する場合に必要な時がある
					if(!function_exists("soyshop_get_site_url")) SOY2::import("base.func.common",".php");
					setcookie("soyshop_mypage_" . SOYSHOP_ID . $myPage->getId() . "_auto_login", $token, $expire, soyshop_get_site_url());
					$autoLogin->setToken($token);
					$autoLogin->save();

				}else{
					$autoLogin->delete();
					setcookie("soyshop_mypage_" . SOYSHOP_ID . $myPage->getId() . "_auto_login", null);
				}

			}catch(Exception $e){
				setcookie("soyshop_mypage_" . SOYSHOP_ID . $myPage->getId() . "_auto_login", null);
			}
		}

		return $myPage;
	}

	/**
	 * マイページを保存
	 */
	public static function saveMyPage(MyPageLogic $myPage){
		$userSession = SOY2ActionSession::getUserSession();
		$userSession->setAttribute("soyshop_mypage_" . SOYSHOP_ID . $myPage->getId(), soy2_serialize($myPage));
	}
	function save(){
		MypageLogic::saveMyPage($this);
	}

	/**
	 * マイページを削除
	 */
	public static function clearMyPage($myPageId){
		$userSession = SOY2ActionSession::getUserSession();
		$userSession->setAttribute("soyshop_mypage_" . SOYSHOP_ID . $myPageId, null);
	}
	function clear(){
		MyPageLogic::clearMyPage($this->getId());
		CartLogic::clearCart();
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
		}

		return $isLoggedIn;
	}

	/**
	 * タイトルフォーマット
	 * @param array args
	 * @return titleFormat
	 */
	function getTitleFormat($args){
		if(!isset($args[0])) return SOYShop_DataSets::get("config.mypage.title", "マイページ");
		if($args[0] === "profile"){
			if(isset($args[1]) && strlen($args[1]) > 0){
				$user = $this->getProfileUser($args[1]);
				if(strlen($user->getDisplayName()) > 0){
					$titleFormat = $user->getDisplayName() . "さんのプロフィール";
				}
			}

			if(!isset($titleFormat)) $titleFormat = "プロフィール";

		//ログインしていない時の表示
		}elseif($args[0] === "login" || $args[0] === "logout" || $args[0] === "remind" || $args[0] === "register"){

			$titleFormat = SOYShop_DataSets::get("config.mypage.title.no_logged_in", "マイページ");

		//マイページにお客様の名前を挿入する
		}else{
			$titleFormat = SOYShop_DataSets::get("config.mypage.title", "マイページ");
			if(strpos($titleFormat, "#") !== false){
				if(strpos($titleFormat, "#USERNAME#") !== false){
					$titleFormat = str_replace("#USERNAME#", $this->getUser()->getName(), $titleFormat);
				}elseif(strpos($titleFormat, "#NICKNAME#") !== false){
					$titleFormat = str_replace("#NICKNAME#", $this->getUser()->getDisplayName(), $titleFormat);
				}
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
	function getProfileUser($profileId){
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
		$autoLoginSessionId = $this->getAttribute("autoLoginId");
		if($autoLoginSessionId) $this->autoLogout($autoLoginSessionId);

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
			SOYShopPlugin::invoke("soyshop.mypage.login", array(
				"mode" => "login"
			));
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
			return true;
		}
	}

	function noPasswordLogin($userId){
		/**
		 * @ログイン周りのチェック
		 */

		//セッションに追加
		$this->setAttribute("loggedin", true);
		$this->setAttribute("userId", $userId);

		$this->save();
		return true;
	}

	/* auto login */

	/**
	 * auto login
	 * @param int defult
	 * @param str defult documentRoot
	 */
	function autoLogin($expire = SOYSHOP_AUTOLOGIN_EXPIRE, $url = null){
		$token = md5(time() . $this->getUserId() . mt_rand(0, 65535));
		$expire += time();

		if(is_null($url)) $url = soyshop_get_site_url(true);

		if(strpos($url, "http://") === 0 || strpos($url, "https://") === 0){
			preg_match("/^https?:\\/\\/([^:\\/]+)(?::[0-9]+)?(?:(\\/[^\\?#]*))?/", $url, $matches);
			$domain = isset($matches[1]) ? $matches[1] : "" ;
			$path   = isset($matches[2]) ? $matches[2] : "/" ;
			$secure = (strpos($url, "https://") === 0);
		}else{
			$path = $url;
		}

		if($url == ""){
			$path = "/";
		}elseif($path[strlen($path)-1] !== "/"){
			$path .= "/";
		}

		//Cookie
		if(isset($domain)){
			setcookie("soyshop_mypage_" . SOYSHOP_ID . $this->getId() . "_auto_login", $token, $expire, $path, $domain, $secure);
		}else{
			setcookie("soyshop_mypage_" . SOYSHOP_ID . $this->getId() . "_auto_login", $token, $expire, $path);
		}

		SOY2::import("domain.user.SOYShop_AutoLoginSession");
		$login = new SOYShop_AutoLoginSession();
		$login->setUserId($this->getUserId());
		$login->setToken($token);
		$login->setLimit($expire);

		$login->save();

		$this->setAttribute("autoLoginId", $login->getId());
	}

	function autoLogout($autoLoginSessionId){

		try{
			$dao = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");
			$session = $dao->getById($autoLoginSessionId);
			$session->delete();
		}catch(Exception $e){
			//
		}
	}

	/**
	 * 自動ログインIDが存在していた場合はtrueを返す
	 * @return boolean
	 */
	function getIsAutoLogin(){
		$res = $this->getAttribute("autoLoginId");
		return (!is_null($res));	//値が存在している場合はtrueを返す
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

		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$tokenDAO = SOY2DAOFactory::create("user.SOYShop_UserTokenDAO");

		//翌日の00:00まで
		$limit = time();
		$year = date("Y", $limit);
		$month = date("m", $limit);
		$day = date("d", $limit);

		$limit = mktime(0, 0, 0, $month, $day, $year);
		$limit = $limit + 60 * 60 * 24 -1;

		try{
			$user = $userDAO->getByMailAddress($mail);
			$query = $this->createQuery($user->getMailAddress());

		}catch(Exception $e){
			$user = new SOYShop_User();
			$query = "";

		}

		try{
			$token = $tokenDAO->getByUserId($user->getId());
			$token->setToken($query);
			$token->setLimit($limit);
			$token->save();

		}catch(Exception $e){
			$token = new SOYShop_UserToken();
			$token->setUserId($user->getId());
			$token->setToken($query);
			$token->setLimit($limit);

			$token->save();
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
