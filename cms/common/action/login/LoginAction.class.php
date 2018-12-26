<?php
/**
 * 通常ログインとASPログインを振り分けて処理
 */
class LoginAction extends SOY2Action{

    function execute($req,$form,$res){
    	$auth = $req->getParameter('Auth');
    	$redirect = $req->getParameter('r');

    	if(defined("SOYCMS_ASP_MODE")){
    		return self::aspLogin($auth);
    	}else{
    		return self::normalLogin($auth, $redirect);
    	}
    }

    /**
     * 通常のログインを行う
     */
    private function normalLogin($auth, $redirect){
    	$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");

    	if($logic->login($auth['name'],$auth['password'])){

    		//ログイン状態をセッションに保存：$logicはloginに成功したらAdministratorが入っている
    		UserInfoUtil::login($logic);

			/**
			 * 転送先が指定されている場合はそこへ遷移する
			 */
			if(strlen($redirect) >0 && CMSAdminPageController::isAllowedPath($redirect)){
				SOY2PageController::redirect($redirect);
			}

    		return SOY2Action::SUCCESS;
    	}

    	$this->setErrorMessage('loginfailed','ログインに失敗しました。');
    	$this->setAttribute('username',$auth['name']);
    	return SOY2Action::FAILED;
    }

    /**
     * ASP版のログインを行う
     */
    private function aspLogin($auth){

    	$name = $auth['name'];
    	$pass = $auth['password'];

    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");

    	try{
    		$user = $dao->login($name,crypt($pass,$name));

    		//ログイン状態をセッションに保存
    		UserInfoUtil::login($user);

			$user->setLastLoginDate(time());
			$dao->update($user);

    	}catch(Exception $e){

    		$this->setErrorMessage('loginfailed','ログインに失敗しました。');
	    	$this->setAttribute('username',$auth['name']);
	    	return SOY2Action::FAILED;
    	}

    	return SOY2Action::SUCCESS;
    }
}
