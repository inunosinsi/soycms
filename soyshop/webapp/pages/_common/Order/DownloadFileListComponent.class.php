<?php

class DownloadFileListComponent extends HTMLList{

	private $order;

	protected function populateItem($entity) {

		$this->addLabel("file_name", array(
			"text" => $entity->getFileName()
		));

		$this->addLabel("time_limit", array(
			"text" => (is_numeric($entity->getTimeLimit())) ? date("Y年m月d日", $entity->getTimeLimit()) : "無期限"
		));

		$this->addLabel("count", array(
			"text" => (!is_null($entity->getCount())) ? $entity->getCount() . " 回" : "無制限"
		));
	}

	function setOrder(SOYShop_Order $order){
		$this->order = $order;
	}
}
