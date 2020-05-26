<?php

class SupplierListComponent extends HTMLList {

	protected function populateItem($entity) {
		$this->addLink("name", array(
			"text" => $entity->getName(),
			"link" => SOY2PageController::createLink("Extension.Detail.supplier_manager." . $entity->getId())
		));

		$this->addLabel("area", array(
			"text" => (is_numeric($entity->getArea())) ? SOYShop_Area::getAreaText($entity->getArea()) : "---"
		));

		$this->addLabel("telephone_number", array(
			"text" => $entity->getTelephoneNumber()
		));

		$this->addLink("mail_address", array(
			"link" => "mailto:" . $entity->getMailAddress(),
			"text" => $entity->getMailAddress()
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.supplier_manager." . $entity->getId())
		));
	}
}
