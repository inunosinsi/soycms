<?php
SOYShopPlugin::load("soyshop.item.option");
class ItemOrderListComponent extends HTMLList {

	protected function populateItem($itemOrder) {

		$item = soyshop_get_item_object($itemOrder->getItemId());

		//確認済みの時は背景色を変更する
		$this->addModel("is_confirm_tr", array(
			"style" => ($itemOrder->getIsConfirm()) ? "background-color:#cdcdcd;" : ""
		));

		$itemExists = ((int)$itemOrder->getItemId() > 0 && method_exists($item, "getCodeOnAdmin") && strlen($item->getCodeOnAdmin()) > 0);
		$this->addLink("item_id", array(
			"text" => $itemExists ? $item->getCodeOnAdmin() : "Deleted Item (ID=" . $itemOrder->getItemId() . ")",
			"link" => $itemExists ? SOY2PageController::createLink("Item.Detail." . $itemOrder->getItemId()) : "",
		));

		$this->addInput("index_hidden", array(
			"name" => "Item[" . $itemOrder->getId() . "]",
			"value" => $itemOrder->getId()
		));

		$this->addCheckBox("is_confirm", array(
			"name" => "Confirm[]",
			"value" => $itemOrder->getId(),
			"selected" => $itemOrder->getIsConfirm(),
			"elementId" => "is_confirm_" . $itemOrder->getId(),	//テストコード用
			"onchange" => '$(\'#confirm_operation\').show();'
		));

		//item_idが0の場合は名前を表示する
		$this->addLabel("item_name", array(
			"text" => ((int)$itemOrder->getItemId() === 0 || strpos($item->getCodeOnAdmin(), "_delete_") === false) ? $itemOrder->getItemNameOnAdmin() : "---"
		));

		//状態のセレクトボックス 状態が2個以上の場合にセレクトボックスを出力する
		$statusList = SOYShop_ItemOrder::getStatusList();
		$this->addModel("is_status", array(
			"visible" => (count($statusList) > 1)
		));

		$this->addSelect("status", array(
			"name" => "Status[" . $itemOrder->getId() . "]",
			"options" => $statusList,
			"selected" => $itemOrder->getStatus(),
			"indexOrder" => true,
			"onchange" => '$(\'#confirm_operation\').show();'
		));

		//状態のセレクトボックス 状態が2個以上の場合にセレクトボックスを出力する
		$flagList = SOYShop_ItemOrder::getFlagList();
		$this->addModel("is_flag", array(
			"visible" => (count($flagList) > 1)
		));

		$this->addSelect("flag", array(
			"name" => "Flag[" . $itemOrder->getId() . "]",
			"options" => $flagList,
			"selected" => $itemOrder->getFlag(),
			"indexOrder" => true,
			"onchange" => '$(\'#confirm_operation\').show();'
		));


		$this->addLabel("item_option", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? "<br>" . soyshop_build_item_option_html_on_item_order($itemOrder) : ""
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($itemOrder->getItemPrice())
		));

		//仕入値
		$this->addModel("is_purchase_price", array(
			"visible" => self::_isPurchasePrice()
		));

		$this->addLabel("purchase_price", array(
			"text" => soy2_number_format($item->getPurchasePrice())
		));

		$this->addLabel("item_count", array(
			"text" => soy2_number_format($itemOrder->getItemCount())
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($itemOrder->getTotalPrice())
		));
	}

	private function _isPurchasePrice(){
		static $cnf;
		if(is_null($cnf)) $cnf = SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin();
		return $cnf;
	}
}
