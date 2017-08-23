<?php
SOY2::import("domain.admin.Administrator");
SOY2::import("util.PasswordUtil");

class AdministratorLogic extends Administrator implements SOY2LogicInterface{

	private $offset;
	private $limit;

	public function setLimit($limit){
		$this->limit = $limit;
	}

	public function setOffset($offset){
		$this->offset  = $offset;
	}

    public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}

	/**
	 * ログイン処理を行い（ユーザーIDとパスワードの照合判定のみ）、$thisにAdministratorの値が入る
	 * @return boolean ログイン成功したかどうか
	 */
	function login($userid,$password){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$bean = $dao->getByUserId($userid);
		}catch(Exception $e){
			return false;
		}

		SOY2::cast($this,$bean);

		$hash = $bean->getUserPassword();
		if($bean->getUserId() == $userid && PasswordUtil::checkPassword($password,$hash)){
			//2009-04-30 パスワードの自動更新は保留
			//$this->upgradeAdministratorPassword($bean, $password);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 管理者の一覧を取得し、そのオブジェクトの配列を返す。
	 * 使用するDAO:admin.AdministratorDAO
	 */
	function getAdministratorList(){

		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$siteRoleDAO = SOY2DAOFactory::create("admin.SiteRoleDAO");
		try{

	    	if(isset($this->limit))$dao->setLimit($this->limit);
	    	if(isset($this->offset))$dao->setOffset($this->offset);

			$entities = $dao->get();
			foreach($entities as $key => $admin){

				$sites = $siteRoleDAO->getByUserId($admin->getId());
				$siteList = array();
				foreach($sites as $site){
					$siteList[$site->getId()] = $site;
				}
				$entities[$key]->sites = $siteList;

			}
			return $entities;
		}catch(Exception $e){
			return array();
		}
	}


	/**
	 * 管理者の数を数える
	 * @return int
	 */
	function count(){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		return $dao->countUser();
	}


	/**
	 * 管理者のパスワードを変更します
	 * @return true:成功 false:失敗
	 * @param userid:対象となる管理者ID
	 * @param newPassword:新しいパスワード
	 */
	function updateAdministratorPassword($userid,$newPassword){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");

		try{
			$entity = $dao->getById($userid);
			$entity->setUserPassword(PasswordUtil::hashPassword($newPassword));
			$dao->update($entity);

		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * ユーザーIDとパスワードチェック
	 * @return boolean
	 * @param $id
	 * @param password
	 */
	 function checkUserIdAndPassword($id,$password){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$entity = $dao->getById($id);
			$hash = $entity->getUserPassword();
			if( PasswordUtil::checkPassword($password,$hash)){
				return true;
			}
		}catch(Exception $e){
		}

		return false;
	}

	/**
	 * 新しく管理者を追加します
	 * @return true:成功 false:失敗
	 * @param $id:追加するユーザID
	 * @param password:追加するユーザのパスワード
	 */
	function createAdministrator($id,$password, $isDefault = false, $name = "", $email = ""){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");

		$entity = new Administrator();
		$entity->setUserId($id);
		$entity->setUserPassword(PasswordUtil::hashPassword($password));
		$entity->setIsDefaultUser((int)$isDefault);
		$entity->setName($name);
		$entity->setEmail($email);

		try{
			$id = $dao->insert($entity);
			$this->setId($id);

			return true;

		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * ユーザーIDが有効かチェックします
	 * @return boolean
	 * @param userid:チェックするユーザーID
	 */
	function checkUserId($userid){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$dao->getByUserId($userid);
		}catch(Exception $e){
			return true;
		}
			return false;
	}

	/**
	 * 管理者を削除します
	 * @return boolean
	 * @param id:管理者のID
	 */
	function deleteAdministrator($id){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$siteRoleDAO = SOY2DAOFactory::create("admin.SiteRoleDAO");
		$appRoleDAO = SOY2DAOFactory::create("admin.AppRoleDAO");
		$dao->begin();
		try{
			$appRoleDAO->deleteByUserId($id);
			$siteRoleDAO->deleteByUserId($id);
			$dao->delete($id);
			$dao->commit();
			return true;
		}catch(Exception $e){
			$dao->rollback();
			return false;
		}
	}

	/**
	 * 初期管理者かどうかチェックする
	 * @return boolean
	 * @param id 管理者のID
	 */
	function checkDefaultUser($id){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$admin = $dao->getById($id);

			return (boolean)$admin->getIsDefaultUser();
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * 初期管理者が存在するかどうかチェックする
	 * @return Integer
	 */
	function hasDefaultUser(){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$count = $dao->countDefaultUser();
			return ($count>0);
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * IDからユーザ情報を取得
	 * @param id ユーザID
	 */
	function getById($id){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$admin = $dao->getById($id);
			return $admin;
		}catch(Exception $e){
			return null;
		}
	}

	/**
	 * cryptでハッシュされたパスワードをハッシュ化しなおす
	 * @param User
	 * @param String input 入力されたパスワード
	 */
	private function upgradeAdministratorPassword($user, $input){
		$stored = $user->getUserPassword();
		if(strpos($stored, "sha512/") === 0 OR strpos($stored, "md5/") === 0 ){
			// cryptじゃないので何もしない
		}else{
			// 入力値が8文字以下のときだけ行なう。
			// cryptは9文字目以降の文字を無視するので入力値が最初に設定したつもりのパスワードと同じである保証がないため。
			if(strlen($input) <= 8){
				$this->updateAdministratorPassword($user->getId(), $input);
			}
		}
	}

	/**
	 * 管理者の中に一人でもメールアドレスを登録している人がいたらtrueを返す
	 */
	function hasMailaddress() {
		$userArray = $this->getAdministratorList();

		foreach($userArray as $user) {
			if(strlen($user->getEmail())) return true;
		}

		return false;
	}

	/**
	 * パスワードリマインダー用のトークンを生成する
	 * @param id
	 * @return String 生成されたトークン
	 */
	function generateToken($id) {

		$user = $this->getById($id);
		if(is_null($user)){
			return null;
		}

		$user->setTokenIssuedDate($_SERVER["REQUEST_TIME"]);
		$user->setToken($this->getRandomStrings(40));

		$dao = $this->getDAO();

    try{
      $dao->update($user);
    }catch(Exception $e){
      error_log(var_export($e, true));
			return null;
    }

    return $user->getToken();
	}

	/**
	 * generate random string of specific length
	 * @param integer $length, length of string
	 * @return string
	 */
	private function getRandomStrings($length = 100){
		$str = "";

		while(strlen($str)<$length){
			if(function_exists("hash")){
				$str .= preg_replace("/[^[:alnum:]]/", "", base64_encode(hash("sha384", mt_rand(),true)));
			}else{
				$str .= preg_replace("/[^[:alnum:]]/", "", substr(base64_encode( md5(mt_rand(),true) ),0,21));
			}
		}
		$str = substr($str, 0, $length);

		if(strlen($str)==0) throw new Exception("Failed to generate random strings.");

		return $str;
	}

	private function getDAO() {
		return SOY2DAOFactory::create("admin.AdministratorDAO");
	}
}
?>
