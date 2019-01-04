<?php

class UrlListComponent extends HTMLList{

	protected function populateItem($entity, $key){
		
		$url = (isset($entity["url"]) && strlen($entity["url"])) ? $entity["url"] : "";
		$this->addLink("url", array(
			"link" => $url,
			"text" => $url,
			"target" => "_blank"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?sitemap&remove=" . $key),
			"onclick" => "return confirm('削除しますか？');"
		));
	}
}
