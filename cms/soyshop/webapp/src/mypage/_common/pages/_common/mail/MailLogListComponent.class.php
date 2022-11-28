<?php

class MailLogListComponent extends HTMLList{

	function populateItem($entity){

		$this->addLabel("send_date", array(
			"text" => (is_numeric($entity->getSendDate())) ? date("Y年m月d日 H:i", $entity->getSendDate()) : ""
		));

		//詳細リンク
		$this->addLink("title", array(
			"link" => soyshop_get_mypage_url() . "/mail/detail/" . $entity->getId(),
			"text" => $entity->getTitle()
		));
	}
}
