<?php

class RemovePage extends WebPage{

	function __construct($args) {

		if(soy2_check_token()){
			$id = (int)$args[0];
			if(!is_numeric(soyshop_get_item_object($id)->getId())) SOY2PageController::jump("Item?error");

			SOY2Logic::createInstance("logic.shop.item.ItemLogic")->delete(array($id));
		}

		SOY2PageController::jump("Item?deleted");
	}
}
