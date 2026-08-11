<?php
class UserGroupAdminList extends SOYShopAdminListBase{

	function getTabName(){
		return "グループ";
	}

	function getTitle(){
		return "グループ";
	}

	function getContent(){
		//削除のURLを含んでいるか？
        if(strpos($_SERVER["REQUEST_URI"], "/Remove/") && soy2_check_token()){
			preg_match('/\/Remove\/(.*)\?/', $_SERVER["REQUEST_URI"], $tmp);
			if(isset($tmp[1]) && is_numeric($tmp[1])){
				if(self::remove((int)$tmp[1])){
					SOY2PageController::jump("Extension.user_group?removed");
				}
			}
		}

		SOY2::import("module.plugins.user_group.page.UserGroupListPage");
		$form = SOY2HTMLFactory::createInstance("UserGroupListPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	private function remove($groupId){
		SOY2::imports("module.plugins.user_group.domain.*");
		$dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
		try{
			$group = $dao->getById($groupId);
		}catch(Exception $e){
			return false;
		}

		$group->setName($group->getName() . "(削除)");
		$group->setIsDisabled(SOYShop_UserGroup::IS_DISABLED);

		try{
			$dao->update($group);
		}catch(Exception $e){
			return false;
		}

		return true;
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "user_group", "UserGroupAdminList");
