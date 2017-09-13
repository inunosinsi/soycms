<?php

class GroupListComponent extends HTMLList{

	private $groupingDao;

	function populateItem($entity, $i){

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$total = self::countUser($entity->getId());
		$this->addLabel("count_user", array(
			"text" => $total
		));

		$this->addLink("group_detail_link", array(
			"link" => ($total > 0) ? SOY2PageController::createLink("Config.Detail?plugin=user_group&group_id=" . $entity->getId()) : null
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.user_group." . $entity->getId())
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Extension.user_group.Remove." . $entity->getId()),
			"onclick" => "return confirm('削除しますか？');"
		));
    }

	private function countUser($groupId){
		try{
		 	return (int)$this->groupingDao->countByGroupId($groupId);
		}catch(Exception $e){
			return 0;
		}
	}

	function setGroupingDao($groupingDao){
		$this->groupingDao = $groupingDao;
	}
}
