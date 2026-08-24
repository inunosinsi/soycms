<?php

class NotFoundLogListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("cdate", array(
			"text" => (isset($entity["cdate"])) ? date("Y-m-d H:i:s", (int)$entity["cdate"]) : "---"
		));

		$this->addLabel("ipv4", array(
			"text" => (isset($entity["v4"])) ? $entity["v4"] : "",
			"style" => (isset($entity["is_suspicious"]) && (int)$entity["is_suspicious"] === 1) ? "font-weight:bold;" : ""
		));

		$strCnt = 30;
		$uri = (isset($entity["uri"])) ? $entity["uri"] : "";		
		$this->addLabel("uri", array(
			"text" => (strlen($uri) >= $strCnt) ? substr($uri, 0, ($strCnt-3))."..." : $uri,
			"attr:data-toggle" => "tooltip",
			"title" => $uri,
			"style" => "cursor:default"
		));
	}
}
