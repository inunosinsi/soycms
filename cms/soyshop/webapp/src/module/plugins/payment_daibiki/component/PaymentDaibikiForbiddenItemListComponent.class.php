<?php

class PaymentDaibikiForbiddenItemListComponent extends HTMLList{

	public function populateItem($code, $key, $counter){

		$this->addInput("item_code", array(
			"name"  => "payment_daibiki[item_table][]",
			"value" => $code,
			"attr:tabindex" => $counter + 200
		));

		//商品情報を取得
		$item = self::_getItemByCode($code);

		$this->addLink("item_name", array(
			"text"  => $item->getName(),
			"link" => SOY2PageController::createLink("Item.Detail." . $item->getId())
		));
	}

	private function _getItemByCode($code){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!is_string($code)) return new SOYShop_Item();
		$code = trim($code);
		if(!strlen($code)) return new SOYShop_Item();

		try{
			return $dao->getByCode($code);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
}
