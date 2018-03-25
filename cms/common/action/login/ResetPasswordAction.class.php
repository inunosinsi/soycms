<?php
SOY2::import("util.PasswordUtil");

class ResetPasswordAction extends SOY2Action{

	protected function execute(SOY2ActionRequest &$request, SOY2ActionForm &$form, SOY2ActionResponse &$response){

		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$token = $request->getParameter("token");

		if(strlen($token) < 1){
			$this->setErrorMessage("error", "URLが無効か期限が切れています。");
			return SOY2Action::FAILED;
		}

		try{
			$user = $dao->getByToken($token);
		}catch (Exception $e){
			$this->setErrorMessage("error", "URLが無効か期限が切れています。");
			return SOY2Action::FAILED;
		}

		if($_SERVER["REQUEST_TIME"] > $user->getTokenIssuedDate()+60*60){
			$this->setErrorMessage("error", "URLが無効か期限が切れています。");
			return SOY2Action::FAILED;
		}

		$password = $request->getParameter("password");
		$validation = $request->getParameter("validation");

		if(strlen($password) < 6){
			$this->setErrorMessage("error", CMSMessageManager::get("ADMIN_PASSWORD_IS_TOO_SHORT"));
			return SOY2Action::FAILED;
		}

		if(strlen($password) > 255){
			$this->setErrorMessage("error", CMSMessageManager::get("ADMIN_PASSWORD_IS_TOO_LONG"));
			return SOY2Action::FAILED;
		}

		if($password != $validation){
			$this->setErrorMessage("error", CMSMessageManager::get("ADMIN_PASSWORDS_NOT_SAME"));
			return SOY2Action::FAILED;
		}

		$user->setUserPassword(PasswordUtil::hashPassword($password));
		$user->setToken(null);
		$user->setTokenIssuedDate(null);

		try{
			$dao->update($user);
		}catch (Exception $e){
			$this->setErrorMessage("error", "パスワードの変更に失敗しました。");
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
	}
}
