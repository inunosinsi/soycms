<?php

class ModuleListComponent extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("module_id", array(
			"text" => $entity->getId()
		));

		$detailLink = SOY2PageController::createLink("Plugin.Detail." . $entity->getId());
		$this->addLink("module_name", array(
			"text" => $entity->getName(),
			"link" => $detailLink
		));
		$this->addLabel("module_is_active", array(
			"text" => (($entity->getIsActive())? "インストール済み" : "未インストール")
		));
		$this->addInput("module_display_order", array(
			"name" => "Plugin[" . $entity->getId() . "]",
			"value" => ($entity->getDisplayOrder() < SOYShop_PluginConfig::DISPLAY_ORDER_MAX) ? $entity->getDisplayOrder() : null,
			"style" => "width:60px;"
		));
		$this->addLink("module_detail_link", array(
			"link" => $detailLink
		));

		return (strlen((string)$entity->getName()) > 0);
	}

}
