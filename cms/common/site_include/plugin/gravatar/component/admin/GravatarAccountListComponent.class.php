<?php

class GravatarAccountListComponent extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$this->addLink("mail_address", array(
			"text" => $entity->getMailAddress(),
			"link" => "mailto:" . $entity->getMailAddress()
		));

		$this->addLink("remove_link", array(
			"link" => "?gravatar&remove=" . $entity->getId() . "#config",
			"onclick" => "return confirm('削除しますか？');"
		));

		$values = (get_class($entity) == "GravatarAccount") ? self::logic()->getGravatarValuesByAccount($entity) : array();
		$this->addLink("confirm_link", array(
			"link" => (isset($values["profileUrl"])) ? $values["profileUrl"] : null,
			"visible" => (isset($values["profileUrl"]))
		));
	}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic");
		return $logic;
	}
}
