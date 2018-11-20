<?php

class AspRegisterLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.asp.domain.AspPreRegisterDAO");
	}

	function register($token){
		$obj = self::_getByToken($token);
		if(is_null($obj->getToken())) return false;

		$data = soy2_unserialize($obj->getData());
		$admin = self::castAdmin($data);

		//管理者の作成、サイトの作成、権限の付与
		$res = self::createSite($obj->getSiteId(), $admin);

		//登録作業が終了したら、tokenを消す
		if($res) self::deleteByToken($token);

		return $res;
	}

	private function createSite($siteId, Administrator $admin){
		$old = CMSUtil::switchDsn();
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");

		$res = true;

		//トランザクションが使えない

		try {
			$adminId = $dao->insert($admin);

			//サイトの作成
			define("CMS_SITE_INCLUDE", _CMS_COMMON_DIR_ . "/site.inc.php");
			define("CMS_SQL_DIRECTORY", _CMS_COMMON_DIR_ . "/sql/");

			//エンコードは決め打ち
			$id = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->createSite($siteId, $siteId, "UTF-8", true, false, "sqlite");

			$roleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
			$role = new SiteRole();
			$role->setUserId($adminId);
			$role->setSiteId($id);
			$role->setIsLimitUser(SiteRole::SITE_SUPER_USER);
			$roleDao->insert($role);
		} catch (Exception $e) {
			$res = false;
		}

		CMSUtil::resetDsn($old);

		return $res;
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
		$admin->setUserId($admin->getEmail());

		return $admin;
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("AspPreRegisterDAO");
		return $dao;
	}
}
