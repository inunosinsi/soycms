<?php

class GroupListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$this->addLink("group_detail_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/" . $id
		));
	}
}
