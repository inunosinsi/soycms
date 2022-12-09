<?php

class SOYShop_CartItemListComponent extends HTMLList{

	protected function populateItem($entity){
		$this->addLabel("item_name", array(
			"text" => $entity->getItemName(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format((int)$entity->getItemPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_count", array(
			"text" => soy2_number_format((int)$entity->getItemCount()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format((int)$entity->getTotalPrice()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}
}