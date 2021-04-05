<?php

SOYShopPlugin::load("soyshop.item.option");
class ItemOrderListComponent extends HTMLList{

	private $itemCount;

	protected function populateItem($itemOrder, $key, $counter) {
		$item = soyshop_get_item_object($itemOrder->getItemId());

		$this->addLink("item_link", array(
			"link" => soyshop_get_item_detail_link($item)
		));

		$this->addLink("item_code", array(
			"text" => (is_string($item->getCode()) && strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
		));

		$this->addLabel("item_code_plain", array(
			"text" => (is_string($item->getCode()) && strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId()
		));

		$this->addImage("item_small_image", array(
			"src" => soyshop_convert_file_path($item->getAttribute("image_small"), $item)
		));

		$this->addLabel("item_name", array(
			"text" => (strlen($item->getCode())) ? $itemOrder->getItemName() : "---"
		));

		$this->addLabel("item_option", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? soyshop_build_item_option_html_on_item_order($itemOrder) : ""
		));

		//隠しモード　商品オプションの編集
		$this->addLabel("item_option_form", array(
			"html" => ($itemOrder instanceof SOYShop_ItemOrder) ? self::getItemOptionForm($itemOrder) : ""
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($itemOrder->getItemPrice())
		));

		$this->addLabel("item_count", array(
			"text" => soy2_number_format($itemOrder->getItemCount())
		));

		$this->addInput("item_count_input", array(
			"name" => "item_count[" . $key . "]",
			"value" => $itemOrder->getItemCount(),
			"attr:id" => "item_count_" . $counter
		));

		$this->addModel("is_item_delete", array(
			"visible" => ($this->itemCount > 1)
		));

		//商品が２つ以上でボタンを押せるようにする
		$this->addActionLink("item_delete", array(
			"text" => ($this->itemCount > 1) ? "削除" : "",
			"link" => ($this->itemCount > 1) ? soyshop_get_mypage_url() . "/order/edit/item/" . $itemOrder->getOrderId() . "?index=" . $key : null,
			"attr:id" => "item_delete_" . $counter
		));

		$this->addLabel("item_total_price", array(
			"text" => soy2_number_format($itemOrder->getTotalPrice())
		));

		//子商品
		$parent = (is_numeric($item->getType())) ? soyshop_get_item_object($item->getType()) : new SOYShop_Item();

		/** 親商品関連のタグ **/
		$this->addLink("parent_link", array(
			"text" => $parent->getOpenItemName(),
			"link" => soyshop_get_item_detail_link($parent)
		));
		$this->addLabel("parent_name_plain", array(
			"text" => $parent->getOpenItemName(),
		));

		$this->addLabel("parent_code", array(
			"text" => $parent->getCode(),
		));

		$this->addImage("parent_small_image", array(
			"src" => soyshop_convert_file_path($parent->getAttribute("image_small"), $parent)
		));

		$this->addImage("parent_large_image", array(
			"src" => soyshop_convert_file_path($parent->getAttribute("image_large"), $parent)
		));
	}

	private function getItemOptionForm(SOYShop_ItemOrder $itemOrder){
		$htmls = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "form",
			"item" => $itemOrder,
		))->getHtmls();

		if(!is_array($htmls) || !count($htmls)) return "";

		$html = array();
		foreach($htmls as $h){
			if(!strlen($h)) continue;
			$html[] = $h;
		}

		if(count($html)) $html[] = "<input type=\"submit\" name=\"option\" value=\"変更\">";

		return implode("\n", $html);
	}

	function setItemCount($itemCount){
		$this->itemCount = $itemCount;
	}
}
