<?php

SOY2::import("module.plugins.common_order_date_customfield.component.CustomfieldDateSearchFormComponent");
class OrderDateCustomfieldOrderSearch extends SOYShopOrderSearch{

	private $dao;
	private $list = array();

	private function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
			foreach(SOYShop_OrderDateAttributeConfig::load() as $config){
				if((int)$config->getOrderSearchItem() === 1){
					$this->list[] = $config;
				}
			}
		}
	}

	function setParameter($params){
		self::prepare();
		if(count($this->list)){
			$queries = array();
			$binds = array();

			foreach($this->list as $field){
				$where = array();

				if(isset($params[$field->getFieldId() . "_start"]) && strlen($params[$field->getFieldId() . "_start"])){
					$where[] = "order_value_1 >= :" . $field->getFieldId() . "_start";
					$binds[":" . $field->getFieldId() . "_start"] = soyshop_convert_timestamp($params[$field->getFieldId() . "_start"]);
				}

				if(isset($params[$field->getFieldId() . "_end"]) && strlen($params[$field->getFieldId() . "_end"])){
					$where[] = "order_value_1 <= :" . $field->getFieldId() . "_end";
					$binds[":" . $field->getFieldId() . "_end"] = soyshop_convert_timestamp($params[$field->getFieldId() . "_end"], "end");
				}

				if(count($where)){
					$queries[] = "id IN (SELECT order_id FROM soyshop_order_date_attribute WHERE order_field_id = '" . $field->getFieldId() . "' AND " . implode(" AND ", $where) . ")";
				}
			}

			if(count($queries)) return array("queries" => $queries, "binds" => $binds);
		}
	}

	function searchItems($params){
		self::prepare();
		if(count($this->list)){
			$array = array();
			foreach($this->list as $field){

				$start = (isset($params[$field->getFieldId() . "_start"])) ? $params[$field->getFieldId() . "_start"] : "";
				$end = (isset($params[$field->getFieldId() . "_end"])) ? $params[$field->getFieldId() . "_end"] : "";

				$html = array();
				$html[] = "<input name=\"search[customs][common_order_date_customfield][" . $field->getFieldId() . "_start]\" type=\"text\" class=\"date_picker_start\" value=\"" . $start . "\">";
				$html[] = "～";
				$html[] = "<input name=\"search[customs][common_order_date_customfield][" . $field->getFieldId() . "_end]\" type=\"text\" class=\"date_picker_end\" value=\"" . $end . "\">";

				$array[$field->getFieldId()] = array("label" => $field->getLabel(), "form" => implode("\n", $html));
			}

			return $array;
		}
	}
}
SOYShopPlugin::extension("soyshop.order.search", "common_order_date_customfield", "OrderDateCustomfieldOrderSearch");
