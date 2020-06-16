<?php

SOY2::import("module.plugins.common_order_date_customfield.component.CustomfieldDateSearchFormComponent");
class OrderCustomfieldOrderSearch extends SOYShopOrderSearch{

	private $dao;
	private $list = array();

	private function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
			foreach(SOYShop_OrderAttributeConfig::load() as $config){
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

				switch($field->getType()){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						if(isset($params[$field->getFieldId()]) && strlen($params[$field->getFieldId()])){
							$where[] = "order_value1 = :" . $field->getFieldId();
							$binds[":" . $field->getFieldId()] = $params[$field->getFieldId()];
						}
						break;
				}

				if(count($where)){
					$queries[] = "id IN (SELECT order_id FROM soyshop_order_attribute WHERE order_field_id = '" . $field->getFieldId() . "' AND " . implode(" AND ", $where) . ")";
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

				$html = array();
				switch($field->getType()){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						$html[] = "<select name=\"search[customs][common_order_customfield][" . $field->getFieldId() . "]\">";
						$html[] = "	<option></option>";
						$cnf = $field->getConfig();
						if(isset($cnf["option"])){
							$opts = explode("\n", $cnf["option"]);
							if(count($opts)){
								foreach($opts as $opt){
									$opt = trim($opt);
									if(!strlen($opt)) continue;
									if(isset($params[$field->getFieldId()]) && $params[$field->getFieldId()] == $opt){
										$html[] = "	<option selected=\"selected\">" . $opt . "</option>";
									}else{
										$html[] = "	<option>" . $opt . "</option>";
									}
								}
							}
						}
						$html[] = "</select>";
						break;
				}

				if(count($html)) $array[$field->getFieldId()] = array("label" => $field->getLabel(), "form" => implode("\n", $html));
			}

			return $array;
		}
	}
}
SOYShopPlugin::extension("soyshop.order.search", "common_order_customfield", "OrderCustomfieldOrderSearch");
