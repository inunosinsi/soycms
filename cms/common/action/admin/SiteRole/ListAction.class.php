<?php

class ListAction extends SOY2Action{

	private $userId = null;
	private $siteId = null;
	private $limitSite = false;
	
	function setUserId($userId){
		$this->userId = $userId;
	}
	
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
	
	function setLimitSite($value){
		$this->limitSite = $value;
	}
	

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	if(is_null($this->userId)){
    		if(is_null($this->siteId)){
    			//両方nullなのでエラー
    			return SOY2Action::FAILED;	
    		}else{
    			//siteを主体とするリストを返す
    			return $this->getListBySiteId($this->siteId);
    		}
    	}else{
    		if(is_null($this->siteId)){
    			//userIdを主体とするリストを返す
    			return $this->getListByUserId($this->userId);
    		}else{
    			//siteId,userIdの2次元のリストを返す
    			//現在は使っている部分は無いのでとりあえずはエラー
    			//必要ならば追記
    			return SOY2Action::FAILED;
    		}
    	}
    }
    
    /**
     * userIdに関連付けられている権限を取得し配列を返す
     */
    private function getListByUserId($userId){
    	$logic = SOY2Logic::createInstance("logic.admin.SiteRole.SiteRoleLogic");
    	$siteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
    	$adminLogic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
    	
    	//初期ユーザーの権限変更画面は表示されない
    	if($adminLogic->checkDefaultUser($userId)){
    		return SOY2Action::FAILED;
    	}
    	
    	//SiteRoleのリストからselectに加工するための配列をつくる
    	//ログイン中のユーザーがログインできるサイトのみ取得する
    	if($this->limitSite){
	    	$siteList = $siteLogic->getSiteByUserId(UserInfoUtil::getUserId());
    	}else{
	    	$siteList = $siteLogic->getSiteList();
    	}
    	
    	$siteTitleArray = array(); //siteTitleArray[siteId]=>サイト名
    	$siteRoleArray = array();  //siteRoleArray[siteId]=>サイト権限
    	
    	
    	foreach($siteList as $key =>$site){
    		$siteTitleArray[$site->getId()] = $site->getSiteId();
    		$siteRoleArray[$site->getId()] = $logic->getSiteRole($site->getId(),$this->userId);
    	}
    	
    	$this->setAttribute("siteTitle",$siteTitleArray);
    	$this->setAttribute("siteRole",$siteRoleArray);
    	$this->setAttribute("adminName",$adminLogic->getById($this->userId));
    	
    	return SOY2Action::SUCCESS;    	
    }
    
    /**
     * siteIdに関連付けられている権限を取得し配列を返す
     */
    private function getListBySiteId($siteId){
    	$logic = SOY2Logic::createInstance("logic.admin.SiteRole.SiteRoleLogic");
    	$adminLogic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
    	$siteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
    	
    	//SiteRoleのリストからselectに加工するための配列をつくる
    	$adminList = $adminLogic->getAdministratorList();
    	
    	$adminNameArray = array(); //siteTitleArray[userId]=>ユーザ名
    	$siteRoleArray = array();  //siteRoleArray[userId]=>サイト権限
    	
    	
    	foreach($adminList as $key =>$admin){
    		//初期ユーザーは表示しない
    		if($adminLogic->checkDefaultUser($admin->getId())){
    			continue;
    		}
    		$adminNameArray[$admin->getId()] = $admin->getUserId();
    		$siteRoleArray[$admin->getId()] = $logic->getSiteRole($this->siteId,$admin->getId());
    	}
    	
    	$this->setAttribute("adminName",$adminNameArray);
    	$this->setAttribute("siteRole",$siteRoleArray);
    	$this->setAttribute("siteTitle",$siteLogic->getById($this->siteId));
    	
    	return SOY2Action::SUCCESS;    	
    }

}
?>