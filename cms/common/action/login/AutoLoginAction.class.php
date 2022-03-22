<?php

class AutoLoginAction extends SOY2Action{

	const LIMIT_DATE = 30;	//自動ログインの期限は30日間

    function execute($req,$form,$res){
		//リセット
		self::_reset();

    	if(defined("SOYCMS_ASP_MODE")){
			return SOY2Action::FAILED;
    		//return self::aspLogin($auth);	ASPモードがないのでAutoLoginは行わない
    	}else{
			return self::normalLogin();
    	}
    }

	private function _reset(){
		try{
			self::dao()->deleteByTime(time());	//今よりも前のオブジェクトはすべて削除
		}catch(Exception $e){
			//
		}
	}

    /**
     * 通常のログインを行う
     */
    private function normalLogin(){
		if(!isset($_COOKIE["soycms_auto_login"])) return SOY2Action::FAILED;

		try{
			$login = self::dao()->getByToken($_COOKIE["soycms_auto_login"]);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
    	$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");

    	if($logic->autoLogin($login->getUserId())){
    		//ログイン状態をセッションに保存：$logicはloginに成功したらAdministratorが入っている
    		UserInfoUtil::login($logic);

			//期限の更新
			$login->setLimit(SOYCMS_AUTOLOGIN_EXPIRE * 24 * 60 * 60 + time());
			try{
				self::dao()->update($login);
			}catch(Exception $e){
				//
			}

    		return SOY2Action::SUCCESS;
    	}

		return SOY2Action::FAILED;
    }

    /**
     * ASP版のログインを行う
     */
    // private function aspLogin($auth){
	//
    // 	$name = $auth['name'];
    // 	$pass = $auth['password'];
	//
    // 	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
	//
    // 	try{
    // 		$user = $dao->login($name,crypt($pass,$name));
	//
    // 		//ログイン状態をセッションに保存
    // 		UserInfoUtil::login($user);
	//
	// 		$user->setLastLoginDate(time());
	// 		$dao->update($user);
	//
    // 	}catch(Exception $e){
	//
    // 		$this->setErrorMessage('loginfailed','ログインに失敗しました。');
	//     	$this->setAttribute('username',$auth['name']);
	//     	return SOY2Action::FAILED;
    // 	}
	//
    // 	return SOY2Action::SUCCESS;
    // }

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("admin.AutoLoginDAO");
		return $dao;
	}
}
