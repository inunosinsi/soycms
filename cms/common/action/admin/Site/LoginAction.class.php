<?php

class LoginAction extends SOY2Action{

	var $siteId;

	function setSiteId($id){
		$this->siteId = $id;
	}

	function execute(){

		$userId = UserInfoUtil::getUserId();
		$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		/*
		 * サイトの権限を取得する
		 */
		if(UserInfoUtil::isDefaultUser()){
			//初期管理者は一般管理者と同等
			$siteRole = new SiteRole();
			$siteRole->setUserId($userId);
			$siteRole->setSiteId($this->siteId);
			$siteRole->setSiteRole(SiteRole::SITE_SUPER_USER);
		}else{
			try{
				$siteRole = $siteRoleDao->getSiteRole($this->siteId, $userId);
			}catch(Exception $e){
				return SOY2Action::FAILED;
			}
		}

		try{
			//ここに来てる時点で複数の管理サイトの権限を持っているので第２引数はfalse
			UserInfoUtil::loginSite($siteRole, false);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
	}

}
?>