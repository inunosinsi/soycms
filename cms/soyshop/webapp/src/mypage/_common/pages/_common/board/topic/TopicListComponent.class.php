<?php

class TopicListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("label", array(
			"text" => $entity->getLabel()
		));

		$this->addLink("topic_detail_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/detail/" . $id
		));
	}
}
