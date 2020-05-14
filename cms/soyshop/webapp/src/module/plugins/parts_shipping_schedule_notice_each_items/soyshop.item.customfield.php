<?php
class ShippingSchuduleNoticeEachItemsCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		//リンクを表示
		$html = array();
		$html[] = "<dt>出荷予定日の設定</dt>";
		$html[] = "<dd><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=parts_shipping_schedule_notice_each_items&item_id=" . $item->getId()) . "\" class=\"btn btn-default\">出荷予定日の設定を開く</a></dd>";
		return implode("\n", $html);
	}

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($id){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "parts_shipping_schedule_notice_each_items", "ShippingSchuduleNoticeEachItemsCustomField");
