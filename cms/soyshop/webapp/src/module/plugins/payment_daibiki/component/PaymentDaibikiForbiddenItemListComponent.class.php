<?php

class PaymentDaibikiForbiddenItemListComponent extends HTMLList{

	public function populateItem($value, $key, $counter){

		$this->addInput("item_code", array(
			"name"  => "payment_daibiki[item_table][]",
			"value" => $value,
			"attr:tabindex" => $counter + 200
		));

		//商品情報を取得
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$item = $itemDAO->getByCode(trim($value));
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		$this->addLink("item_name", array(
			"text"  => $item->getName(),
			"link" => SOY2PageController::createLink("Item.Detail." . $item->getId())
		));
	}
}
?>