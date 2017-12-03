<?php
class ExtendUserDAO extends SOY2LogicBase{
	
	private $extendDao;
	
	function getByUserId($userId){
		if(!$this->extendDao)$this->extendDao = $this->getDAO();
		
		$className = $this->getDAOClassName();
		
		if($className==="SOYShop_User")$old = SOYMailUtil::switchSOYShopConfig();
		
		try{
			$user = $this->extendDao->getById($userId);
		}catch(Exception $e){
			$user = new $className;
		}
		
		if($className==="SOYShop_User")SOYMailUtil::resetConfig($old);
		
		return $user;
	}
	
	function getDAOClassName(){
		if(!$this->extendDao)$this->extendDao = $this->getDAO();
		return str_replace("DAOImpl","",get_class($this->extendDao));
	}
	
	/**
	 * SOY Shop連携を行っている場合はtrueを返す
	 * @return boolean
	 */
	function checkSOYShopConnect(){
		$className = $this->getDAOClassName();
		return ($className==="SOYShop_User") ? true : false;
	}
	
	function countUser(){
		if(!$this->extendDao)$this->extendDao = $this->getDAO();
		
		$className = $this->getDAOClassName();
			
		if($className==="SOYShop_User")$old = SOYMailUtil::switchSOYShopConfig();
		
		try{
			$count = $this->extendDao->countUser();
		}catch(Exception $e){
			$count = 0;
		}
		
		if($className==="SOYShop_User")SOYMailUtil::resetConfig($old);
		
		return $count;
	}
	
	function getDAO(){
		
		$connectorDao = SOY2DAOFactory::create("SOYMail_SOYShopConnectorDAO");
		try{
			$config = $connectorDao->get()->getConfig();
		}catch(Exception $e){
			$config = array();
		}
		
		$userDao = null;
		
		if(isset($config["siteId"]) && $config["siteId"] > 0){

			$old = SOYMailUtil::switchConfig();
			
			$siteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			try{
				$site = $siteDao->getById($config["siteId"]);
			}catch(Exception $e){
				$site = new SOYShop_Site();
			}
			if(!defined("SOYSHOP_SITE_ID"))define("SOYSHOP_SITE_ID",$site->getSiteId());
			
			SOYMailUtil::resetConfig($old);
			
			//サイトの情報が取得出来た場合
			if(!is_null($site->getId())){
				$old = SOYMailUtil::switchSOYShopConfig();
				
				$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				//SOY2::import("domain.config.SOYShop_Area");
				
				SOYMailUtil::resetConfig($old);
			}
		}
		
		if(is_null($userDao))$userDao = SOY2DAOFactory::create("SOYMailUserDAO");
		
		return $userDao;
	}
}

?>