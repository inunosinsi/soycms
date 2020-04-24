<?php
class ItemOrderPriceBulkChangeOrderEdit extends SOYShopOrderEditBase{

	function addFunc($orderId){
		return self::buildCalcArea($orderId);
	}

	function addFuncOnAdminOrder($orderId){
		return self::buildCalcArea($orderId);
	}

	private function buildCalcArea($orderId){
		SOY2::import("module.plugins.itemorder_price_bulk_change.util.BulkChangeUtil");
		$config = BulkChangeUtil::getConfig();

		$html = array();

		$html[] = "<table class=\"form_list\" style=\"width:80%;\">";
		$html[] = "<tr>";
		$html[] = "<th class=\"alC\">単価の一括変更</th>";
		$html[] = "<td style=\"text-align:center;\">";
		$html[] = "<input type=\"number\" id=\"itemorder_price_bulk_change\" step=\"0.01\" style=\"width:80px;\">&nbsp;%";
		$html[] = "<span style=\"border:1px solid #ABABAB;padding:2px 3px;\">";

		foreach(BulkChangeUtil::getModeList() as $t){
			if($config["mode"] == $t){
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change\" value=\"" . $t . "\" checked=\"checked\">" . BulkChangeUtil::getModeText($t) . "</label>";
			}else{
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change\" value=\"" . $t . "\">" . BulkChangeUtil::getModeText($t) . "</label>";
			}
		}

		$html[] = "</span>";
		$html[] = "<span style=\"border:1px solid #ABABAB;padding:2px 3px;margin-left:3px;\">";
		foreach(BulkChangeUtil::getMethodList() as $t){
			if($config["method"] == $t){
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change_method\" value=\"" . $t . "\" checked=\"checked\">" . BulkChangeUtil::getMethodText($t) . "</label>";
			}else{
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change_method\" value=\"" . $t . "\">" . BulkChangeUtil::getMethodText($t) . "</label>";
			}
		}

		$html[] = "</span>";
		$html[] = "&nbsp;&nbsp;<a href=\"javascript:void(0);\" id=\"itemorder_price_bulk_change_button\" class=\"button\">一括変更</a>";
		$html[] = "</td>";
		$html[] = "</tr>";
		$html[] = "</table>";
		$html[] = "<script>";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/edit.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.order.edit", "itemorder_price_bulk_change", "ItemOrderPriceBulkChangeOrderEdit");
