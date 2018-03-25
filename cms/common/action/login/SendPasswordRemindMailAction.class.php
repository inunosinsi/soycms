<?php

class SendPasswordRemindMailAction extends SOY2Action{

	protected function execute(SOY2ActionRequest &$request, SOY2ActionForm &$form, SOY2ActionResponse &$response){

		$email = $request->getParameter("email");
		$userId = $request->getParameter("user_id");

		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

		if(strlen($email) < 1 || strlen($userId) < 1){
			$this->setErrorMessage("error", "ログインIDとメールアドレスを入力して下さい。");
			return SOY2Action::FAILED;
		}

		try{
			$user = $dao->getByUserIdAndEmail($userId, $email);
		}catch (Exception $e){
			$this->setErrorMessage("error", "入力項目に誤りがあります。");
			return SOY2Action::FAILED;
		}

		$token = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->generateToken($user->getId());
		if(is_null($token)){
			$this->setErrorMessage("error", "エラーが発生しました。");
			return SOY2Action::FAILED;
		}

		try{
			$mailLogic->sendPasswordRemindMail($user->getEmail(), strlen($user->getName()) ? $user->getName() : $user->getUserId() , $token);
		}catch(Exception $e){
			$this->setErrorMessage("error", "メールの送信中にエラーが発生しました。");
			return SOY2Action::FAILED;
		}
		return SOY2Action::SUCCESS;
	}
}
