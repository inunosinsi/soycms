<?php

class IndexPage extends CMSWebPageBase{
	
	function doPost(){
		
		if(soy2_check_token()){
			$dao = self::dao();
			
			if(isset($_POST["add"])){
				if(strlen($_POST["Config"]["sign"]) === 0) SOY2PageController::jump("Application.Secret?error");
				
				$obj = SOY2::cast("AppDB", $_POST["Config"]);
				
				try{
					$dao->insert($obj);
					SOY2PageController::jump("Application.Secret?success");
				}catch(Exception $e){
					//
				}
			}else if(isset($_POST["update"])){
				$old = self::getDefAccount();
				$defAcc = SOY2::cast($old, $_POST["Def"]);
				
				//新規
				if(is_null($defAcc->getId())){
					try{
						self::dao()->insert($defAcc);
						SOY2PageController::jump("Application.Secret?success");
					}catch(Exception $e){
						//
					}
				//更新
				}else{
					try{
						self::dao()->update($defAcc);
						SOY2PageController::jump("Application.Secret?success");
					}catch(Exception $e){
						//
					}
				}
			}
		}
		
		SOY2PageController::jump("Application.Secret?error");
	}
	
	function __construct(){
		if(UserInfoUtil::isDefaultUser() != 1) SOY2PageController::jump("");
		
		parent::__construct();
		
		$this->addForm("form");
				
		$this->addInput("def_account_id_hidden", array(
			"type" => "hidden",
			"name" => "Def[accountId]",
			"value" => 1
		));
		
		$this->addInput("def_sign", array(
			"name" => "Def[sign]",
			"value" => self::getDefAccount()->getSign(),
			"style" => "width:100%;"
		));
		
		$this->createAdd("accout_list", "AccountListComponent", array(
			"list" => self::get(),
			"adDao" => SOY2DAOFactory::create("admin.AdministratorDAO")
		));
		
		$this->addSelect("account", array(
			"name" => "Config[accountId]",
			"options" => self::getAccountList()
		));
		
		$this->addInput("sign", array(
			"name" => "Config[sign]",
			"value" => "",
			"style" => "width:100%;",
			"attr:pattern" => "^[a-zA-Z0-9]+$"
		));
	}
	
	private function getDefAccount(){
		try{
			return self::dao()->getByAccountId(1);
		}catch(Exception $e){
			return new AppDB();
		}
	}
	
	private function get(){
		$dao = self::dao();
		$dao->setOrder("account_id ASC");
		try{
			return $dao->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	private function getAccountList(){
		try{
			$array = SOY2DAOFactory::create("admin.AdministratorDAO")->get();
		}catch(Exception $e){
			return array();
		}
		
		
		$list = array();
		foreach($array as $obj){
			if($obj->getIsDefaultUser() == 1) continue;	//defaultuserは除く
			$list[$obj->getId()] = $obj->getUserId();
		}
		
		return $list;
	}
	
	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("service.AppDBDAO");
		return $dao;
	}
}

class AccountListComponent extends HTMLList{
	
	private $adDao;
	
	protected function populateItem($entity, $key){
		
		$this->addLabel("account", array(
			"text" => self::getAccount($entity->getAccountId())
		));
		
		$this->addLabel("sign", array(
			"text" => $entity->getSign(),
		));
		
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Application.Secret.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除しますか？');"
		));
		
		
		if(method_exists($entity, "getAccountId") && $entity->getAccountId() == 1) return false;
	}
	
	private function getAccount($accountId){
		try{
			return $this->adDao->getById($accountId)->getUserId();
		}catch(Exception $e){
			return "";
		}
	}
	
	function setAdDao($adDao){
		$this->adDao = $adDao;
	}
}
?>