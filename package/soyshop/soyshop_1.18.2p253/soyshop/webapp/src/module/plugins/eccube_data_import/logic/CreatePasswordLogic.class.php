<?php

class CreatePasswordLogic extends SOY2LogicBase{
	
	private $userDao;
	private $logic;
	
	function __construct(){
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}
	
	function execute(){
		set_time_limit(0);
		
		$users = self::getUsers();
		
		if(count($users) === 0) return;
		
		//IDとパスワードを保持しておく
		$exports = array();
		foreach($users as $id => $user){
			$pass = self::makeRandStr();
			$user->setPassword($user->hashPassword($pass));
			try{
				$this->userDao->update($user);
			}catch(Exception $e){
				continue;
			}

			$exports[$id] = $pass;
		}
		
		//CSVエクスポート
		if(count($exports)){
			$this->logic = SOY2Logic::createInstance("logic.shop.item.ExImportLogic");
			$this->logic->setCharset($_POST["charset"]);
			
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; filename=import_ec_cube_pass-".date("Ymd").".csv");
			header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");
			
			//ラベル
			echo "ID,お名前,メールアドレス,パスワード\r\n";
			
			for($i = 0; $i < count($exports); $i++){
				$lines = array();
				$lines[] = $users[$i]->getId();
				$lines[] = $users[$i]->getName();
				$lines[] = $users[$i]->getMailAddress();
				$lines[] = $exports[$i];
				echo implode(",", $lines) . "\r\n";
			}
			
			exit;
		}
	}
	
	private function getUsers(){
		try{
			$results = $this->userDao->executeQuery("SELECT * FROM soyshop_user WHERE attribute3 = 'EC CUBE' AND is_disabled != 1;", array());
		}catch(Exception $e){
			return array();
		}
		
		$users = array();
		foreach($results as $res){
			if(!isset($res["id"])) continue;
			$users[] = $this->userDao->getObject($res);
		}
		
		return $users;
	}
	
	private function makeRandStr($length = 8) {
	    static $chars;
	    if (!$chars) {
	        $chars = array_flip(array_merge(
	            range('a', 'z'), range('A', 'Z'), range('0', '9')
	        ));
	    }
	    $str = '';
	    for ($i = 0; $i < $length; ++$i) {
	        $str .= array_rand($chars);
	    }
	    return $str;
	}
}
?>