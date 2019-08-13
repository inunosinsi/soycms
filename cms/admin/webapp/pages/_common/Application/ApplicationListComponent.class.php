<?php

class ApplicationListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		$this->addLabel("name", array(
			"text" => $entity["title"]
		));

		/**
		 * ログイン後の転送先（$_GET["r"]）があれば再度$_GET["r"]に入れておく
		 */
		$param = array();
		if(isset($_GET["r"]) && strlen($_GET["r"]) && strpos($_GET["r"], "/app/index.php/" . $key)) $param["r"] = $_GET["r"];
		$loginLink = SOY2PageController::createRelativeLink("../app/index.php/" . $key) . ( count($param) ? "?" . http_build_query($param) : "" );
		if(strpos($loginLink, "?")){
			$loginLink .= "&login";
		}else{
			$loginLink .= "?login";
		}
		$this->addLink("login_link", array(
			"link" => $loginLink
		));

		$this->addLabel("description", array(
			"text" => $entity["description"]
		));

		$this->addLabel("version", array(
			"text" => $entity["version"],
			"visible" => (isset($entity["version"]))
		));

		$this->addLink("auth_link", array(
			"link" => SOY2PageController::createLink("Application.Role") . "?app_id=" . $key
		));
	}
}
