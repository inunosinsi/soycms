<?php

class StorageListComponent extends HTMLList{

	private $downloadUrl;

	protected function populateItem($entity){

		$this->addLabel("file_name", array(
			"text" => $entity->getFileName()
		));

		$this->addLabel("upload_date", array(
			"text" => (is_numeric($entity->getUploadDate())) ? date("Y-m-d H:i:s", $entity->getUploadDate()) : ""
		));

		$this->addInput("download_input", array(
			"value" => $this->downloadUrl . $entity->getToken(),
			"style" => "width:95%;",
			"readonly" => true,
			"onclick" => "this.select()"
		));

		$this->addLink("download_link", array(
			"link" => $this->downloadUrl . $entity->getToken()
		));
	}

	function setDownloadUrl($downloadUrl){
		$this->downloadUrl = $downloadUrl;
	}
}
