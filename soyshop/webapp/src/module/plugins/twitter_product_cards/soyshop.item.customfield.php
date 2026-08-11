<?php
class TwitterProductCardsCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		$attr = soyshop_get_item_attribute_object((int)$item->getId(), "twitter_product_cards");
		$values = (isset($_POST["twitter_product_cards"]) && is_array($_POST["twitter_product_cards"])) ? soy2_serialize($_POST["twitter_product_cards"]) : null;
		$attr->setValue($values);
		soyshop_save_item_attribute_object($attr);
	}

	function getForm(SOYShop_Item $item){
		$values = soy2_unserialize(soyshop_get_item_attribute_value((int)$item->getId(), "twitter_product_cards", "string"));

		$label = (isset($values["label"])) ? $values["label"] : "";
		$value = (isset($values["value"])) ? $values["value"] : "";

		$html[] = "<div class=\"alert alert-info\" style=\"margin-top:15px;\">Twitter Cards:Product Card設定</div>";
		$html[] = "<h4>Twitter Product Cardsのオプション値の追加</h4>";
		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>ラベル</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"twitter_product_cards[label]\" value=\"" . $label . "\">";
		$html[] = "	</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>値</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"twitter_product_cards[value]\" value=\"" . $value . "\">";
		$html[] = "	</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"alert alert-info\">Twitter Cards:Product Card設定ここまで</div>";

		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "twitter_product_cards", "TwitterProductCardsCustomField");
