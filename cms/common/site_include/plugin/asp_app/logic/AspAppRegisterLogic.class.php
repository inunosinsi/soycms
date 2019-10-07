<?php

class AspAppRegisterLogic extends SOY2LogicBase {

	private $adminId;

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.domain.AspAppPreRegisterDAO");
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
	}

	function register($token){
		$obj = self::_getByToken($token);
		if(is_null($obj->getToken())) return false;

		$data = soy2_unserialize($obj->getData());
		$admin = self::castAdmin($data);

		//管理者の作成権限の付与
		$res = self::addAppAuth($admin);

		//登録作業が終了したら、tokenを消す
		if($res) self::deleteByToken($token);

		return $res;
	}

	private function addAppAuth(Administrator $admin){
		$appId = AspAppUtil::getAppIdConfig();

		$old = CMSUtil::switchDsn();
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");

		$res = true;

		//トランザクションが使えない

		try {
			$this->adminId = $dao->insert($admin);

			$roleDao = SOY2DAOFactory::create("admin.AppRoleDAO");
			$role = new AppRole();
			$role->setUserId($this->adminId);
			$role->setAppId($appId);
			$role->setAppRole(AppRole::APP_USER);
			$roleDao->insert($role);
		} catch (Exception $e) {
			$res = false;
		}

		CMSUtil::resetDsn($old);

		return $res;
	}

	function getAdminId(){
		return $this->adminId;
	}

	function getAdminByToken($token){
		return self::castAdmin(soy2_unserialize(self::_getByToken($token)->getData()));
	}

	private function _getByToken($token){
		static $obj;
		if(is_null($obj)){
			try{
				$obj = self::dao()->getByToken(trim($token));
			}catch(Exception $e){
				$obj = new AspPreRegister();
			}
		}
		return $obj;
	}

	private function deleteByToken($token){
		try{
			self::dao()->deleteByToken($token);
		}catch(Exception $e){
			//
		}
	}

	private function castAdmin($values){
		$old = CMSUtil::switchDsn();
		SOY2::import("domain.admin.Administrator");

		if(is_array($values) && count($values)){
			$admin = SOY2::cast("Administrator", $values);
		}else{
			$admin = new Administrator();
		}

		CMSUtil::resetDsn($old);

		//user_idをメールアドレスにする
		if(!strlen($admin->getUserId())) $admin->setUserId($admin->getEmail());

		return $admin;
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("AspAppPreRegisterDAO");
		return $dao;
	}
}
