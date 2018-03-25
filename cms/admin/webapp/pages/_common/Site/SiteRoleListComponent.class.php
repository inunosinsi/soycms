<?php

class SiteRoleListComponent extends HTMLList{

	private $site;
	private $userId;
	private $dao;

	function setSite($site){
		$this->site = $site;
	}

	function setUserId($userId){
		$this->userId = $userId;
	}

	function setDao($dao){
		$this->dao = $dao;
	}

	protected function populateItem($entity, $key){

		$this->addLabel("site_name", array(
			"text"	=> $this->site[$key],
		));


		$this->addSelect("site_role", array(
			"options" => SiteRole::getSiteRoleLists(),
			"name" => "siteRole[" . $this->userId . "][" . $key . "]",
			"indexOrder" => true,
			"selected" => (int)$entity,
			"visible"=>UserInfoUtil::isDefaultUser(),
			"disabled" => (self::getSiteType($key) == 2)
		));

		$list = SiteRole::getSiteRoleLists();
		$text = $list[(int)$entity];
		$this->addLabel("site_role_text", array(
			"text" => $text,
			"visible" => !UserInfoUtil::isDefaultUser()
		));
	}

	private function getSiteType($key){
		try{
			return $this->dao->getById($key)->getSiteType();
		}catch(Exception $e){
			return Site::TYPE_SOY_CMS;
		}
	}
}
