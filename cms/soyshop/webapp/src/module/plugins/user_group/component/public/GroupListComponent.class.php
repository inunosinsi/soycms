<?php
SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
class GroupListComponent extends HTMLList{

	private $groupingDao;

	function populateItem($entity, $i){

		$this->addLabel("name", array(
			"soy2prefix" => UserGroupCustomSearchFieldUtil::PLUGIN_PREFIX,
			"text" => $entity->getName()
		));

		$this->addLabel("code", array(
			"soy2prefix" => UserGroupCustomSearchFieldUtil::PLUGIN_PREFIX,
			"text" => $entity->getCode()
		));

		//カスタムサーチフィールド
		$values = self::getCustomValues($entity->getId());

		/** @ToDo 各カラムの出力 **/
    }

	private function getCustomValues($groupId){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupDataBaseLogic");
		if(!isset($groupId)) return array();
		return $logic->getByGroupId($groupId);
	}
}
