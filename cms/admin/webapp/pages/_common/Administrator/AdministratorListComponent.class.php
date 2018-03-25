<?php

class AdministratorListComponent extends HTMLList{

	private $sites = null;

	protected function populateItem($entity){

		$this->addLabel("userId", array(
			"text" => $entity->getUserId()
		));

		$this->addLabel("userName", array(
			"text" => $entity->getName()
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Administrator.SiteRole.".$entity->getId()),
			"visible"=> !$entity->getIsDefaultUser(),
			"text"=>(UserInfoUtil::isDefaultUser()) ? CMSMessageManager::get("ADMIN_ROLE_SETTING") : CMSMessageManager::get("ADMIN_DISPLAY_ROLES")
		));

		$this->addLink("update_link", array(
			"link" => SOY2PageController::createLink("Administrator.Detail.".$entity->getId()),
			"text"=>(UserInfoUtil::isDefaultUser() || $entity->getId() == UserInfoUtil::getUserId()) ? CMSMessageManager::get("ADMIN_DETAIL_EDIT") : CMSMessageManager::get("ADMIN_DISPLAY_DETAILS")
		));

		//パスワード変更（初期管理者限定）
		//遷移先では現在のパスワードがなくても変更できてしまうので、自身のパスワード変更は行えないようにしておく
		$this->addLink("update_password_link", array(
				"link" => SOY2PageController::createLink("Administrator.Password.".$entity->getId()),
				"visible"=> UserInfoUtil::isDefaultUser() && $entity->getId() != UserInfoUtil::getUserId(),
		));

		//自身のパスワード変更
		$this->addLink("update_password_link_for_current_user", array(
				"link" => SOY2PageController::createLink("Administrator.ChangePassword"),
				"visible"=> $entity->getId() == UserInfoUtil::getUserId(),
		));

		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Administrator.Remove." . $entity->getId()),
			"visible"=> UserInfoUtil::isDefaultUser() && !$entity->getIsDefaultUser(),
		));

		$siteName = array();

		if($entity->getIsDefaultUser()){
			$siteName[] = CMSMessageManager::get("ADMIN_SUPER_USER");
		}else{
			foreach($entity->sites as $managed){
				if(isset($this->sites[$managed->getSiteId()])){
					$siteName[] = htmlspecialchars($this->sites[$managed->getSiteId()]->getSiteName(), ENT_QUOTES, "UTF-8");
								//."<br/>". htmlspecialchars(" => ".$managed->getSiteRoleText(),ENT_QUOTES);
				}
			}
		}

		$this->addLabel("managingSite", array(
			"html"=>implode("<br />", $siteName)
		));
	}

	function setSites($sites){
		$this->sites = $sites;
	}

	function getSiteRoleText($siteRole){
		$list = SiteRole::getSiteRoleLists();
		$text = $list[(int)$siteRole];
	}
}
