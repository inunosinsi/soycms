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

		$html[] = "<div class=\"table-responsive text-center\">";
		$html[] = "<table class=\"table table-striped\" style=\"width:80%;\">";
		$html[] = "<tr>";
		$html[] = "<th class=\"text-cente\">単価の一括変更</th>";
		$html[] = "<td class=\"text-center;\">";
		$html[] = "<input type=\"number\" id=\"itemorder_price_bulk_change\" step=\"0.01\" style=\"width:80px;\">&nbsp;%";
		$html[] = "<span style=\"border:1px solid #ABABAB;padding:8px 10px;\">";

		foreach(BulkChangeUtil::getModeList() as $t){
			if($config["mode"] == $t){
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change\" value=\"" . $t . "\" checked=\"checked\">" . BulkChangeUtil::getModeText($t) . "</label>";
			}else{
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change\" value=\"" . $t . "\">" . BulkChangeUtil::getModeText($t) . "</label>";
			}
		}

		$html[] = "</span>";
		$html[] = "<span style=\"border:1px solid #ABABAB;padding:8px 10px;margin-left:3px;\">";
		foreach(BulkChangeUtil::getMethodList() as $t){
			if($config["method"] == $t){
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change_method\" value=\"" . $t . "\" checked=\"checked\">" . BulkChangeUtil::getMethodText($t) . "</label>";
			}else{
				$html[] = "<label><input type=\"radio\" name=\"itemorder_price_bulk_change_method\" value=\"" . $t . "\">" . BulkChangeUtil::getMethodText($t) . "</label>";
			}
		}

		$html[] = "</span>";
		$html[] = "&nbsp;&nbsp;<a href=\"javascript:void(0);\" id=\"itemorder_price_bulk_change_button\" class=\"btn btn-info btn-sm\">一括変更</a>";
		$html[] = "</td>";
		$html[] = "</tr>";
		$html[] = "</table>";
		$html[] = "</div>";
		$html[] = "<script>";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/edit.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.order.edit", "itemorder_price_bulk_change", "ItemOrderPriceBulkChangeOrderEdit");
