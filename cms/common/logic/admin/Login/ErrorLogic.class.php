<?php

class ErrorLogic extends SOY2LogicBase{
	
	const ERROR_CNT = 10;	//エラーの回数で不正ログインと見做す
	
	//ログインエラーを記録
	function save(){
		$ip = $_SERVER["REMOTE_ADDR"];
		$obj = self::getByIp($ip);
		
		//新規で記録
		if(is_null($obj->getIp())){
			$obj->setIp($ip);
			$obj->setCount(1);
			$obj->setSuccessed(0);
			
			try{
				self::dao()->insert($obj);
			}catch(Exception $e){
				//
			}
		//更新
		}else{
			$cnt = (int)$obj->getCount();
			$obj->setCount(++$cnt);
			
			try{
				self::dao()->update($obj);
			}catch(Exception $e){
				//
			}
		}
						
		//警告メールを送信する
		if($obj->getCount() >= self::ERROR_CNT && $obj->getCount() % self::ERROR_CNT === 0){
			try{
				$accounts = SOY2DAOFactory::create("admin.AdministratorDAO")->get();
			}catch(Exception $e){
				$accounts = array();
			}
			
			if(count($accounts)) {
				$serverConfig = SOY2LogicContainer::get("logic.mail.MailConfigLogic")->get();
				$from = $serverConfig->getFromMailAddress();
				if(!is_null($from) && strlen($from)){
					$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");	
					$title = "総当たり攻撃を受けている可能性があります。";
					$body = "総当たり攻撃を受けている可能性があります。\n管理画面にログインして詳細を確認してください。";	
					$body .= "\n\nドメイン:" . $_SERVER["HTTP_HOST"];
					$body .= "\nID：" . $_POST["Auth"]["name"] . "でログインを試みられています。";
					
					foreach($accounts as $account){
						if($account->getIsDefaultUser() && strlen($account->getEmail())){
							$mailLogic->sendMail($account->getEmail(), $title, $body, $_SERVER["HTTP_HOST"]);
						}
					}
				}	
			}
		}
		
		//古いデータは削除しておく
		self::clean();
	}
	
	function saveLogin(){
		$obj = self::getByIp($_SERVER["REMOTE_ADDR"]);
		if($obj->getCount() >= self::ERROR_CNT && (int)$obj->getSuccessed() === 0){
			$obj->setSuccessed($obj->getCount());
			try{
				self::dao()->update($obj);
			}catch(Exception $e){
				//
			}
		}
		
		//古いデータは削除しておく
		self::clean();
	}
	
	//ログの削除:参照速度を上げるため
	private function clean(){
		self::dao()->clean(self::ERROR_CNT);
	}
	
	//短い期間で何度もログインを試みた形跡があるか
	function hasErrorLogin(){
		try{
			return self::dao()->hasErrorLogin(self::ERROR_CNT);
		}catch(Exception $e){
			return false;
		}
	}
	
	function getCandidates(){
		try{
			return self::dao()->getCandidates(self::ERROR_CNT);
		}catch(Exception $e){
			return array();
		}
	}
	
	function measure($id){
		try{
			$obj = self::dao()->getById($id);
		}catch(Exception $e){
			return false;
		}
		
		if(is_null($obj->getIp())) return false;
		
		try{
			self::dao()->deleteByIp($obj->getIp());
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	private function getByIp($ip){
		try{
			return self::dao()->getByIp($ip);
		}catch(Exception $e){
			return new LoginErrorLog();
		}
	}
	
	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("admin.LoginErrorLogDAO");
		return $dao;
	}
}
?>