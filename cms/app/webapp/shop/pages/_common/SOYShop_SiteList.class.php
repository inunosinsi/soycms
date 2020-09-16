<?php
class SOYShop_SiteList extends HTMLList{

	private $logic;

	function populateItem($entity, $key, $index){
		//site id
		$this->addLabel("soyshop_site_id", array(
			"text" => $entity->getSiteId()
		));

		//site name
		$this->addLabel("soyshop_site_name", array(
			"text" => $entity->getSiteName()
		));

		//site url
		$this->addLink("soyshop_site_url", array(
			"link" => $entity->getUrl(),
			"text" => $entity->getUrl(),
			"target" => "_blank"
		));

		//site db
		$this->addLabel("soyshop_site_db", array(
			"text" => ($entity->getIsMysql())? "MySQL": "SQLite"
		));


		//login
		$this->addLink("soyshop_site_login", array(
			"link" => SOY2PageController::createRelativeLink("../soyshop?site_id=" . $entity->getSiteId()),
			"text" => "ログイン"
		));

		$this->addLink("site_detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.Detail." . $entity->getId()),
			"text" => "詳細"
		));
		$this->addLink("config_detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Config.Detail." . $entity->getId()),
			"text" => "詳細"
		));

		$res = $this->logic->checkIsRootSite($entity->getSiteId());
		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.Remove." . $entity->getId()),
			"visible" => (!$res),
			"attr:id" => "remove_" . $entity->getSiteId()
		));

	}

	function setLogic($logic){
		$this->logic = $logic;
	}
}
